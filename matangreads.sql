CREATE DATABASE IF NOT EXISTS matangreads;
USE matangreads;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS borrow_requests;
DROP TABLE IF EXISTS book_requests;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  tel_no VARCHAR(20),
  user_type ENUM('admin','user') NOT NULL DEFAULT 'user',
  full_name VARCHAR(150),
  profile_pic VARCHAR(255) NULL,
  credit_balance DECIMAL(8,2) DEFAULT 0.00,
  date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE books (
  book_id INT AUTO_INCREMENT PRIMARY KEY,
  bookname VARCHAR(255) NOT NULL,
  author VARCHAR(255),
  category VARCHAR(100),
  description TEXT,
  image VARCHAR(255),
  quantity INT DEFAULT 1,
  availability TINYINT(1) DEFAULT 1
);


CREATE TABLE borrow_requests (
  borrow_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  borrow_date DATE,
  due_date DATE,
  return_date DATE NULL,
  status ENUM('pending','approved','rejected','returned') DEFAULT 'pending',
  fine DECIMAL(8,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);


CREATE TABLE book_requests (
  req_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  book_title VARCHAR(255),
  author VARCHAR(255),
  category VARCHAR(100),
  notes TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);


CREATE TABLE payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  amount DECIMAL(8,2),
  description VARCHAR(255),
  payment_method VARCHAR(50),
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


INSERT INTO books (bookname, author, category, description, image, quantity)
VALUES
('Harry Potter and the Goblet of Fire','J.K. Rowling','Fiction','Fourth book in the HP series. Harry finds himself unexpectedly entered into the legendary Triwizard Tournament.','HarryPotter4.jpeg', 3),
('Letters to God','Norhafsah Hamid','Fiction','A journey of young girl named Sarah in trying to find her footing in a challenging world.','LetterstoGod.jpg', 2),
('Elite Bunian: Api Bukit Tengkorak','Sabrina Ismail','Malay','Raed, seorang remaja dari alam bunian, sedang menyamar sebagai mata-mata di alam manusia.','EliteBunian.jpg', 4),
('Spy X Family','Tatsuya Endo','Manga','互いに正体を隠した仮初め家族が、受験と世界の危機に立ち向かう痛快ホームコメディ!!','SpyXFamily.jpg', 5),
('Dark Moon: The Blood Altar, Vol. 1','','Manga','The seven most popular boys at Decelis Academy all share a secret―they’re vampires.','DarkMoon.jpg', 3),
('Home Is Where the Bodies Are','Jeneva Rose','Horror',"Three estranged siblings reunite to sort out their mother's estate.",'jenevarose.jpg', 2);

-- Sample Users 
INSERT INTO users (username, password, user_type, full_name, email, credit_balance)
VALUES 
('admin', '$2y$10$Q.P1uB7tE8h5Y2k6z4j0oO5j9Yc3yR7K/jX5hL9g1vA0v2/qA5zV6', 'admin', 'Library Admin', 'admin@matangreads.com', 0.00),
('user1', '$2y$10$Q.P1uB7tE8h5Y2k6z4j0oO5j9Yc3yR7K/jX5hL9g1vA0v2/qA5zV6', 'user', 'Test User One', 'user1@example.com', 0.00),
('ain', '$2y$10$Q.P1uB7tE8h5Y2k6z4j0oO5j9Yc3yR7K/jX5hL9g1vA0v2/qA5zV6', 'user', 'nurain', 'credit@example.com', 50.00), -- User with existing credit
('mel', '$2y$10$Q.P1uB7tE8h5Y2k6z4j0oO5j9Yc3yR7K/jX5hL9g1vA0v2/qA5zV6', 'user', 'melissa', 'borrower@example.com', 0.00);
-- password hash for 'password123' is repeated for simplicity.

