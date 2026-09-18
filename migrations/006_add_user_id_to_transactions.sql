-- Migration 006: track which logged-in user recorded each transaction
USE `sathiwelding`;

ALTER TABLE `transactions`
  ADD COLUMN `user_id` INT UNSIGNED NULL AFTER `client_id`,
  ADD KEY `idx_transactions_user_id` (`user_id`),
  ADD CONSTRAINT `fk_transactions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
