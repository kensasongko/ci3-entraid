-- Add SSO-friendly columns to the existing `user` table.
-- Run manually per environment after deploying the SSO code.

ALTER TABLE `user`
  ADD COLUMN `azure_oid`     VARCHAR(64)  NULL,
  ADD COLUMN `email`         VARCHAR(255) NULL,
  ADD COLUMN `display_name`  VARCHAR(255) NULL,
  ADD COLUMN `auth_source`   ENUM('local','azure') NOT NULL DEFAULT 'local',
  ADD COLUMN `last_login_at` DATETIME NULL,
  ADD UNIQUE KEY `uq_user_azure_oid` (`azure_oid`);
