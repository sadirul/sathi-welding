-- Migration 005: add a display name to each login (users.name)
USE `sathiwelding`;

ALTER TABLE `users`
  ADD COLUMN `name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`;
