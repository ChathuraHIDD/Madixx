-- =====================================================================
-- Glowelle — Premium Cosmetics E-Commerce
-- Database schema + seed data
-- =====================================================================

CREATE DATABASE IF NOT EXISTS glowelle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE glowelle;

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
  brand VARCHAR(100) NOT NULL DEFAULT 'Glowelle',
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
  payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod',
  payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
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
  author VARCHAR(100) NOT NULL DEFAULT 'Glowelle Team',
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

-- Admin user: admin@glowelle.com / Glowelle@2026
-- Sample customer: sophie@example.com / Password@123
INSERT INTO users (name, email, phone, password, role, status) VALUES
('Glowelle Admin', 'admin@glowelle.com', '0770000000', '$2y$10$C0KyIhlouOG9uiWG/JeyWOoUg3UmszunvRECzyIe5PzjKAI1NG8ha', 'admin', 'active'),
('Sophie Fernando', 'sophie@example.com', '0771234567', '$2y$10$YOWyHjIydndgQU.5emMSuuvS9/RJW8rf/uCHT1UcirgWG4rTFC3F2', 'customer', 'active'),
('Amaya Perera', 'amaya@example.com', '0779876543', '$2y$10$YOWyHjIydndgQU.5emMSuuvS9/RJW8rf/uCHT1UcirgWG4rTFC3F2', 'customer', 'active');

-- categories
INSERT INTO categories (id, name, slug, description, image) VALUES
(1, 'Skincare', 'skincare', 'Cleanser, serum, moisturizer and more.', 'assets/images/placeholder-category.jpg'),
(2, 'Makeup', 'makeup', 'Complexion, lips, eyes and more.', 'assets/images/placeholder-category.jpg'),
(3, 'Body Care', 'body-care', 'Nourish, hydrate and soften.', 'assets/images/placeholder-category.jpg'),
(4, 'Hair Care', 'hair-care', 'Shampoo, treatments and hair oils.', 'assets/images/placeholder-category.jpg'),
(5, 'Glow Essentials', 'glow-essentials', 'Our most-loved beauty products.', 'assets/images/placeholder-category.jpg');

-- products — Skincare (category_id 1)
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(1, 'Radiance Renewal Vitamin C Serum', 'radiance-renewal-vitamin-c-serum', 'GW-SK-001', 'A fast-absorbing serum that brightens, evens tone and defends against daily environmental stress with a stable 15% Vitamin C complex.', 'Brightening serum with 15% Vitamin C for a luminous, even complexion.', 'Glowelle', 'Serum', 'All Skin Types', 62.00, 49.00, 42, 'assets/images/placeholder-product.jpg',
 'Aqua, Ascorbic Acid (15%), Ferulic Acid, Vitamin E, Hyaluronic Acid, Glycerin',
 'Brightens dull skin\nEvens out skin tone\nAntioxidant protection\nBoosts collagen production',
 'Apply 3-4 drops to clean, dry skin every morning before moisturizer and SPF.',
 4.8, 126, 1, 1, 0),
(1, 'Cloud Barrier Ceramide Moisturizer', 'cloud-barrier-ceramide-moisturizer', 'GW-SK-002', 'A weightless, cushiony moisturizer that restores the skin barrier with a triple-ceramide complex for 72-hour hydration.', 'Barrier-repairing moisturizer with a triple-ceramide complex.', 'Glowelle', 'Moisturizer', 'Dry, Sensitive', 54.00, NULL, 65, 'assets/images/placeholder-product.jpg',
 'Aqua, Ceramide NP, Ceramide AP, Ceramide EOP, Cholesterol, Squalane, Niacinamide',
 'Restores skin barrier\n72-hour hydration\nSoothes sensitivity\nNon-greasy finish',
 'Smooth over face and neck morning and night as the final step of your routine.',
 4.9, 203, 1, 1, 0),
