<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use TheNetworg\OAuth2\Client\Provider\Azure as AzureProvider;

/**
 * Wraps TheNetworg/oauth2-azure for the OpenID Connect Authorization Code flow
 * against Microsoft Entra ID (v2.0 endpoint).
 *
 * Loaded with: $this->load->library('azure_auth');
 */
class Azure_auth {

    const SESSION_STATE_KEY = 'azure_oauth_state';
    const SESSION_NONCE_KEY = 'azure_oauth_nonce';

    /** @var array */
    protected $cfg;

    /** @var AzureProvider */
    protected $provider;

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        // CI3 auto-merges application/config/<ENVIRONMENT>/Azure.php on top of
        // the default file when both exist. The third argument suppresses the
        // exception if the library is loaded twice in one request.
        $this->CI->config->load('Azure', FALSE, TRUE);
        $loaded = $this->CI->config->item('azure');
        $this->cfg = is_array($loaded) ? $loaded : [];

        if (empty($this->cfg['clientId']) || empty($this->cfg['clientSecret']) || empty($this->cfg['tenantId'])) {
            throw new RuntimeException('Azure SSO is not configured: clientId, clientSecret, and tenantId are required.');
        }
        if (empty($this->cfg['redirectUri'])) {
            throw new RuntimeException('Azure SSO is not configured: redirectUri is required and must match the Entra app registration.');
        }

        $this->provider = new AzureProvider([
            'clientId'                => $this->cfg['clientId'],
            'clientSecret'            => $this->cfg['clientSecret'],
            'redirectUri'             => $this->cfg['redirectUri'],
            'tenant'                  => $this->cfg['tenantId'],
            'defaultEndPointVersion'  => AzureProvider::ENDPOINT_VERSION_2_0,
            'scopes'                  => isset($this->cfg['scopes']) && is_array($this->cfg['scopes'])
                                            ? $this->cfg['scopes']
                                            : ['openid', 'profile', 'email', 'offline_access'],
        ]);
    }

    /**
     * Build the authorization URL, generate fresh state + nonce, store both
     * in the session for verification on callback, return the URL.
     */
    public function get_authorization_url() {
        $nonce = bin2hex(random_bytes(16));

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => $this->provider->scope,
            'nonce' => $nonce,
        ]);

        $this->CI->session->set_userdata([
            self::SESSION_STATE_KEY => $this->provider->getState(),
            self::SESSION_NONCE_KEY => $nonce,
        ]);

        return $authUrl;
    }

    /**
     * Verify state, exchange code for tokens, validate ID token claims,
     * return a normalized claims array. Throws on any failure.
     *
     * @param string $code  The `code` query param from the callback.
     * @param string $state The `state` query param from the callback.
     * @return array{oid:string,tid:string,email:?string,name:?string,preferred_username:?string,upn:?string}
     */
    public function handle_callback($code, $state) {
        $expectedState = $this->CI->session->userdata(self::SESSION_STATE_KEY);
        $expectedNonce = $this->CI->session->userdata(self::SESSION_NONCE_KEY);

        $this->CI->session->unset_userdata(self::SESSION_STATE_KEY);
        $this->CI->session->unset_userdata(self::SESSION_NONCE_KEY);

        if (empty($code)) {
            throw new RuntimeException('Missing authorization code on callback.');
        }
        if (empty($state) || empty($expectedState) || !hash_equals((string) $expectedState, (string) $state)) {
            throw new RuntimeException('OAuth state mismatch — possible CSRF.');
        }

        $token = $this->provider->getAccessToken('authorization_code', ['code' => $code]);

        $claims = $token->getIdTokenClaims();
        if (!is_array($claims) || empty($claims)) {
            throw new RuntimeException('ID token claims missing from token response.');
        }

        if (!isset($claims['aud']) || $claims['aud'] !== $this->cfg['clientId']) {
            throw new RuntimeException('ID token audience does not match clientId.');
        }
        if (!isset($claims['nonce']) || !hash_equals((string) $expectedNonce, (string) $claims['nonce'])) {
            throw new RuntimeException('ID token nonce mismatch — possible replay.');
        }
        if (!isset($claims['tid']) || empty($claims['tid'])) {
            throw new RuntimeException('ID token missing tid (tenant id) claim.');
        }

        $allowed = isset($this->cfg['allowedTenants']) && is_array($this->cfg['allowedTenants'])
            ? $this->cfg['allowedTenants']
            : [];
        if (!empty($allowed)) {
            if (!in_array($claims['tid'], $allowed, TRUE)) {
                throw new RuntimeException('ID token tenant is not in the allowed list.');
            }
        } else {
            if (!hash_equals((string) $this->cfg['tenantId'], (string) $claims['tid'])) {
                throw new RuntimeException('ID token tenant does not match configured tenantId.');
            }
        }

        if (empty($claims['oid'])) {
            throw new RuntimeException('ID token missing oid (object id) claim.');
        }

        return [
            'oid'                => $claims['oid'],
            'tid'                => $claims['tid'],
            'email'              => $claims['email'] ?? NULL,
            'name'               => $claims['name'] ?? NULL,
            'preferred_username' => $claims['preferred_username'] ?? NULL,
            'upn'                => $claims['upn'] ?? NULL,
        ];
    }

    /**
     * Build the federated logout URL — sends the user to Microsoft to sign out
     * of Entra, then back to postLogoutRedirectUri.
     */
    public function build_logout_url() {
        $postLogout = !empty($this->cfg['postLogoutRedirectUri'])
            ? $this->cfg['postLogoutRedirectUri']
            : '';
        return $this->provider->getLogoutUrl($postLogout);
    }
}
