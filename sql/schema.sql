CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Admin','User') NOT NULL DEFAULT 'User',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,3) NOT NULL CHECK (price >= 0),
  quantity_available INT NOT NULL CHECK (quantity_available >= 0),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL CHECK (quantity > 0),
  unit_price DECIMAL(10,3) NOT NULL,
  total_price DECIMAL(12,3) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO products (name, price, quantity_available) VALUES
('Coke', 3.990, 50),
('Pepsi', 6.885, 50),
('Water', 0.500, 100),
('Water 2', 0.500, 100),
('Water 3', 0.500, 100),
('Water 4', 0.500, 100),
('Water 5', 0.500, 100),
('Water 6', 0.500, 100),
('Water 7', 0.500, 100),
('Water 8', 0.500, 100),
('Water 9', 0.500, 100),
('Water 10', 0.500, 100),
('Water 11', 0.500, 100),
('Water 12', 0.500, 100),
('Water 13', 0.500, 100);

INSERT INTO users (email, password_hash, role)
VALUES ('admin@example.com', '$2y$10$anjt4.OG.eME3WVWYYI/dOO5vBl7hX2N68/pqpsH5Xe06Y3E78F5O', 'Admin');

INSERT INTO users (email, password_hash, role)
VALUES ('user@example.com', '$2y$10$IVuTp809VBqrcS3Vd8pCZObnT0B6pp9uKwx7dBMjEHYKiCSrq1RWa', 'User');
