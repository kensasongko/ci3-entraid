# SSO Implementation Plan: Microsoft Entra ID for CodeIgniter 3

> Companion to `SSO_ENTRA_ID_PLAN.md` (which targeted CI4). This document is the CI3-specific plan that matches the actual project layout in this repo.

## 1. Current State of the Project

This is a **CodeIgniter 3** project (not CI4). The relevant pieces:

- `index.php` + `system/` + `application/` — classic CI3 front controller layout. No `composer.json` and no `vendor/` directory exist yet.
- `application/controllers/Account.php` — three actions:
  - `login()` — if `session->userdata('user')` is set, redirects to `Dashboard`; otherwise loads `v_login`.
  - `validate()` — receives the AJAX POST, calls `Maccount->validateLogin()`, echoes JSON.
  - `logout()` — calls `sess_destroy()` and redirects to `/`.
- `application/models/Maccount.php` — looks up `user` table by lowercase `username`, compares `md5($password)`, on success stores the row (minus password) into `session->userdata('user')` and returns `{status:true, redirect: <Dashboard or url_ref>}`.
- `application/controllers/Dashboard.php` — renders `v_main` with `v_dashboard` content. **No auth gate currently** in the controller (relies on the login form being the default route).
- `application/config/routes.php` — `default_controller = 'Account/login'`, `login => Account/login`. No SSO routes.
- `application/config/autoload.php` — autoloads `database`, `session` libraries; `url`, `file` helpers; `Maccount` model.
- `application/views/v_login.php` — Bootstrap (Sneat-style) login form, AJAX submission to `Account/validate`, on success `window.location = data.redirect`.
- No `.env`, no migrations, no Composer integration. The `user` table is presumed to exist out-of-band with at least `username`, `password` columns.

The session contract — `session->userdata('user')` set to a row-shaped array — is the integration seam SSO must populate so that any future auth gate (and the existing `Account::login()` redirect-if-logged-in check) keeps working unchanged.

Security note (out of scope for this plan but worth flagging): the current local login uses `md5()` for password comparison. SSO sidesteps that for federated users; if local login is retained, plan a separate hash-upgrade pass.

## 2. Goal & Approach

Add **Microsoft Entra ID** as a sign-in option using the **OpenID Connect Authorization Code flow** (v2.0 endpoint). Local username/password remains available behind a config flag so we can pilot SSO and cut over later.

**Library choice:** `thenetworg/oauth2-azure` (built on `league/oauth2-client`).

Why this library on CI3:
- Pure PHP, no framework coupling — works fine inside a CI3 controller after Composer autoload is enabled.
- Maintained, supports v2.0 endpoint, multi-tenant, and exposes ID-token claims.
- Compatible with PHP 7.x/8.x (CI3.1.13 supports PHP 7.3+). Confirm your target PHP version before pinning a version constraint.

Alternatives considered: raw `league/oauth2-client` + manual MS endpoints (more code, no benefit); SAML via `simplesamlphp` (heavier, only if IdP team mandates SAML); `jumbojett/openid-connect-php` (also viable, lighter dep tree, no Azure-specific helpers — fine fallback if `thenetworg/oauth2-azure` is rejected).

## 3. Prerequisites

1. Tenant access in Microsoft Entra admin center to register an app.
2. **Composer** installed locally and on the deployment target. CI3 ships without it; we'll wire it in (Section 5.1).
3. HTTPS in any non-local environment (Entra rejects non-HTTPS redirect URIs except `http://localhost`).
4. PHP extensions: `openssl`, `curl`, `json`, `mbstring`.
5. `application/config/config.php` → `$config['base_url']` set correctly per environment (currently `http://localhost/codeigniter3`); the redirect URI registered in Entra must match exactly.

## 4. Entra ID App Registration (one-time, in Azure portal)

Same as the CI4 plan — done once per environment by an Entra admin:

1. Microsoft Entra admin center → **App registrations** → **New registration**.
2. Name: `CodeIgniter3 App (dev)` (or per-env name).
3. Supported account types: typically **Single tenant**. Multi-tenant only if external orgs need to sign in.
4. Redirect URI: type **Web**, value `https://<host>/codeigniter3/index.php/auth_azure/callback` (adjust the path segment if `index.php` is removed via mod_rewrite, or if the app is mounted at `/`). For local: `http://localhost/codeigniter3/index.php/auth_azure/callback`.
5. After creation, capture **Application (client) ID** and **Directory (tenant) ID**.
6. **Certificates & secrets** → **New client secret** → copy the secret **value** immediately.
7. **Token configuration** → optional claims on ID token: `email`, `upn`, `preferred_username`.
8. **API permissions** → ensure delegated `Microsoft Graph → User.Read`, plus `openid`, `profile`, `email`. Grant admin consent if required by tenant policy.

