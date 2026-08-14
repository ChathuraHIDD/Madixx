-- =====================================================================
-- MADIXX — Sunglasses, Spectacles & Eyewear Accessories
-- Database schema + seed data
-- =====================================================================

CREATE DATABASE IF NOT EXISTS madixx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE madixx;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  sku VARCHAR(60) NOT NULL,
  description TEXT,
  short_description VARCHAR(500),
  brand VARCHAR(100) NOT NULL DEFAULT 'MADIXX',
  product_type VARCHAR(100) DEFAULT NULL,
  skin_type VARCHAR(100) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  sale_price DECIMAL(10,2) DEFAULT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  ingredients TEXT,
  benefits TEXT,
  how_to_use TEXT,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0,
  review_count INT UNSIGNED NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  is_new TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','draft') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slug (slug),
  UNIQUE KEY uniq_sku (sku),
  INDEX idx_category (category_id),
  INDEX idx_featured (is_featured),
  INDEX idx_bestseller (is_bestseller),
  INDEX idx_new (is_new),
  INDEX idx_status (status),
  INDEX idx_price (price),
  FULLTEXT INDEX idx_search (name, short_description, brand, product_type),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- NOTE: the `skin_type` column (inherited from the storefront's original template)
-- is repurposed here to store each product's Frame Material — displayed on the
-- storefront as "Frame Material" rather than renaming the column.

-- ---------------------------------------------------------------------
-- product_images (gallery, in addition to products.image = main image)
-- ---------------------------------------------------------------------
CREATE TABLE product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- addresses
-- ---------------------------------------------------------------------
CREATE TABLE addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  label VARCHAR(50) NOT NULL DEFAULT 'Home',
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  address_line VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  province VARCHAR(100) DEFAULT NULL,
  postal_code VARCHAR(20) DEFAULT NULL,
  country VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- cart (guest via session_id, customer via user_id)
-- ---------------------------------------------------------------------
CREATE TABLE cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  session_id VARCHAR(100) DEFAULT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_session (session_id),
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- wishlist
-- ---------------------------------------------------------------------
CREATE TABLE wishlist (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_product (user_id, product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------------
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  order_number VARCHAR(20) NOT NULL,
  customer_name VARCHAR(150) NOT NULL,
  customer_email VARCHAR(190) NOT NULL,
  customer_phone VARCHAR(30) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cod','bank_transfer','card') NOT NULL DEFAULT 'cod',
  payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  stripe_session_id VARCHAR(255) DEFAULT NULL,
  stripe_payment_intent VARCHAR(255) DEFAULT NULL,
  order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  delivery_method ENUM('standard','express') NOT NULL DEFAULT 'standard',
  shipping_address VARCHAR(255) NOT NULL,
  shipping_city VARCHAR(100) NOT NULL,
  shipping_province VARCHAR(100) DEFAULT NULL,
  shipping_postal_code VARCHAR(20) DEFAULT NULL,
  shipping_country VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
  notes VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_order_number (order_number),
  INDEX idx_status (order_status),
  INDEX idx_user (user_id),
  INDEX idx_stripe_session (stripe_session_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- order_items
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED DEFAULT NULL,
  product_name VARCHAR(200) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- reviews (only 'approved' shown publicly)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  review TEXT NOT NULL,
  status ENUM('pending','approved') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_product_review (user_id, product_id),
  INDEX idx_status (status),
  INDEX idx_product (product_id),
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- blog_posts
-- ---------------------------------------------------------------------
CREATE TABLE blog_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  excerpt VARCHAR(500) DEFAULT NULL,
  content LONGTEXT NOT NULL,
  featured_image VARCHAR(255) DEFAULT NULL,
  category VARCHAR(100) NOT NULL,
  author VARCHAR(100) NOT NULL DEFAULT 'MADIXX Team',
  status ENUM('published','draft') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_slug (slug),
  INDEX idx_status (status),
  INDEX idx_category (category),
  FULLTEXT INDEX idx_blog_search (title, excerpt, content)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- newsletter_subscribers
-- ---------------------------------------------------------------------
CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- password_resets
-- ---------------------------------------------------------------------
CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  CONSTRAINT fk_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- contact_messages
-- ---------------------------------------------------------------------
CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(200) DEFAULT NULL,
  message TEXT NOT NULL,
  status ENUM('new','read') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Admin user: admin@madixx.com / Madixx@2026
-- Sample customer: sophie@example.com / Password@123
INSERT INTO users (name, email, phone, password, role, status) VALUES
('MADIXX Admin', 'admin@madixx.com', '0770000000', '$2y$12$cgcFiDJTXGeJejBPljrqS.X9SXwA.EWIDFWI.Tnj7PRvTrKDTKmju', 'admin', 'active'),
('Sophie Fernando', 'sophie@example.com', '0771234567', '$2y$12$Tg69i.ulf6hGgCQ6iwH/RuR4qFnfpo4K0Msos2Eq6UrhV//BBvfPa', 'customer', 'active'),
('Amaya Perera', 'amaya@example.com', '0779876543', '$2y$12$Tg69i.ulf6hGgCQ6iwH/RuR4qFnfpo4K0Msos2Eq6UrhV//BBvfPa', 'customer', 'active');

-- categories
INSERT INTO categories (id, name, slug, description, image) VALUES
(1, 'Sunglasses', 'sunglasses', 'UV400 sun protection in square, round, aviator, cat-eye and shield silhouettes.', 'assets/images/p2.png'),
(2, 'Spectacles', 'spectacles', 'Prescription-ready optical frames for everyday clarity, from acetate classics to featherlight titanium.', 'assets/images/p9.png'),
(3, 'Accessories', 'accessories', 'Cases, cleaning cloths, lens spray and chains to keep every pair protected.', 'assets/images/p21.png');

-- ---------------------------------------------------------------------
-- products — Sunglasses (category_id 1)
-- ---------------------------------------------------------------------
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(1, 'Havana Tortoise Square Sunglasses', 'havana-tortoise-square-sunglasses', 'MDX-SG-001', 'A bold square silhouette in a rich Havana tortoise acetate, finished with gradient brown lenses for glare-free, everyday sun protection.', 'Bold Havana tortoise square frame with gradient brown lenses.', 'MADIXX', 'Square', 'Acetate', 89.00, 69.00, 42, 'assets/images/p1.png',
 'Acetate frame, Gradient brown lenses, UV400 protection, Metal hinges, Keyhole bridge',
 'Blocks 100% of UVA/UVB rays\nGradient lens softens overhead glare\nLightweight acetate for all-day wear\nSpring-loaded hinges for a secure fit',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.8, 126, 1, 1, 0),
(1, 'Noir Bold Square Sunglasses', 'noir-bold-square-sunglasses', 'MDX-SG-002', 'A statement-making all-black square frame with deep, fully-opaque lenses — a modern classic built for everyday sun protection.', 'All-black square sunglasses with fully-opaque UV400 lenses.', 'MADIXX', 'Square', 'Acetate', 79.00, NULL, 65, 'assets/images/p2.png',
 'Acetate frame, Solid black lenses, UV400 protection, Reinforced metal hinges',
 'Full UV400 protection\nHigh-contrast, glare-reducing tint\nDurable, scratch-resistant acetate\nUnisex, versatile everyday shape',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.9, 203, 1, 1, 0),
(1, 'Skyline Gold Aviator Sunglasses', 'skyline-gold-aviator-sunglasses', 'MDX-SG-006', 'The double-bridge aviator, reimagined in a slim gold-tone metal frame with warm brown gradient lenses.', 'Slim gold-tone metal aviators with brown gradient lenses.', 'MADIXX', 'Aviator', 'Metal', 95.00, NULL, 38, 'assets/images/p6.png',
 'Metal frame, Double bridge, Gradient brown lenses, UV400 protection, Adjustable nose pads',
 'Timeless double-bridge aviator shape\nUV400-rated gradient lenses\nAdjustable nose pads for a custom fit\nLightweight, corrosion-resistant metal',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.7, 89, 0, 0, 0),
(1, 'Solstice Hexagon Sunglasses', 'solstice-hexagon-sunglasses', 'MDX-SG-008', 'A sharp, geometric hexagon frame in polished gold-tone metal with warm amber gradient lenses for a retro-modern edge.', 'Geometric gold-tone hexagon frame with amber gradient lenses.', 'MADIXX', 'Geometric', 'Metal', 92.00, NULL, 34, 'assets/images/p8.png',
 'Metal frame, Hexagon lens shape, Gradient amber lenses, UV400 protection',
 'Statement geometric silhouette\nUV400-rated gradient lenses\nLightweight metal construction\nAdjustable nose pads',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.6, 47, 0, 0, 1),
(1, 'Champagne Square Sunglasses', 'champagne-square-sunglasses', 'MDX-SG-010', 'A soft champagne-toned acetate square frame paired with warm brown gradient lenses for an elevated, easy-to-wear neutral.', 'Champagne acetate square frame with brown gradient lenses.', 'MADIXX', 'Square', 'Acetate', 85.00, 68.00, 51, 'assets/images/p10.png',
 'Acetate frame, Gradient brown lenses, UV400 protection, Metal hinges',
 'Soft neutral tone pairs with everything\nUV400 protection\nGradient lens cuts glare from above\nComfortable, lightweight acetate',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.7, 74, 0, 1, 0),
(1, 'Riviera Oval Sunglasses', 'riviera-oval-sunglasses', 'MDX-SG-012', 'A slim tortoise oval frame with a subtle keyhole bridge and warm brown lenses — a compact, face-flattering everyday shape.', 'Slim tortoise oval sunglasses with warm brown lenses.', 'MADIXX', 'Oval', 'Acetate', 88.00, NULL, 29, 'assets/images/p12.png',
 'Acetate frame, Oval lens shape, Gradient brown lenses, UV400 protection',
 'Compact, face-flattering oval shape\nUV400-rated gradient lenses\nLightweight acetate construction\nSubtle tortoise finish',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.5, 38, 0, 0, 0),
(1, 'Ivory Coast Square Sunglasses', 'ivory-coast-square-sunglasses', 'MDX-SG-014', 'A crisp ivory acetate square frame with warm brown gradient lenses and polished gold hardware for a bright, warm-weather look.', 'Ivory acetate square frame with brown gradient lenses.', 'MADIXX', 'Square', 'Acetate', 82.00, NULL, 44, 'assets/images/p14.png',
 'Acetate frame, Gradient brown lenses, UV400 protection, Gold-tone hardware',
 'Bright, warm-weather colourway\nUV400 protection\nGold-tone hardware accents\nDurable, scratch-resistant acetate',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.6, 55, 0, 0, 1),
(1, 'Horizon Shield Sunglasses', 'horizon-shield-sunglasses', 'MDX-SG-017', 'A rimless wraparound shield in warm gold-tone metal with a single sweeping gradient lens for maximum coverage and a directional edge.', 'Rimless gold shield sunglasses with a sweeping gradient lens.', 'MADIXX', 'Shield', 'Metal', 99.00, 79.00, 22, 'assets/images/p17.png',
 'Metal frame, Rimless shield lens, Gradient brown lenses, UV400 protection',
 'Wraparound coverage blocks peripheral glare\nUV400-rated gradient lens\nRimless, ultra-lightweight build\nFashion-forward directional shape',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.8, 61, 1, 0, 1),
(1, 'Vintage Gold Round Sunglasses', 'vintage-gold-round-sunglasses', 'MDX-SG-018', 'A classic round frame in slender gold-tone metal with warm brown gradient lenses — inspired by 1970s archive shapes.', 'Slender gold-tone round sunglasses with brown gradient lenses.', 'MADIXX', 'Round', 'Metal', 84.00, NULL, 40, 'assets/images/p18.png',
 'Metal frame, Round lens shape, Gradient brown lenses, UV400 protection',
 'Archive-inspired round silhouette\nUV400-rated gradient lenses\nSlim, lightweight metal frame\nAdjustable nose pads',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.7, 68, 0, 1, 0),
(1, 'Diplomat Tortoise Sunglasses', 'diplomat-tortoise-sunglasses', 'MDX-SG-019', 'A refined tortoise browline with a keyhole bridge and warm brown gradient lenses, built on sturdy acetate for daily wear.', 'Tortoise browline sunglasses with a keyhole bridge.', 'MADIXX', 'Browline', 'Acetate', 91.00, NULL, 27, 'assets/images/p19.png',
 'Acetate frame, Gradient brown lenses, UV400 protection, Keyhole bridge, Metal hinges',
 'Distinctive browline profile\nUV400-rated gradient lenses\nKeyhole bridge for secure fit\nDurable acetate construction',
 'Store in the included pouch when not in use. Clean lenses only with a microfiber cloth and lens spray — avoid paper towels and household glass cleaner.',
 4.6, 33, 0, 0, 0);

-- ---------------------------------------------------------------------
-- products — Spectacles (category_id 2)
-- ---------------------------------------------------------------------
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(2, 'Heritage Round Optical Frames', 'heritage-round-optical-frames', 'MDX-SP-003', 'A timeless round optical frame in warm tortoise acetate — ready to be fitted with your prescription for sharper, more comfortable vision.', 'Tortoise round optical frames, prescription-ready.', 'MADIXX', 'Round', 'Acetate', 68.00, NULL, 55, 'assets/images/p3.png',
 'Acetate frame, Prescription-ready, Spring hinges, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nSpring hinges resist everyday wear\nLightweight acetate for all-day comfort\nTimeless round silhouette',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.8, 118, 1, 1, 0),
(2, 'Crystal Clear Square Frames', 'crystal-clear-square-frames', 'MDX-SP-004', 'A transparent acetate square frame that pairs with any prescription for a clean, understated everyday look.', 'Transparent acetate square optical frames.', 'MADIXX', 'Square', 'Acetate', 62.00, 49.00, 60, 'assets/images/p4.png',
 'Acetate frame, Prescription-ready, Spring hinges, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nUnderstated, goes-with-everything transparency\nSpring hinges resist everyday wear\nLightweight acetate for all-day comfort',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.7, 92, 1, 1, 0),
(2, 'Rosette Cat-Eye Metal Frames', 'rosette-cat-eye-metal-frames', 'MDX-SP-005', 'A softly angled cat-eye frame in rose-gold metal, designed to sit comfortably for full-day prescription wear.', 'Rose-gold metal cat-eye optical frames.', 'MADIXX', 'Cat-Eye', 'Metal', 74.00, NULL, 36, 'assets/images/p5.png',
 'Metal frame, Prescription-ready, Adjustable nose pads, Spring hinges',
 'Compatible with single-vision and progressive lenses\nFlattering, softly angled cat-eye shape\nAdjustable nose pads for a custom fit\nLightweight, corrosion-resistant metal',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.6, 54, 0, 0, 1),
(2, 'Ironclad Square Metal Frames', 'ironclad-square-metal-frames', 'MDX-SP-007', 'A structured gunmetal square frame with a double bridge — a sharp, modern shape built for daily prescription wear.', 'Gunmetal square optical frames with a double bridge.', 'MADIXX', 'Square', 'Metal', 70.00, NULL, 41, 'assets/images/p7.png',
 'Metal frame, Prescription-ready, Double bridge, Adjustable nose pads, Spring hinges',
 'Compatible with single-vision and progressive lenses\nStructured, modern double-bridge shape\nAdjustable nose pads for a custom fit\nDurable gunmetal finish',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.5, 47, 0, 0, 0),
(2, 'Uptown Clubmaster Frames', 'uptown-clubmaster-frames', 'MDX-SP-009', 'The browline classic — black acetate on top, slim gold-tone metal below — updated for everyday prescription wear.', 'Black-and-gold clubmaster optical frames.', 'MADIXX', 'Clubmaster', 'Acetate & Metal', 76.00, 62.00, 48, 'assets/images/p9.png',
 'Acetate & metal frame, Prescription-ready, Spring hinges, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nClassic browline silhouette\nSpring hinges resist everyday wear\nGold-tone metal accents',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.9, 141, 1, 1, 0),
(2, 'Muse Cat-Eye Frames', 'muse-cat-eye-frames', 'MDX-SP-011', 'A confident black acetate cat-eye with gold hardware details, cut for a comfortable everyday prescription fit.', 'Black acetate cat-eye optical frames with gold hardware.', 'MADIXX', 'Cat-Eye', 'Acetate', 72.00, NULL, 39, 'assets/images/p11.png',
 'Acetate frame, Prescription-ready, Spring hinges, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nConfident, sculpted cat-eye shape\nSpring hinges resist everyday wear\nGold-tone hardware details',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.7, 63, 0, 0, 0),
(2, 'Meridian Gold Square Frames', 'meridian-gold-square-frames', 'MDX-SP-013', 'A refined gold-tone metal square frame with a slim double bridge — polished enough for the office, light enough for all day.', 'Slim gold-tone metal square optical frames.', 'MADIXX', 'Square', 'Metal', 78.00, NULL, 33, 'assets/images/p13.png',
 'Metal frame, Prescription-ready, Double bridge, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nPolished, office-ready square shape\nAdjustable nose pads for a custom fit\nLightweight, corrosion-resistant metal',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.6, 41, 0, 0, 0),
(2, 'Featherlight Rimless Frames', 'featherlight-rimless-frames', 'MDX-SP-015', 'A rimless rectangular frame in brushed titanium — as close to weightless as eyewear gets, for all-day prescription comfort.', 'Rimless titanium rectangular optical frames.', 'MADIXX', 'Rimless', 'Titanium', 66.00, NULL, 30, 'assets/images/p15.png',
 'Titanium frame, Prescription-ready, Rimless construction, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nRimless build is virtually weightless\nHypoallergenic titanium construction\nAdjustable nose pads for a custom fit',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.8, 57, 1, 0, 1),
(2, 'Amber Grove Square Frames', 'amber-grove-square-frames', 'MDX-SP-016', 'A warm, translucent amber acetate square frame that adds a soft pop of colour to everyday prescription wear.', 'Translucent amber acetate square optical frames.', 'MADIXX', 'Square', 'Acetate', 64.00, 52.00, 46, 'assets/images/p16.png',
 'Acetate frame, Prescription-ready, Spring hinges, Adjustable nose pads',
 'Compatible with single-vision and progressive lenses\nWarm, translucent amber colourway\nSpring hinges resist everyday wear\nLightweight acetate for all-day comfort',
 'Have your prescription lenses fitted by an optician. Clean with a microfiber cloth and lens spray daily to keep lenses clear.',
 4.5, 35, 0, 0, 0);

-- ---------------------------------------------------------------------
-- products — Accessories (category_id 3)
-- ---------------------------------------------------------------------
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(3, 'Saddle Leather Glasses Case', 'saddle-leather-glasses-case', 'MDX-AC-020', 'A soft, snap-close glasses case in tan vegan leather with a curved silhouette that slips easily into a bag.', 'Snap-close vegan leather glasses case in tan.', 'MADIXX', 'Case', 'Vegan Leather', 28.00, NULL, 80, 'assets/images/p20.png',
 'Vegan leather exterior, Soft microfiber lining, Magnetic snap closure',
 'Cushions frames from drops and scratches\nSoft microfiber lining protects lenses\nSlim profile fits easily into bags\nMagnetic snap stays securely shut',
 'Wipe clean with a dry cloth. Keep away from prolonged direct sunlight to preserve the leather colour.',
 4.7, 44, 0, 1, 0),
(3, 'MADIXX Classic Hard Case', 'madixx-classic-hard-case', 'MDX-AC-021', 'A matte-black clamshell hard case with the MADIXX wordmark debossed on the lid — built to protect your frames from daily knocks.', 'Matte-black clamshell hard case with debossed logo.', 'MADIXX', 'Case', 'Molded Shell', 24.00, NULL, 95, 'assets/images/p21.png',
 'Molded protective shell, Plush interior lining, Spring-hinge lid',
 'Rigid shell guards against impact\nPlush lining prevents scratches\nSpring-hinge lid opens and snaps shut smoothly\nFits most sunglasses and spectacles',
 'Wipe the exterior clean with a dry or slightly damp cloth. Allow to air dry before storing frames inside.',
 4.8, 71, 1, 1, 0),
(3, 'MADIXX Microfiber Lens Cloth', 'madixx-microfiber-lens-cloth', 'MDX-AC-022', 'A generously sized microfiber cleaning cloth with the MADIXX Eyewear wordmark in gold foil — gentle on coated lenses, tough on smudges.', 'MADIXX-branded microfiber lens cleaning cloth.', 'MADIXX', 'Cleaning Cloth', 'Microfiber', 8.00, NULL, 200, 'assets/images/p22.png',
 '100% microfiber, Lint-free weave, Machine washable',
 'Lifts smudges without scratching lenses\nSafe for anti-reflective and blue-light coatings\nLint-free, streak-free finish\nMachine washable for reuse',
 'Use dry for light smudges, or with MADIXX Lens Cleaner Spray for a deeper clean. Machine wash cold, air dry — avoid fabric softener.',
 4.9, 96, 0, 1, 0),
(3, 'MADIXX Lens Cleaner Spray', 'madixx-lens-cleaner-spray', 'MDX-AC-023', 'A streak-free, alcohol-free lens cleaning spray formulated to be safe on anti-reflective, blue-light and polarized coatings.', 'Alcohol-free lens cleaner, safe on coated lenses.', 'MADIXX', 'Lens Care', 'Alcohol-Free Formula', 12.00, NULL, 120, 'assets/images/p23.png',
 'Purified water, Mild surfactant, Alcohol-free, Ammonia-free',
 'Streak-free, residue-free clean\nSafe on anti-reflective and blue-light coatings\nAlcohol- and ammonia-free formula\nCompact size for a bag or desk',
 'Mist 1-2 pumps onto each lens and wipe gently with a microfiber cloth in circular motions.',
 4.6, 58, 0, 0, 1),
(3, 'Beaded Gold Eyewear Chain', 'beaded-gold-eyewear-chain', 'MDX-AC-024', 'A delicate gold-plated chain with black enamel beading and adjustable silicone grips — keeps your glasses close without the awkward tan line.', 'Gold-plated beaded eyewear chain with silicone grips.', 'MADIXX', 'Chain', 'Gold-Plated Brass', 22.00, 17.00, 65, 'assets/images/p24.png',
 'Gold-plated brass, Enamel beading, Silicone frame grips',
 'Keeps glasses within reach when not worn\nSecure silicone grips fit most frame arms\nDoubles as a everyday necklace-style accessory\nTarnish-resistant gold plating',
 'Attach the silicone grips to the tips of your glasses arms. Wipe clean with a soft, dry cloth — avoid perfume and water contact.',
 4.5, 29, 0, 0, 1);

-- product_images (single gallery entry mirroring the main image — one photo per product)
INSERT INTO product_images (product_id, image, sort_order)
SELECT id, image, 1 FROM products;

-- sample approved reviews
INSERT INTO reviews (user_id, product_id, rating, review, status, created_at) VALUES
(2, 1, 5, 'The tortoise pattern is gorgeous in person and the fit is snug without pinching. Get compliments every time I wear these.', 'approved', '2026-06-02 09:15:00'),
(3, 2, 5, 'My go-to pair now. Lightweight, sturdy hinges, and the lenses are properly dark without being distorted.', 'approved', '2026-06-10 14:02:00'),
(2, 9, 5, 'Ordered these for my prescription and the optician had no trouble fitting my lenses. Clubmaster shape looks great on my face.', 'approved', '2026-05-20 11:30:00'),
(3, 15, 4, 'Genuinely barely feel like I am wearing glasses. Only wish they came in more colours.', 'approved', '2026-06-15 16:45:00'),
(2, 21, 5, 'Sturdy little case, the logo is a nice subtle touch and it fits in my bag easily.', 'approved', '2026-06-18 08:20:00'),
(3, 22, 4, 'Does the job well, bigger than I expected which is great for cleaning both lenses at once.', 'approved', '2026-06-05 19:10:00');

-- blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, author, status, created_at) VALUES
('How to Choose Sunglasses for Your Face Shape', 'how-to-choose-sunglasses-for-your-face-shape', 'A quick guide to matching frame silhouettes — square, round, cat-eye and aviator — to your face shape.', '<p>The easiest rule of thumb: choose a frame shape that contrasts your face shape. Round faces are flattered by angular square or geometric frames, while angular faces are softened by round or oval shapes.</p><p>Oval faces are the most versatile and can wear nearly any silhouette, from aviators to bold squares. When in doubt, try a browline or cat-eye — both tend to suit the widest range of face shapes.</p>', 'assets/images/hero.png', 'Style Guides', 'MADIXX Team', 'published', '2026-05-01 10:00:00'),
('UV400, Polarized, Gradient: A Lens Glossary', 'uv400-polarized-gradient-lens-glossary', 'What the lens terms on every product page actually mean, explained simply.', '<p>UV400 means a lens blocks essentially all UVA and UVB rays up to 400 nanometers — the industry standard for sun protection, and something every MADIXX sunglass lens meets.</p><p>Polarized lenses filter out horizontal glare bouncing off flat surfaces like water, roads and glass. Gradient lenses are tinted darker at the top and lighter at the bottom, cutting glare from the sky while keeping close-up visibility clear.</p>', 'assets/images/p6.png', 'Lens Guides', 'MADIXX Team', 'published', '2026-05-12 10:00:00'),
('Caring for Your Frames: A 5-Minute Routine', 'caring-for-your-frames-routine', 'Keep your sunglasses and spectacles scratch-free and streak-free with this simple weekly habit.', '<p>Rinse lenses under lukewarm water before wiping to remove any grit that could scratch the coating, then use a dedicated lens spray and a clean microfiber cloth in gentle circular motions.</p><p>Store frames in a hard case whenever they are not being worn, and avoid leaving them lens-down on any surface — even a soft one.</p>', 'assets/images/p23.png', 'Care Guides', 'MADIXX Team', 'published', '2026-05-20 10:00:00'),
('Acetate vs. Metal vs. Titanium: Which Frame Material Is Right for You?', 'acetate-vs-metal-vs-titanium-frame-materials', 'A breakdown of the three frame materials in the MADIXX collection and where each one shines.', '<p>Acetate is a plant-based plastic that holds rich colour and pattern — think tortoiseshell — and is naturally hypoallergenic and easy to adjust for fit.</p><p>Metal frames are slim and lightweight, ideal for delicate shapes like aviators and cat-eyes. Titanium takes that a step further: it is around 40% lighter than standard metal and highly resistant to corrosion, making it ideal for daily prescription wear.</p>', 'assets/images/p15.png', 'Frame Guides', 'MADIXX Team', 'published', '2026-06-01 10:00:00'),
('Introducing: The New MADIXX Collection', 'introducing-the-new-madixx-collection', 'Our biggest launch yet is here — sunglasses, spectacles and accessories designed to see and be seen.', '<p>We are thrilled to introduce the new MADIXX collection: a curated edit of sunglasses, prescription-ready spectacles and eyewear accessories designed to work together, from the beach to the boardroom.</p><p>Shop the collection now and discover why it is already becoming a customer favourite.</p>', 'assets/images/hero.png', 'MADIXX News', 'MADIXX Team', 'published', '2026-06-20 10:00:00');
