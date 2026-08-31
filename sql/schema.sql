CREATE DATABASE IF NOT EXISTS `CHANGE_DATABASE_NAME`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `CHANGE_DATABASE_NAME`;

CREATE TABLE app_settings (
 setting_key VARCHAR(100) PRIMARY KEY,
 setting_value TEXT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO app_settings(setting_key,setting_value) VALUES
 ('restaurant_logo','assets/logo-reference.jpg'),
 ('restaurant_name','Bismillah Pak Darbar'),
 ('restaurant_tagline','Authentic Pakistani & Indian Cuisine'),
 ('currency_symbol','€'),
 ('restaurant_phone',''),
 ('restaurant_address',''),
 ('restaurant_hours','');

CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','staff','kitchen') NOT NULL DEFAULT 'staff',
 can_view_orders TINYINT(1) NOT NULL DEFAULT 0,
 can_mark_paid TINYINT(1) NOT NULL DEFAULT 0,
 can_complete TINYINT(1) NOT NULL DEFAULT 0,
 can_take_orders TINYINT(1) NOT NULL DEFAULT 0,
 can_manage_tables TINYINT(1) NOT NULL DEFAULT 0,
 can_manage_menu TINYINT(1) NOT NULL DEFAULT 0,
 can_view_reports TINYINT(1) NOT NULL DEFAULT 0,
 can_manage_settings TINYINT(1) NOT NULL DEFAULT 0,
 can_manage_staff TINYINT(1) NOT NULL DEFAULT 0,
 can_view_dashboard TINYINT(1) NOT NULL DEFAULT 0,
 can_use_kitchen TINYINT(1) NOT NULL DEFAULT 0,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE restaurant_tables (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 table_no VARCHAR(30) NOT NULL UNIQUE,
 qr_token CHAR(48) NOT NULL UNIQUE,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 sort_order INT NOT NULL DEFAULT 0,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menu_items (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id INT UNSIGNED NOT NULL,
 name VARCHAR(160) NOT NULL,
 description VARCHAR(500) NULL,
 price DECIMAL(10,2) NOT NULL DEFAULT 0,
 image VARCHAR(255) NULL,
 available TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 table_id INT UNSIGNED NOT NULL,
 status ENUM('new','accepted','preparing','ready','served','completed','cancelled') NOT NULL DEFAULT 'new',
 payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
 payment_method ENUM('cash','card','online','other') NULL,
 paid_by_user_id INT UNSIGNED NULL,
 total DECIMAL(10,2) NOT NULL DEFAULT 0,
 notes VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(table_id) REFERENCES restaurant_tables(id),
 FOREIGN KEY(paid_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX(status), INDEX(payment_status), INDEX(created_at)
) ENGINE=InnoDB;

CREATE TABLE order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id BIGINT UNSIGNED NOT NULL,
 menu_item_id INT UNSIGNED NOT NULL,
 item_name VARCHAR(160) NOT NULL,
 unit_price DECIMAL(10,2) NOT NULL,
 qty INT UNSIGNED NOT NULL,
 notes VARCHAR(300) NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
 FOREIGN KEY(menu_item_id) REFERENCES menu_items(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(120) NOT NULL,
 entity_type VARCHAR(80) NULL,
 entity_id VARCHAR(80) NULL,
 details TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Initial categories
INSERT INTO categories(name,sort_order) VALUES
('Breakfast',1),('Evening Snacks',2),('Tandoori Special',3),
('Main Dishes',4),('Vegetarian Dishes',5),('Rice & Biryani',6),
('Breads',7),('Sweets',8),('Cold Drinks',9),('Hot Drinks',10);

-- Initial menu based on the supplied menu image. Admin can edit/delete/add everything.
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Omelette + Paratha + Tea','Eggs with onion, tomatoes, green chilli & spices.',4.50,1 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chani + Paratha + Tea','Chickpeas cooked with onion, tomatoes, garlic, ginger & spices.',4.50,2 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chana Egg + Paratha + Tea','Chickpeas cooked in spicy gravy with boiled egg, onions, tomatoes & spices.',6.00,3 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Haleem + Paratha + Tea','Slow cooked wheat, lentils, meat with onions, garlic, ginger & traditional spices.',8.00,4 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Plane Paratha','Plain layered paratha.',1.50,5 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Alo Paratha','Stuffed with spiced potatoes, green chilli, coriander.',2.50,6 FROM categories WHERE name='Breakfast';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Lassi','Yogurt drink, sweet or salty.',2.00,7 FROM categories WHERE name='Breakfast';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Veg Pakora (5 pcs)','Mixed vegetables fritters with onion, potato, spinach, spices.',3.00,1 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Pakora (5 pcs)','Chicken pieces marinated with spices, coated & deep fried.',3.50,2 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Shami Tikki (2 pcs)','Minced meat with chana dal, onion, ginger, garlic & spices.',2.00,3 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Alo Tikki (2 pcs)','Mashed potatoes with peas, onion, herbs & spices.',2.00,4 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Kebab','Minced chicken with onion, coriander, garlic, green chilli & spices.',5.00,5 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Samosa Chaat','Samosa topped with chickpeas, yogurt, onion, chutney & spices.',4.00,6 FROM categories WHERE name='Evening Snacks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Gool Gapi (6 pcs)','Crispy puri filled with spiced water, chickpeas, potatoes & chutney.',4.00,7 FROM categories WHERE name='Evening Snacks';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Tandoori Chicken Tikka','Boneless chicken pieces marinated in yogurt, lemon, garlic, ginger & spices.',7.00,1 FROM categories WHERE name='Tandoori Special';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Malai Boti','Tender chicken marinated in cream, yogurt, cheese, garlic, ginger & mild spices.',8.00,2 FROM categories WHERE name='Tandoori Special';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Haryali','Chicken marinated in fresh green herbs, mint, coriander, green chilli, yogurt & spices.',8.00,3 FROM categories WHERE name='Tandoori Special';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Wings (6 pcs)','Marinated wings with garlic, ginger & green spices.',5.00,4 FROM categories WHERE name='Tandoori Special';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Tandoori (2 pcs)','Classic tandoori chicken marinated with yogurt, lemon, garlic & ginger.',6.00,5 FROM categories WHERE name='Tandoori Special';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Beef Kebab','Minced beef mixed with spices, onion & coriander.',8.00,6 FROM categories WHERE name='Tandoori Special';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Karahi','Chicken cooked with onion, tomatoes, garlic, ginger & spices.',8.00,1 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Mutton Karahi','Mutton cooked with onion, tomatoes, garlic, ginger & spices.',10.00,2 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Palak','Chicken cooked with spinach, slow cooked.',8.00,3 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chana Masala','Chickpeas cooked with onion, tomatoes, garlic & spices.',5.00,4 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Dal Chawal','Lentils cooked with onion, tomatoes, garlic & rice.',3.00,5 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Jalfrezi','Chicken with capsicum, onion, tomatoes & spices.',8.00,6 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Sheekh Kebab Karahi','Minced beef kebab with onion, tomatoes & spices.',8.50,7 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Black Pepper','Chicken cooked with onion, capsicum, black pepper.',8.50,8 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Achari','Chicken with onion, tomatoes, capsicum & spices.',8.50,9 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Nihari','Slow cooked beef with bone marrow, spices & herbs.',9.00,10 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Shahi Paneer','Cottage cheese cooked in cream gravy with onion, tomatoes & spices.',8.50,11 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Peshawari Chapli Kebab (3 pcs)','Minced meat with onion, coriander & traditional spices.',8.50,12 FROM categories WHERE name='Main Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Special Peshwari Namkeen','Special dry meat cooked with traditional spices.',12.00,13 FROM categories WHERE name='Main Dishes';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Bindi (Okra)','Cooked with onion, tomatoes & spices.',5.00,1 FROM categories WHERE name='Vegetarian Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Karla (Bitter Gourd)','Bitter gourd cooked with onion, tomatoes & spices.',5.00,2 FROM categories WHERE name='Vegetarian Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Alo Gobi','Potatoes with cauliflower, onion & spices.',4.00,3 FROM categories WHERE name='Vegetarian Dishes';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Green Peas Paneer','Green peas with paneer.',7.00,4 FROM categories WHERE name='Vegetarian Dishes';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Special Platter Rice (2 People)','Rice with chicken, vegetables & 1 Coke each.',20.00,1 FROM categories WHERE name='Rice & Biryani';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Afghani Pulao','Rice cooked with beef, onion, carrots, raisins & spices.',8.50,2 FROM categories WHERE name='Rice & Biryani';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Rice with Chana','Plain rice with chana.',3.00,3 FROM categories WHERE name='Rice & Biryani';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Chicken Biryani','Aromatic rice cooked with chicken, spices, onion, tomatoes.',8.50,4 FROM categories WHERE name='Rice & Biryani';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Beef Biryani','Aromatic rice cooked with beef, onion, tomatoes.',8.50,5 FROM categories WHERE name='Rice & Biryani';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Plain Nan','Freshly baked nan.',1.00,1 FROM categories WHERE name='Breads';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Tandoori Roti','Fresh tandoori roti.',1.50,2 FROM categories WHERE name='Breads';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Lahori Kulcha','Soft stuffed kulcha.',1.50,3 FROM categories WHERE name='Breads';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Butter Nan','Nan with butter.',1.75,4 FROM categories WHERE name='Breads';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Keema Nan','Nan stuffed with minced meat.',4.00,5 FROM categories WHERE name='Breads';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Peshwari Nan','Sweet nutty Peshwari nan.',3.50,6 FROM categories WHERE name='Breads';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Jalebi','Crispy sweet jalebi.',2.00,1 FROM categories WHERE name='Sweets';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Kheer','Traditional rice pudding.',3.00,2 FROM categories WHERE name='Sweets';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Lassi','Sweet or salty lassi.',2.00,3 FROM categories WHERE name='Sweets';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Gajar Halwa','Carrot halwa.',4.00,4 FROM categories WHERE name='Sweets';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Mango Lassi','Mango yogurt drink.',2.50,1 FROM categories WHERE name='Cold Drinks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Banana Milkshake','Fresh banana milkshake.',2.50,2 FROM categories WHERE name='Cold Drinks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Mint & Lemon Juice','Fresh mint and lemon drink.',2.00,3 FROM categories WHERE name='Cold Drinks';

INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Karak Tea','Pakistani karak chai.',1.09,1 FROM categories WHERE name='Hot Drinks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Masala Tea','Masala chai.',1.20,2 FROM categories WHERE name='Hot Drinks';
INSERT INTO menu_items(category_id,name,description,price,sort_order)
SELECT id,'Karak Chai','Karak chai.',1.20,3 FROM categories WHERE name='Hot Drinks';

-- Generate initial tables; admin can add/remove later.
INSERT INTO restaurant_tables(table_no,qr_token)
SELECT LPAD(n,2,'0'), REPLACE(REPLACE(REPLACE(UUID(),'-',''),'0','a'),'1','b')
FROM (
 SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) x;