These five values land in the new config file (Section 5.3):

```
azure.tenantId               = <directory-tenant-id>
azure.clientId               = <application-client-id>
azure.clientSecret           = <client-secret-value>
azure.redirectUri            = https://<host>/codeigniter3/index.php/auth_azure/callback
azure.postLogoutRedirectUri  = https://<host>/codeigniter3/index.php/login
```

## 5. Code Changes — Step by Step

### 5.1 Add Composer + the dependency

CI3 has first-class Composer support but it isn't enabled by default in this repo.

1. At project root, run:
   ```
   composer init --no-interaction --name="local/codeigniter3-app" --require="thenetworg/oauth2-azure:^2.2"
   composer install
   ```
   This creates `composer.json`, `composer.lock`, `vendor/`.
2. Enable autoload in `application/config/config.php`:
   ```php
   $config['composer_autoload'] = TRUE;
   ```
   (CI3 autoloads `vendor/autoload.php` when this is `TRUE`. Alternative: set it to a custom path string.)
3. Add `vendor/` to `.gitignore`; commit `composer.json` and `composer.lock`.

### 5.2 Environment configuration — pick ONE approach

CI3 has no native `.env`. Two reasonable options:

- **Option A (recommended): use CI3's environment-aware config.** Create `application/config/development/Azure.php` and `application/config/production/Azure.php`. CI3 loads the file matching `ENVIRONMENT` (`index.php` defines it). Production secrets live only on prod servers.
- **Option B: use `vlucas/phpdotenv`.** `composer require vlucas/phpdotenv`, load it in `application/config/Azure.php`, read with `getenv()`. Adds a dep but keeps a single config file.

This plan assumes **Option A** for simplicity (no extra dep, works with current deploy practice). Document the choice in the README.

### 5.3 New config file — `application/config/Azure.php`

CI3 configs are arrays, not classes. Example:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['azure'] = [
    'tenantId'              => '',   // override per-env
    'clientId'              => '',
    'clientSecret'          => '',
    'redirectUri'           => '',   // must exactly match Entra registration
    'postLogoutRedirectUri' => '',
    'scopes'                => ['openid', 'profile', 'email', 'offline_access'],
    'endpointVersion'       => 2.0,
    'allowLocalLogin'       => TRUE, // flip to FALSE after cutover
    'allowedTenants'        => [],   // empty = trust whatever tenantId is set; otherwise whitelist of tids
    'jitProvision'          => TRUE, // create user row on first SSO login
];
```

Per-env overrides go in `application/config/development/Azure.php` and `application/config/production/Azure.php` (same array shape, real values).

Loaded via `$this->config->load('Azure');` in the new library/controller, then read with `$this->config->item('azure')`.

### 5.4 New library — `application/libraries/Azure_auth.php`

CI3 libraries are plain PHP classes loaded with `$this->load->library('azure_auth')`. The library wraps `TheNetworg\OAuth2\Client\Provider\Azure` and exposes:

- `__construct()` — reads `azure` config, instantiates the provider.
- `get_authorization_url()` — generates a fresh `state` and `nonce`, stores both in `session->userdata`, returns the URL.
- `handle_callback($code, $state)` — verifies `state` against the session value, exchanges the code for tokens, validates the ID token (`aud == clientId`, `iss` matches expected issuer for the tenant, `nonce` matches stored value, `tid` is in `allowedTenants` if that list is non-empty), returns a normalized claims array (`oid`, `tid`, `email`, `name`, `preferred_username`, `upn`).
- `build_logout_url()` — returns `https://login.microsoftonline.com/{tenant}/oauth2/v2.0/logout?post_logout_redirect_uri=...` for federated logout.

Why a library rather than putting it all in the controller: keeps the controller thin and lets the OAuth flow be unit-tested with a mocked provider.

Critical implementation details:
- `state` is a CSRF token for the OAuth round trip — generate with `bin2hex(random_bytes(16))`, store in session, single-use (delete after verification).
- `nonce` is bound into the ID token — the library should fail-closed if `nonce` claim is missing or mismatched.
- For single-tenant apps, **always** verify `tid` matches the configured `tenantId`. `thenetworg/oauth2-azure` validates the JWT signature, but tenant trust is the caller's responsibility.

### 5.5 New controller — `application/controllers/Auth_azure.php`

CI3 controller (filename and class name capitalized). Three actions:

- `start()` — if already logged in, redirect to `Dashboard`; otherwise call `azure_auth->get_authorization_url()` and `redirect()` to it.
- `callback()` — read `code` and `state` from query string, call `azure_auth->handle_callback()`, on success populate the session in the same shape `Maccount` does today, then redirect to `Dashboard` (or `session->userdata('url_ref')` if set). On failure, set a flashdata error and redirect back to `/login`.
- `logout()` (optional) — `sess_destroy()`, then redirect to `azure_auth->build_logout_url()` for federated sign-out. If federated logout isn't required, the existing `Account::logout()` is enough.

