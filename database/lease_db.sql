-- =============================================================
--  LEASE MANAGEMENT SYSTEM — Database Schema
--  Generated: 2026-03-11
--  Database : lease_db
-- =============================================================

CREATE DATABASE IF NOT EXISTS `lease_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `lease_db`;

-- -------------------------------------------------------------
--  Table: lessees
--  Stores all lessee / tenant information and lease details
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lessees` (
    `id`                        INT(11)         NOT NULL AUTO_INCREMENT,

    -- Company / Business Info
    `company_name`              VARCHAR(255)    NOT NULL,
    `trade_name`                VARCHAR(255)    DEFAULT NULL COMMENT 'Trade name or store name',
    `nature_of_business`        VARCHAR(255)    DEFAULT NULL,

    -- Contact / Representative
    `owner_lessee_name`         VARCHAR(255)    NOT NULL  COMMENT 'Owner\'s name or lessee representative',
    `owner_address`             TEXT            DEFAULT NULL,
    `business_address`          TEXT            DEFAULT NULL,
    `contact_nos`               VARCHAR(255)    DEFAULT NULL,
    `email_address`             VARCHAR(255)    DEFAULT NULL,

    -- Stall / Space Info
    `space_code`                VARCHAR(100)    NOT NULL  COMMENT 'Stall/unit code e.g. BLOCK4-11',
    `total_area`                DECIMAL(10,2)   DEFAULT NULL COMMENT 'Total area in square meters',

    -- Financial / Rent Details
    `rate_per_sqm`              DECIMAL(12,2)   DEFAULT NULL COMMENT 'Rate per square meter',
    `basic_rent`                DECIMAL(12,2)   DEFAULT NULL,
    `cusa`                      DECIMAL(12,2)   DEFAULT NULL COMMENT 'Common Use Service Area charges',
    `aircon_charges`            DECIMAL(12,2)   DEFAULT NULL,
    `electricity_water_charges` DECIMAL(12,2)   DEFAULT NULL,

    -- Deposits & Bonds
    `security_deposit`          DECIMAL(12,2)   DEFAULT NULL,
    `utility_deposit`           DECIMAL(12,2)   DEFAULT NULL,
    `construction_bond`         DECIMAL(12,2)   DEFAULT NULL,

    -- Lease Period
    `lease_period_start`        DATE            DEFAULT NULL,
    `lease_period_end`          DATE            DEFAULT NULL,
    `lease_period_notes`        VARCHAR(255)    DEFAULT NULL COMMENT 'e.g. "1 year", "Month-to-Month"',

    -- Requirements & Documents
    `valid_ids_presented`       TEXT            DEFAULT NULL COMMENT 'Valid IDs presented and their expiration dates',
    `requirements_submitted`    TEXT            DEFAULT NULL COMMENT 'List of submitted requirements',

    -- Record Metadata
    `status`                    ENUM('Active','Inactive','Terminated','Pending') NOT NULL DEFAULT 'Active',
    `created_at`                TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_company_name`  (`company_name`),
    KEY `idx_space_code`    (`space_code`),
    KEY `idx_status`        (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Main lessees / tenant master table';


-- -------------------------------------------------------------
--  Table: users  (Admin system users)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT(11)     NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(150) NOT NULL,
    `username`      VARCHAR(80)  NOT NULL,
    `email`         VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('Admin','Manager','Staff','Viewer') NOT NULL DEFAULT 'Staff',
    `status`        ENUM('Active','Inactive','Suspended')    NOT NULL DEFAULT 'Active',
    `last_login`    DATETIME    DEFAULT NULL,
    `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
--  Table: audit_logs  (Activity / Audit trail)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11)      DEFAULT NULL,
    `username`   VARCHAR(80)  NOT NULL,
    `action`     VARCHAR(50)  NOT NULL COMMENT 'e.g. LOGIN, CREATE, UPDATE, DELETE',
    `detail`     TEXT         DEFAULT NULL,
    `ip_address` VARCHAR(45)  DEFAULT NULL,
    `level`      ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_user_id`    (`user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================
--  Default seed data — admin user (password: Admin@1234)
--  Hash generated with PASSWORD_BCRYPT
-- =============================================================
INSERT INTO `users` (`name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
('Maria Santos',   'msantos',   'maria@leasepro.ph',  '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'Admin',   'Active'),
('Juan dela Cruz', 'jdelacruz', 'juan@leasepro.ph',   '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'Manager', 'Active'),
('Ana Reyes',      'areyes',    'ana@leasepro.ph',    '$2y$12$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', 'Staff',   'Active');

-- =============================================================
--  END OF SCRIPT
-- =============================================================