(1, 'Pure Clarity Gel Cleanser', 'pure-clarity-gel-cleanser', 'GW-SK-003', 'A gentle gel cleanser that lifts away impurities and makeup without stripping the skin, leaving it soft and balanced.', 'Gentle daily gel cleanser that balances without stripping.', 'Glowelle', 'Cleanser', 'Oily, Combination', 32.00, NULL, 88, 'assets/images/placeholder-product.jpg',
 'Aqua, Cocamidopropyl Betaine, Salicylic Acid, Aloe Vera, Panthenol',
 'Removes impurities and makeup\nBalances oil production\nSoothes and calms\nMaintains skin barrier',
 'Massage onto damp skin morning and night, then rinse with lukewarm water.',
 4.6, 98, 0, 1, 0),
(1, 'Dew Drop Hyaluronic Toner', 'dew-drop-hyaluronic-toner', 'GW-SK-004', 'An alcohol-free hydrating toner that preps skin with multi-weight hyaluronic acid for plumper, dewier skin.', 'Hydrating alcohol-free toner with multi-weight hyaluronic acid.', 'Glowelle', 'Toner', 'All Skin Types', 28.00, 22.00, 54, 'assets/images/placeholder-product.jpg',
 'Aqua, Hyaluronic Acid, Sodium PCA, Glycerin, Chamomile Extract',
 'Deeply hydrates\nPreps skin for serums\nSoothes redness\nRefines pores',
 'Pat onto clean skin with hands or a cotton pad before serum.',
 4.7, 74, 0, 0, 1),
(1, 'Golden Hour SPF 50 Fluid', 'golden-hour-spf-50-fluid', 'GW-SK-005', 'A weightless, invisible-finish broad-spectrum sunscreen fluid that layers beautifully under makeup.', 'Invisible-finish broad-spectrum SPF 50 daily sunscreen.', 'Glowelle', 'Sunscreen', 'All Skin Types', 38.00, NULL, 70, 'assets/images/placeholder-product.jpg',
 'Aqua, Zinc Oxide, Niacinamide, Vitamin E, Green Tea Extract',
 'Broad-spectrum SPF 50 protection\nNo white cast\nLightweight, breathable finish\nAntioxidant support',
 'Apply generously as the last step of your morning routine, reapply every 2 hours in direct sun.',
 4.8, 152, 1, 0, 1),
(1, 'Overnight Renewal Retinol Cream', 'overnight-renewal-retinol-cream', 'GW-SK-006', 'A gentle encapsulated retinol night cream that smooths texture and softens fine lines while you sleep.', 'Encapsulated retinol night cream for smoother, younger-looking skin.', 'Glowelle', 'Night Cream', 'Normal, Combination', 68.00, 58.00, 30, 'assets/images/placeholder-product.jpg',
 'Aqua, Encapsulated Retinol (0.3%), Squalane, Shea Butter, Bakuchiol',
 'Smooths fine lines\nImproves texture\nEvens skin tone\nGentle, encapsulated delivery',
 'Apply a pea-sized amount at night, starting 2-3 times a week and building tolerance.',
 4.7, 89, 0, 0, 0);

-- products — Makeup (category_id 2)
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(2, 'Second Skin Silk Foundation', 'second-skin-silk-foundation', 'GW-MU-001', 'A buildable, skin-loving foundation with a soft-focus finish that feels like nothing at all.', 'Buildable soft-focus foundation with a natural skin finish.', 'Glowelle', 'Foundation', 'All Skin Types', 46.00, NULL, 60, 'assets/images/placeholder-product.jpg',
 'Aqua, Dimethicone, Titanium Dioxide, Hyaluronic Acid, Vitamin E',
 'Buildable medium coverage\nSoft-focus natural finish\nLightweight, breathable wear\nHydrates while wearing',
 'Apply with a damp sponge or brush, building coverage where needed.',
 4.7, 141, 1, 1, 0),
(2, 'Velvet Muse Matte Lipstick', 'velvet-muse-matte-lipstick', 'GW-MU-002', 'A richly pigmented, comfortable matte lipstick that glides on and stays put for hours.', 'Richly pigmented matte lipstick with all-day comfort.', 'Glowelle', 'Lipstick', NULL, 26.00, NULL, 95, 'assets/images/placeholder-product.jpg',
 'Isododecane, Dimethicone, Pigments, Vitamin E, Jojoba Oil',
 'Rich, full-coverage color\nComfortable matte finish\nLong-wearing\nMoisturizing formula',
 'Apply directly from the bullet, starting at the center of the lips and blending outward.',
 4.6, 187, 0, 1, 0),