Required session shape on success — keep it compatible with the existing `'user'` userdata used by `Account::login()`'s redirect check:

```php
$user = [
    'id'         => $localUserId,                                  // from JIT lookup, see 5.7
    'username'   => $claims['preferred_username'] ?? $claims['upn'] ?? $claims['email'],
    'name'       => $claims['name'] ?? '',
    'email'      => $claims['email'] ?? $claims['upn'] ?? '',
    'azure_oid'  => $claims['oid'],   // Entra object id — stable user key across renames
    'tenant'     => $claims['tid'],
    'auth_source'=> 'azure',
];
$this->session->set_userdata('user', $user);
$this->session->sess_regenerate(TRUE); // prevent session fixation; CI3 method
```

### 5.6 Routes — `application/config/routes.php`

CI3 has no filter system. Add explicit routes:

```php
$route['auth_azure']            = 'Auth_azure/start';
$route['auth_azure/callback']   = 'Auth_azure/callback';
// Optional federated logout:
// $route['auth_azure/logout']  = 'Auth_azure/logout';
```

Keep `default_controller = Account/login` and the `login` route as-is.

### 5.7 Database — JIT provisioning (recommended)

The current `user` table is presumed to exist with `username`, `password` columns. Add SSO-friendly columns. Since this repo has no migrations enabled, ship a one-shot SQL file alongside the code change:

`application/migrations/sql/2026-04-28_add_sso_columns.sql`:

```sql
ALTER TABLE `user`
  ADD COLUMN `azure_oid`    VARCHAR(64)  NULL,
  ADD COLUMN `email`        VARCHAR(255) NULL,
  ADD COLUMN `display_name` VARCHAR(255) NULL,
  ADD COLUMN `auth_source`  ENUM('local','azure') NOT NULL DEFAULT 'local',
  ADD COLUMN `last_login_at` DATETIME NULL,
  ADD UNIQUE KEY `uq_user_azure_oid` (`azure_oid`);
```

Extend `application/models/Maccount.php` with:

- `findByAzureOid($oid)` — returns the user row or `NULL`.
- `provisionFromAzure(array $claims)` — inserts a new row keyed on `azure_oid`, copies `email`/`display_name`/`username`, sets `auth_source='azure'`, returns the new row.
- `touchLastLogin($userId)` — updates `last_login_at = NOW()`.

The controller calls `findByAzureOid` first; if missing and `azure.jitProvision = TRUE`, calls `provisionFromAzure`; if missing and JIT is off, fail-closes with an "account not provisioned" error. Always call `touchLastLogin` on success.

Decide and document the unknown-user policy (reject vs. JIT provision) per environment. JIT is convenient; reject is safer for tenants with external guests.

### 5.8 Auth gate — new `MY_Controller`

The existing controllers don't enforce auth. To keep the SSO change self-contained, add `application/core/MY_Controller.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user')) {
            $this->session->set_userdata('url_ref', current_url());
            redirect('login');
        }
    }
}
```

Then change `Dashboard extends CI_Controller` to `Dashboard extends MY_Controller`. `Account` and `Auth_azure` stay on `CI_Controller` (they must be reachable when logged out). This is the closest CI3 analogue to the CI4 `Authlogin` filter.

### 5.9 Login view — `application/views/v_login.php`

Add a **"Sign in with Microsoft"** button under the existing username/password form, linking to `<?= site_url('auth_azure') ?>`. Wrap the local form in:

```php
<?php $azure = $this->config->item('azure'); if (!empty($azure['allowLocalLogin'])): ?>
   ... existing form ...
