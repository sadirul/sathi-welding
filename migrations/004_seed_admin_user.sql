-- Migration 004: seed the default admin login
-- Default PIN is 103050 — CHANGE THIS after your first login.
-- See README.md ("Changing the login PIN") for how to generate a new hash.
USE `sathiwelding`;

INSERT INTO `users` (`pin_hash`)
SELECT '$2y$12$2tK18HmI9iCWZgKIhGaAI.TZ5nsz0FNwOwxiTyj3gDCMLcMZ1.abO'
WHERE NOT EXISTS (SELECT 1 FROM `users`);
