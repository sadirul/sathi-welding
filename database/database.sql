-- =============================================================================
-- Sathi Welding Karkhana — full database schema
-- Database: sathiwelding
--
-- Import this file directly for a quick setup, or run the numbered files in
-- migrations/ one by one if you prefer tracking schema changes incrementally
-- (both create the same result).
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `sathiwelding`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `sathiwelding`;

-- -----------------------------------------------------------------------------
-- users — single login identity for the shop owner, authenticated by PIN
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pin_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- clients
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `mobile` VARCHAR(10) NULL,
  `address` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clients_mobile` (`mobile`),
  KEY `idx_clients_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- transactions — every due/paid entry for a client; totals are derived from
-- this table via SQL aggregation rather than duplicated on the client row.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `type` ENUM('due', 'paid') NOT NULL,
  `notes` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transactions_client_id` (`client_id`),
  KEY `idx_transactions_type` (`type`),
  KEY `idx_transactions_client_type` (`client_id`, `type`),
  CONSTRAINT `fk_transactions_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Seed a default admin login, PIN = 103050 (bcrypt hash below).
-- CHANGE THIS PIN after your first login — see README.md for how to
-- generate a new hash and update this row.
-- -----------------------------------------------------------------------------
INSERT INTO `users` (`pin_hash`)
SELECT '$2y$12$2tK18HmI9iCWZgKIhGaAI.TZ5nsz0FNwOwxiTyj3gDCMLcMZ1.abO'
WHERE NOT EXISTS (SELECT 1 FROM `users`);