(2, 'Featherlight Volume Mascara', 'featherlight-volume-mascara', 'GW-MU-003', 'A flexible hourglass brush delivers clump-free volume and lift that lasts all day.', 'Clump-free volumizing mascara with a flexible hourglass brush.', 'Glowelle', 'Mascara', NULL, 24.00, 19.00, 110, 'assets/images/placeholder-product.jpg',
 'Aqua, Beeswax, Carnauba Wax, Panthenol, Vitamin B5',
 'Instant volume and lift\nClump-free application\nSmudge-resistant\nConditions lashes',
 'Wiggle brush from root to tip, applying 1-2 coats to upper and lower lashes.',
 4.5, 96, 0, 0, 1),
(2, 'Soft Focus Blush Duo', 'soft-focus-blush-duo', 'GW-MU-004', 'A silky, buildable powder blush duo that mimics a natural flush from within.', 'Silky powder blush duo for a natural, lit-from-within flush.', 'Glowelle', 'Blush', NULL, 30.00, NULL, 58, 'assets/images/placeholder-product.jpg',
 'Talc, Mica, Pigments, Jojoba Esters, Vitamin E',
 'Natural, buildable flush\nSilky, blendable texture\nLightweight wear\nTravel-friendly duo',
 'Sweep onto the apples of cheeks with a fluffy brush, blending upward and outward.',
 4.6, 62, 0, 0, 0),
(2, 'Sunlit Dimension Eyeshadow Palette', 'sunlit-dimension-eyeshadow-palette', 'GW-MU-005', 'Nine warm, wearable shades in matte and shimmer finishes for effortless day-to-night eyes.', 'Nine-shade warm-tone eyeshadow palette, matte and shimmer.', 'Glowelle', 'Eyeshadow', NULL, 52.00, 42.00, 40, 'assets/images/placeholder-product.jpg',
 'Talc, Mica, Dimethicone, Pigments, Vitamin E',
 'Highly pigmented shades\nBlendable matte and shimmer finishes\nCrease-resistant formula\nDay-to-night versatility',
 'Apply with a blending brush, building intensity in the crease and along the lash line.',
 4.8, 118, 1, 1, 0),
(2, 'Bare Glow Tinted Lip Oil', 'bare-glow-tinted-lip-oil', 'GW-MU-006', 'A nourishing, non-sticky lip oil that delivers a sheer wash of color and glass-like shine.', 'Nourishing tinted lip oil with a glass-like shine.', 'Glowelle', 'Lip Oil', NULL, 22.00, NULL, 75, 'assets/images/placeholder-product.jpg',
 'Jojoba Oil, Vitamin E, Squalane, Pigments, Peppermint Extract',
 'Sheer buildable tint\nGlossy, glass-like shine\nNourishes and softens lips\nNon-sticky feel',
 'Apply directly to lips alone or over lipstick for extra shine.',
 4.7, 84, 0, 0, 1);

-- products — Body Care (category_id 3)
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(3, 'Whipped Shea Body Butter', 'whipped-shea-body-butter', 'GW-BD-001', 'An ultra-rich, whipped body butter that melts into skin for 48-hour softness without the grease.', 'Ultra-rich whipped body butter for 48-hour softness.', 'Glowelle', 'Body Butter', 'Dry', 34.00, NULL, 66, 'assets/images/placeholder-product.jpg',
 'Shea Butter, Cocoa Butter, Jojoba Oil, Vitamin E, Sweet Almond Oil',
 'Deeply nourishes dry skin\n48-hour moisture\nFast-absorbing, non-greasy\nSoftens and smooths',
 'Massage generously over body after showering while skin is still damp.',
 4.8, 132, 1, 1, 0),
