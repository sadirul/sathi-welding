-- Migration 004: seed the default admin login
-- Default PIN is 203050 — CHANGE THIS after your first login.
-- See README.md ("Changing the login PIN") for how to generate a new hash.
USE `sathiwelding`;

INSERT INTO `users` (`pin_hash`)
SELECT '$2y$12$vEfcPqSX4KnybB.687Rl4eYaMnjxWYtas4B/saBzl2lDzpu64HQFm'
WHERE NOT EXISTS (SELECT 1 FROM `users`);
