-- =============================================================
--  LEASE MANAGEMENT SYSTEM — Users Table
--  File   : database/users.sql
--  Run    : mysql -u root lease_db < database/users.sql
-- =============================================================

USE `lease_db`;

-- -------------------------------------------------------------
--  Drop & recreate to ensure clean schema
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `users`;

-- -------------------------------------------------------------
--  Table: users
-- -------------------------------------------------------------
CREATE TABLE `users` (
    `id`                INT(11)      NOT NULL AUTO_INCREMENT,

    -- Identity
    `name`              VARCHAR(150) NOT NULL                COMMENT 'Full name',
    `username`          VARCHAR(80)  NOT NULL                COMMENT 'Unique login username',
    `email`             VARCHAR(255) NOT NULL                COMMENT 'Email address',
    `password_hash`     VARCHAR(255) NOT NULL                COMMENT 'bcrypt hashed password',

    -- Profile
    `avatar`            VARCHAR(255) DEFAULT NULL            COMMENT 'Path to profile photo',
    `phone`             VARCHAR(50)  DEFAULT NULL            COMMENT 'Contact number',
    `address`           TEXT         DEFAULT NULL            COMMENT 'Home/office address',
    `position`          VARCHAR(150) DEFAULT NULL            COMMENT 'Job title / position',
    `department`        VARCHAR(150) DEFAULT NULL            COMMENT 'Department',

    -- Access Control
    `role`              ENUM('Admin','Manager','Staff','Viewer')    NOT NULL DEFAULT 'Staff',
    `status`            ENUM('Active','Inactive','Suspended')       NOT NULL DEFAULT 'Active',

    -- Login Tracking
    `last_login`        DATETIME     DEFAULT NULL            COMMENT 'Last successful login timestamp',
    `last_login_ip`     VARCHAR(45)  DEFAULT NULL            COMMENT 'IP address of last login',
    `login_attempts`    TINYINT(3)   NOT NULL DEFAULT 0      COMMENT 'Failed login attempt counter',
    `locked_until`      DATETIME     DEFAULT NULL            COMMENT 'Account locked until this time after too many failed attempts',

    -- Security
    `email_verified`    TINYINT(1)   NOT NULL DEFAULT 0      COMMENT '1 = email verified',
    `two_factor`        TINYINT(1)   NOT NULL DEFAULT 0      COMMENT '1 = 2FA enabled',
    `two_factor_secret` VARCHAR(100) DEFAULT NULL,

    -- Timestamps
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME     DEFAULT NULL             COMMENT 'Soft delete — NULL means not deleted',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email`    (`email`),
    KEY `idx_role`           (`role`),
    KEY `idx_status`         (`status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='System users and administrators';


-- -------------------------------------------------------------
--  Table: user_sessions  (tracks active login sessions)
-- -------------------------------------------------------------
CREATE TABLE `user_sessions` (
    `id`            INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`       INT(11)      NOT NULL,
    `session_token` VARCHAR(255) NOT NULL                COMMENT 'Unique session token',
    `ip_address`    VARCHAR(45)  DEFAULT NULL,
    `user_agent`    VARCHAR(500) DEFAULT NULL            COMMENT 'Browser / device info',
    `browser`       VARCHAR(150) DEFAULT NULL            COMMENT 'Parsed browser name',
    `platform`      VARCHAR(100) DEFAULT NULL            COMMENT 'Parsed OS / platform',
    `login_time`    DATETIME     NOT NULL DEFAULT NOW(),
    `last_activity` DATETIME     DEFAULT NULL,
    `expires_at`    DATETIME     NOT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_session_token` (`session_token`),
    KEY `idx_user_id`             (`user_id`),
    KEY `idx_is_active`           (`is_active`),
    CONSTRAINT `fk_sessions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Active user login sessions';


-- -------------------------------------------------------------
--  Table: password_resets
-- -------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11)      NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_token` (`token`),
    KEY `idx_user_id`     (`user_id`),
    CONSTRAINT `fk_resets_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Password reset tokens';


-- =============================================================
--  Seed Data — Default Users
--  All passwords = Admin@1234  (bcrypt hash below)
--  IMPORTANT: Change passwords immediately after first login!
-- =============================================================
INSERT INTO `users`
    (`name`, `username`, `email`, `password_hash`, `role`, `status`, `position`, `department`, `phone`)
VALUES
    (
        'Maria Santos', 'msantos', 'maria@leasepro.ph',
        '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW',
        'Admin', 'Active', 'System Administrator', 'IT', '09171234567'
    ),
    (
        'Juan dela Cruz', 'jdelacruz', 'juan@leasepro.ph',
        '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW',
        'Manager', 'Active', 'Leasing Manager', 'Operations', '09181234567'
    ),
    (
        'Ana Reyes', 'areyes', 'ana@leasepro.ph',
        '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW',
        'Staff', 'Active', 'Leasing Officer', 'Operations', '09191234567'
    ),
    (
        'Pedro Lim', 'plim', 'pedro@leasepro.ph',
        '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW',
        'Staff', 'Inactive', 'Leasing Officer', 'Operations', '09201234567'
    ),
    (
        'Rosa Cruz', 'rcruz', 'rosa@leasepro.ph',
        '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW',
        'Viewer', 'Active', 'Auditor', 'Finance', '09211234567'
    );

-- =============================================================
--  END OF SCRIPT
-- =============================================================
