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
-- users — one login per staff member, authenticated by PIN
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL DEFAULT '',
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
-- user_id records which logged-in staff member recorded the entry ("Received
-- by"); it's nullable and ON DELETE SET NULL so removing a user never loses
-- the transaction history itself.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `type` ENUM('due', 'paid') NOT NULL,
  `notes` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transactions_client_id` (`client_id`),
  KEY `idx_transactions_type` (`type`),
  KEY `idx_transactions_client_type` (`client_id`, `type`),
  KEY `idx_transactions_user_id` (`user_id`),
  CONSTRAINT `fk_transactions_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transactions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Seed logins.
-- Anisul — PIN 103050 (bcrypt hash below)
-- Hara   — PIN 103060 (bcrypt hash below)
-- CHANGE THESE PINS after your first login — see README.md for how to
-- generate a new hash and update a row.
-- -----------------------------------------------------------------------------
INSERT INTO `users` (`name`, `pin_hash`)
SELECT 'Anisul', '$2y$12$2tK18HmI9iCWZgKIhGaAI.TZ5nsz0FNwOwxiTyj3gDCMLcMZ1.abO'
WHERE NOT EXISTS (SELECT 1 FROM `users`);

INSERT INTO `users` (`name`, `pin_hash`)
SELECT 'Hara', '$2y$12$H9EOOjx2FeRGwUI0qd09L.7WrrOlD2e6Yr875FNr4/T2D5yqYhhNe'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `name` = 'Hara');