<?php endif; ?>
```

So flipping `allowLocalLogin` to `FALSE` retires the password form without code edits. Also surface `flashdata('sso_error')` near the top of the card so callback failures are visible.

### 5.10 Files touched — summary

| File | Action |
|---|---|
| `composer.json` / `composer.lock` | **new** — add `thenetworg/oauth2-azure` |
| `application/config/config.php` | set `composer_autoload = TRUE` |
| `application/config/Azure.php` | **new** (defaults / shape) |
| `application/config/development/Azure.php` | **new** (per-env values) |
| `application/config/production/Azure.php` | **new** (per-env values, secrets) |
| `application/config/routes.php` | add 2 (or 3) routes |
| `application/libraries/Azure_auth.php` | **new** |
| `application/controllers/Auth_azure.php` | **new** |
| `application/core/MY_Controller.php` | **new** (auth gate for protected controllers) |
| `application/controllers/Dashboard.php` | extend `MY_Controller` instead of `CI_Controller` |
| `application/models/Maccount.php` | add `findByAzureOid`, `provisionFromAzure`, `touchLastLogin` |
| `application/migrations/sql/2026-04-28_add_sso_columns.sql` | **new** — run manually per env |
| `application/views/v_login.php` | add SSO button, gate local form on `allowLocalLogin`, render flashdata error |
| `.gitignore` | add `vendor/`, ignore `application/config/*/Azure.php` if secrets live there |

## 6. Security Checklist

Non-negotiable before going live:

- [ ] HTTPS enforced for all non-local environments (web server config + `$config['cookie_secure'] = TRUE` in CI3 config).
- [ ] `state` generated per-request (cryptographic random) and verified on callback; deleted from session after use.
- [ ] `nonce` included in auth request and validated against the ID token claim.
- [ ] ID token signature validated (handled by `thenetworg/oauth2-azure`); `aud == clientId`, `iss` matches expected tenant issuer, `exp` not past, and `tid` matches `tenantId` (or is in `allowedTenants`).
- [ ] Client secret is **only** in `application/config/<env>/Azure.php` (or env vars / secret manager) — never committed in the default `application/config/Azure.php`.
- [ ] `sess_regenerate(TRUE)` called immediately after successful login (CI3 equivalent of session fixation defense).
- [ ] CI3 session config: `$config['sess_match_ip']` evaluated for your network; `$config['cookie_httponly'] = TRUE`, `$config['cookie_secure'] = TRUE`.
- [ ] Redirect URI registered in Entra matches `azure.redirectUri` **exactly** (scheme, host, port, path including or excluding `index.php`).
- [ ] Unknown-user policy decided and documented (reject vs. JIT provision) per env.
- [ ] Local password column not exposed in any SSO error path (don't leak whether a username exists).

## 7. Testing Plan

1. **Local dev**: register a second app (or add a localhost redirect to the existing one), serve via Apache/`php -S`, browse `/login`, click "Sign in with Microsoft", complete the flow, confirm `Dashboard` loads and `var_dump($this->session->userdata('user'))` matches the existing shape.
2. **Negative cases**:
   - Tamper with `state` on callback → expect rejection, redirect to `/login` with flashdata error.
   - Drop the `nonce` from the ID token (mock) → expect rejection.
   - Use a user from a different tenant when `allowedTenants` is set → expect rejection.
   - Expired/used `code` (replay the callback) → graceful error, no fatal.
3. **Session contract**: confirm existing `Account::login()`'s `if ($this->session->userdata("user"))` redirect to `Dashboard` still fires after SSO login.
4. **Local fallback**: with `allowLocalLogin = TRUE`, confirm the AJAX form still works against `Maccount::validateLogin`. With it `FALSE`, confirm the form is hidden.
5. **Logout**: `Account::logout()` clears the session; the optional `Auth_azure::logout` also reaches the Entra logout page.
6. **JIT provisioning**: log in as a brand-new Entra user, confirm a row appears in `user` with `auth_source='azure'`, `azure_oid` populated, and `last_login_at` set. Log in again — no duplicate row, `last_login_at` updated.

## 8. Rollout Strategy

1. Ship with `azure.allowLocalLogin = TRUE`. Both methods coexist during pilot.
2. Pilot with a small group; verify `last_login_at` audit data and session behavior.
3. Once stable, set `allowLocalLogin = FALSE` to retire local passwords (or keep both if break-glass accounts are required).
4. Communicate the change and document the new sign-in flow in onboarding.

## 9. Open Questions for the Team

1. Single-tenant or multi-tenant? (Affects `tid` validation and `allowedTenants` list.)
2. Keep local password login as a fallback, or hard cutover after pilot?
3. JIT provisioning, or pre-create users in `user` before first login?
4. Federated logout (sign out of Entra too), or local logout only?
5. Any role/group mapping required from Entra (group claims) for authorization beyond "logged in"?
6. Stick with `application/config/<env>/Azure.php` for secrets, or adopt `vlucas/phpdotenv` for a single-source `.env`?
7. Move existing local accounts off `md5()` to `password_hash()` in the same release, or as a follow-up?

## Sources

- [Microsoft: OpenID Connect on the Microsoft identity platform](https://learn.microsoft.com/en-us/entra/identity-platform/v2-protocols-oidc)
- [Microsoft: ID token claims reference](https://learn.microsoft.com/en-us/entra/identity-platform/id-token-claims-reference)
- [TheNetworg/oauth2-azure (GitHub)](https://github.com/TheNetworg/oauth2-azure)
- [CodeIgniter 3 user guide — Composer integration](https://codeigniter.com/userguide3/general/managing_apps.html#composer-support)
- [CodeIgniter 3 user guide — Extending Core (MY_Controller)](https://codeigniter.com/userguide3/general/core_classes.html)
- [CodeIgniter 3 user guide — Sessions](https://codeigniter.com/userguide3/libraries/sessions.html)