(3, 'Silk Petal Body Wash', 'silk-petal-body-wash', 'GW-BD-002', 'A creamy, sulfate-free body wash infused with rose and oat milk for a soft, silky cleanse.', 'Creamy sulfate-free body wash with rose and oat milk.', 'Glowelle', 'Body Wash', 'All Skin Types', 24.00, 19.00, 90, 'assets/images/placeholder-product.jpg',
 'Aqua, Coco-Glucoside, Oat Milk, Rose Extract, Glycerin',
 'Gentle sulfate-free cleanse\nSoothes and softens\nLightly fragranced\nLeaves skin silky-smooth',
 'Lather onto wet skin with hands or a puff, then rinse thoroughly.',
 4.6, 77, 0, 0, 0),
(3, 'Velvet Hands Renewal Cream', 'velvet-hands-renewal-cream', 'GW-BD-003', 'A fast-absorbing hand cream with shea and glycerin that repairs dry, overworked hands.', 'Fast-absorbing hand cream that repairs dry hands.', 'Glowelle', 'Hand Cream', 'Dry, Sensitive', 18.00, NULL, 100, 'assets/images/placeholder-product.jpg',
 'Shea Butter, Glycerin, Panthenol, Allantoin, Vitamin E',
 'Repairs dry, cracked hands\nFast-absorbing formula\nNon-sticky finish\nLong-lasting hydration',
 'Massage into hands and cuticles as needed throughout the day.',
 4.7, 58, 0, 0, 0),
(3, 'Sugar Bloom Body Scrub', 'sugar-bloom-body-scrub', 'GW-BD-004', 'A fine sugar and jojoba bead scrub that buffs away dullness for baby-soft, radiant skin.', 'Fine sugar scrub that buffs away dullness for radiant skin.', 'Glowelle', 'Body Scrub', 'All Skin Types', 30.00, NULL, 48, 'assets/images/placeholder-product.jpg',
 'Sugar, Jojoba Oil, Coconut Oil, Vitamin E, Vanilla Extract',
 'Gently exfoliates dull skin\nSoftens and smooths\nBoosts circulation\nLeaves skin radiant',
 'Massage onto damp skin in circular motions 2-3 times a week, then rinse.',
 4.5, 44, 0, 0, 1),
(3, 'Golden Mist Body Oil', 'golden-mist-body-oil', 'GW-BD-005', 'A fast-absorbing dry body oil that leaves skin with a luminous, non-greasy glow.', 'Fast-absorbing dry oil for a luminous, non-greasy glow.', 'Glowelle', 'Body Oil', 'Dry, Normal', 36.00, 29.00, 52, 'assets/images/placeholder-product.jpg',
 'Squalane, Jojoba Oil, Vitamin E, Sweet Almond Oil, Rosehip Oil',
 'Luminous, healthy glow\nFast-absorbing, non-greasy\nDeeply nourishes\nLightly scented',
 'Massage onto damp or dry skin, focusing on elbows, knees and legs.',
 4.7, 69, 0, 1, 0);

-- products — Hair Care (category_id 4)
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(4, 'Silk Strand Repair Shampoo', 'silk-strand-repair-shampoo', 'GW-HR-001', 'A sulfate-free shampoo that gently cleanses while repairing damage from the inside out.', 'Sulfate-free repairing shampoo for damaged hair.', 'Glowelle', 'Shampoo', NULL, 28.00, NULL, 64, 'assets/images/placeholder-product.jpg',
 'Aqua, Coco-Betaine, Keratin, Biotin, Argan Oil',
 'Repairs damaged strands\nGentle sulfate-free cleanse\nAdds shine and softness\nStrengthens over time',
 'Massage into wet hair, lather, and rinse thoroughly. Follow with conditioner.',
 4.6, 71, 0, 0, 0),
(4, 'Silk Strand Repair Conditioner', 'silk-strand-repair-conditioner', 'GW-HR-002', 'A rich conditioner that detangles and seals the hair cuticle for smooth, glossy strands.', 'Rich repairing conditioner for smooth, glossy hair.', 'Glowelle', 'Conditioner', NULL, 28.00, NULL, 61, 'assets/images/placeholder-product.jpg',
 'Aqua, Cetearyl Alcohol, Keratin, Argan Oil, Shea Butter',
 'Detangles and smooths\nSeals hair cuticle\nAdds glossy shine\nReduces breakage',
 'Apply from mid-length to ends after shampooing, leave for 2-3 minutes, then rinse.',
 4.6, 65, 0, 0, 0),
