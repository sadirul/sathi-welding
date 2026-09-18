-- Migration 007: name the existing login "Anisul" and add a second login
-- "Hara" with PIN 103060.
USE `sathiwelding`;

SET @first_user_id = (SELECT MIN(id) FROM `users`);
UPDATE `users` SET `name` = 'Anisul' WHERE `id` = @first_user_id;

INSERT INTO `users` (`name`, `pin_hash`)
SELECT 'Hara', '$2y$12$H9EOOjx2FeRGwUI0qd09L.7WrrOlD2e6Yr875FNr4/T2D5yqYhhNe'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `name` = 'Hara');
