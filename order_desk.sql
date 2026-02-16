-- Create database
CREATE DATABASE IF NOT EXISTS order_desk;
USE order_desk;

-- Admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers table (using English column names for consistency)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    address TEXT,
    password VARCHAR(255),
    role ENUM('customer','admin') DEFAULT 'customer',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
    description TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) DEFAULT 'TZS',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);



-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_phone VARCHAR(20),
    rating INT DEFAULT 5,
    message TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Refunds table
CREATE TABLE refunds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    customer_id INT NOT NULL,
    reason TEXT,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password, name) VALUES 
('admin', '$2y$10$mK7eJ7q7x8L6vF9gH2jKl.1Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E', 'System Administrator');

-- Insert sample customers (password: same as phone number)
INSERT INTO customers (name, phone, email, address, password) VALUES
('John Doe', '0712345678', 'john@example.com', 'Dar es Salaam', '$2y$10$mK7eJ7q7x8L6vF9gH2jKl.1Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E'),
('Jane Smith', '0755123456', 'jane@example.com', 'Arusha', '$2y$10$mK7eJ7q7x8L6vF9gH2jKl.1Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E'),
('Ahmed Hassan', '0788123456', 'ahmed@example.com', 'Mwanza', '$2y$10$mK7eJ7q7x8L6vF9gH2jKl.1Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E');

-- Insert sample orders
INSERT INTO orders (order_id, customer_id, customer_name, phone, product_name, quantity, unit_price, total_price, status) VALUES
('ORD-20240115-001', 1, 'John Doe', '0712345678', 'Laptop Dell', 1, 1500000, 1500000, 'Delivered'),
('ORD-20240116-001', 2, 'Jane Smith', '0755123456', 'Phone Case', 3, 50000, 150000, 'Shipped'),
('ORD-20240117-001', 3, 'Ahmed Hassan', '0788123456', 'Mathematics Book', 5, 15000, 75000, 'Processing');

-- Insert sample payments
INSERT INTO payments (order_id, customer_id, amount, payment_method, status) VALUES
('ORD-20240115-001', 1, 1500000, 'M-Pesa', 'Approved'),
('ORD-20240116-001', 2, 150000, 'Credit Card', 'Approved'),
('ORD-20240117-001', 3, 75000, 'Bank Transfer', 'Pending');

-- Insert sample feedback
INSERT INTO feedback (customer_phone, rating, message) VALUES
('0712345678', 5, 'Great service, my laptop arrived on time!'),
('0755123456', 4, 'Good products, but delivery was a bit late'),
('0788123456', 5, 'Excellent customer support, very helpful!');