(4, 'Moonlit Repair Hair Oil', 'moonlit-repair-hair-oil', 'GW-HR-003', 'A lightweight overnight hair oil that nourishes and tames frizz without weighing hair down.', 'Lightweight overnight oil that tames frizz and nourishes.', 'Glowelle', 'Hair Oil', NULL, 32.00, 26.00, 38, 'assets/images/placeholder-product.jpg',
 'Argan Oil, Jojoba Oil, Vitamin E, Rosemary Extract',
 'Tames frizz and flyaways\nNourishes and strengthens\nAdds natural shine\nLightweight, non-greasy',
 'Apply a few drops to damp or dry hair, focusing on mid-lengths and ends.',
 4.7, 53, 0, 0, 1),
(4, 'Volume Bloom Dry Shampoo', 'volume-bloom-dry-shampoo', 'GW-HR-004', 'A weightless dry shampoo that absorbs oil instantly and adds root-lifting volume.', 'Weightless dry shampoo with instant root-lifting volume.', 'Glowelle', 'Dry Shampoo', NULL, 22.00, NULL, 80, 'assets/images/placeholder-product.jpg',
 'Oryza Sativa Starch, Silica, Fragrance',
 'Absorbs oil instantly\nAdds root-lift volume\nNo white residue\nRefreshes hair between washes',
 'Spray at the roots from a distance, wait 1 minute, then massage and brush through.',
 4.4, 39, 0, 0, 0);

-- products — Glow Essentials (category_id 5) — curated, cross-flagged bestsellers
INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, rating, review_count, is_featured, is_bestseller, is_new) VALUES
(5, 'The Glow Edit Discovery Set', 'the-glow-edit-discovery-set', 'GW-GE-001', 'A curated travel-size trio of our best-selling serum, moisturizer and lip oil — the perfect introduction to Glowelle.', 'Curated travel-size trio of Glowelle bestsellers.', 'Glowelle', 'Gift Set', 'All Skin Types', 45.00, NULL, 35, 'assets/images/placeholder-product.jpg',
 'See individual full-size products for complete ingredient lists',
 'Perfect introduction to Glowelle\nTravel-friendly sizes\nCurated bestsellers\nBeautifully packaged for gifting',
 'Use each product as directed on its individual label.',
 4.9, 61, 1, 1, 0),
(5, 'Luminous Skin Duo', 'luminous-skin-duo', 'GW-GE-002', 'Our Vitamin C serum and SPF 50 fluid paired together for the ultimate glow-protecting morning ritual.', 'Vitamin C serum + SPF 50 fluid, paired for daily glow.', 'Glowelle', 'Bundle', 'All Skin Types', 92.00, 79.00, 28, 'assets/images/placeholder-product.jpg',
 'See individual full-size products for complete ingredient lists',
 'Brightens and protects\nSimplifies your morning routine\nSave versus buying separately\nDermatologist-tested duo',
 'Apply serum first, follow with SPF fluid as the final morning step.',
 4.8, 47, 1, 1, 0),
(5, 'Golden Hour Gift Box', 'golden-hour-gift-box', 'GW-GE-003', 'A beautifully boxed edit of body butter, hand cream and body oil for head-to-toe glow.', 'Boxed body-care edit for head-to-toe glow.', 'Glowelle', 'Gift Set', 'All Skin Types', 58.00, NULL, 24, 'assets/images/placeholder-product.jpg',
 'See individual full-size products for complete ingredient lists',
 'Head-to-toe hydration\nReady-to-gift packaging\nCurated fan-favorites\nLuxurious everyday ritual',
 'Use each product as directed on its individual label.',
 4.9, 33, 0, 1, 1);

-- product_images (simple gallery entries reusing the placeholder)
INSERT INTO product_images (product_id, image, sort_order)
SELECT id, 'assets/images/placeholder-product.jpg', 1 FROM products;
INSERT INTO product_images (product_id, image, sort_order)
SELECT id, 'assets/images/placeholder-product.jpg', 2 FROM products;

