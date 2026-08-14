-- =====================================================================
-- Adds Stripe card payments to an existing MADIXX database.
-- Run this once against your current database if you already imported
-- database/madixx.sql before Stripe support was added:
--   mysql -u root madixx < database/add_stripe_payment.sql
-- (Skip this file entirely on a fresh install — database/madixx.sql
-- already includes these columns.)
-- =====================================================================

USE madixx;

ALTER TABLE orders
  MODIFY COLUMN payment_method ENUM('cod','bank_transfer','card') NOT NULL DEFAULT 'cod',
  ADD COLUMN stripe_session_id VARCHAR(255) DEFAULT NULL AFTER payment_status,
  ADD COLUMN stripe_payment_intent VARCHAR(255) DEFAULT NULL AFTER stripe_session_id,
  ADD INDEX idx_stripe_session (stripe_session_id);