-- sample approved reviews
INSERT INTO reviews (user_id, product_id, rating, review, status, created_at) VALUES
(2, 1, 5, 'This serum completely changed my morning routine. My skin looks so much brighter within two weeks!', 'approved', '2026-06-02 09:15:00'),
(3, 1, 5, 'Absorbs quickly and never feels sticky. Worth every cent.', 'approved', '2026-06-10 14:02:00'),
(2, 2, 5, 'My skin has never felt this hydrated. A little goes a long way.', 'approved', '2026-05-20 11:30:00'),
(3, 7, 4, 'Beautiful natural finish, though I need a touch of powder by midday.', 'approved', '2026-06-15 16:45:00'),
(2, 11, 5, 'Melts into skin immediately and the scent is so calming.', 'approved', '2026-06-18 08:20:00'),
(3, 16, 4, 'Great everyday shampoo, my hair feels so much softer.', 'approved', '2026-06-05 19:10:00');

-- blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, author, status, created_at) VALUES
('The 5-Step Skincare Routine Every Beginner Needs', 'five-step-skincare-routine-beginners', 'New to skincare? Here is the simple, effective routine our estheticians recommend to every first-timer.', '<p>Building a skincare routine does not need to be complicated. Start with these five essential steps: cleanse, tone, treat, moisturize and protect.</p><p>Begin each day by gently cleansing with a formula suited to your skin type, follow with a hydrating toner, apply a targeted serum for your primary concern, seal everything in with a moisturizer, and always finish with SPF in the morning.</p><p>At night, swap SPF for a treatment product like retinol, and let your skin repair itself while you sleep.</p>', 'assets/images/placeholder-blog.jpg', 'Skincare Tips', 'Glowelle Team', 'published', '2026-05-01 10:00:00'),
('How to Make Your Lipstick Last All Day', 'how-to-make-lipstick-last-all-day', 'Three professional tips for a matte lip that survives coffee, lunch and everything in between.', '<p>The secret to long-lasting lip color starts with prep. Exfoliate lips gently, then apply a thin layer of lip balm and blot before your lipstick.</p><p>Apply color in thin layers, blotting between each one with a tissue. Finish by pressing a light dusting of translucent powder through a tissue over your lips to set the color in place.</p>', 'assets/images/placeholder-blog.jpg', 'Makeup Tips', 'Glowelle Team', 'published', '2026-05-12 10:00:00'),
('Building a Morning Beauty Ritual That Sticks', 'building-a-morning-beauty-ritual', 'A calm, consistent morning routine is the secret to skin that glows all day long.', '<p>The best beauty ritual is the one you will actually stick to. Keep your morning routine to five products or fewer, and lay them out the night before so your routine takes minutes, not decisions.</p><p>Pair your skincare with a moment of stillness — even sixty seconds of gentle massage while applying moisturizer can set a calmer tone for your whole day.</p>', 'assets/images/placeholder-blog.jpg', 'Beauty Routines', 'Glowelle Team', 'published', '2026-05-20 10:00:00'),
('Understanding Hyaluronic Acid: Your Skin''s Best Friend', 'understanding-hyaluronic-acid', 'This much-loved ingredient can hold up to 1000x its weight in water. Here is how to use it correctly.', '<p>Hyaluronic acid is a humectant, meaning it draws moisture into the skin rather than adding oil. For it to work its best, apply it to damp skin and always follow with a moisturizer to lock hydration in.</p><p>Look for formulas with multiple molecular weights of hyaluronic acid, which allow it to hydrate both the surface and deeper layers of skin.</p>', 'assets/images/placeholder-blog.jpg', 'Ingredients', 'Glowelle Team', 'published', '2026-06-01 10:00:00'),
('Introducing: The New Glow Collection', 'introducing-the-new-glow-collection', 'Our biggest launch yet is here — meet the products designed to give you your most radiant skin.', '<p>We are thrilled to introduce the New Glow Collection, a curated edit of our most advanced formulas yet, designed to work in harmony for visibly radiant, healthy-looking skin.</p><p>Shop the collection now and discover why it is already becoming a customer favorite.</p>', 'assets/images/placeholder-blog.jpg', 'Glowelle News', 'Glowelle Team', 'published', '2026-06-20 10:00:00');
