-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2025 at 06:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `booknook`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `bid` varchar(3) NOT NULL,
  `pid` varchar(3) NOT NULL,
  `p_location` varchar(255) NOT NULL,
  `b_location` varchar(255) NOT NULL,
  `title` varchar(30) NOT NULL,
  `author` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(10) NOT NULL,
  `featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`bid`, `pid`, `p_location`, `b_location`, `title`, `author`, `description`, `genre`, `featured`) VALUES
('001', '001', 'Covers\\\\001.jpg', 'Books\\\\001.pdf', 'Pride and Prejudice', 'Jane Austen', 'Vanity and pride are different things, though the words are often used synonymously. A person may be proud without being vain.', 'Romance', 0),
('002', '002', 'Covers\\\\002.jpg', 'Books\\\\002.pdf', 'The Great Gatsby', 'F. Scott Fitzgerald', 'There are only the pursued, the pursuing, the busy, and the tired.', 'Mystery', 0),
('003', '003', 'Covers\\\\003.jpg', 'Books\\\\003.pdf', 'Frankenstein', 'Mary Wollstonecraft ', 'I am malicious because I am miserable. Am I not shunned and hated by all mankind?', 'Fiction', 0),
('004', '004', 'Covers\\\\004.jpg', 'Books\\\\004.pdf', 'Dracula', 'Bram Stoker', 'I am longing to be with you, and by the sea, where we can talk together freely and build our castles in the air.', 'Fiction', 0),
('005', '005', 'Covers\\\\005.jpg', 'Books\\\\005.pdf', 'Great Expectations', 'Charles Dickens', 'Heaven knows we need never be ashamed of our tears, for they are rain upon the blinding dust of earth, overlying our hard hearts.', 'Literary', 0),
('006', '006', 'Covers\\\\006.jpg', 'Books\\\\006.pdf', 'IKIGAI', 'Francesc Miralles an', 'We all have an ikigai.', 'Self-Help', 1),
('007', '007', 'Covers\\\\007.jpg', 'Books\\\\007.pdf', 'The Complete Sherlock Holmes', 'Arthur Conan Doyle', 'As a rule, the more bizarre a thing is, the less mysterious it proves to be.', 'Mystery', 1),
('008', '008', 'Covers\\\\008.jpg', 'Books\\\\008.pdf', 'The Little Prince', 'Antoine de Saint Exu', 'The most beautiful things in the world cannot be seen or touched, they are felt with the heart.', 'Fiction', 0),
('009', '009', 'Covers\\\\009.jpg', 'Books\\\\009.pdf', 'The Da Vinci Code', 'Dan Brown', 'Today is one. But there are many tomorrows.', 'Mystery', 1),
('010', '010', 'Covers\\\\010.jpg', 'Books\\\\010.pdf', 'To Kill A Mockingbird', 'Harper Lee', 'Shoot all the bluejays you want, if you can hit them, but remember it is a sin to kill a mockingbird.', 'Fiction', 1),
('011', '011', 'Covers\\\\011.jpg', 'Books\\\\011.pdf', 'The Book Thief', 'Markus Zusak', 'I have hated words and I have loved them, and I hope I have made them right.', 'Fiction', 1),
('012', '012', 'Covers\\\\012.jpg', 'Books\\\\012.pdf', 'The Catcher In The Rye', 'J. D. Salinger', 'Certain things they should stay the way they are.', 'Literary', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `password`, `email`) VALUES
(3, 'Admin', '@Admin123', 'Admin@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `user_admin_library`
--

CREATE TABLE `user_admin_library` (
  `bid` varchar(3) NOT NULL,
  `pid` varchar(3) NOT NULL,
  `p_location` varchar(255) NOT NULL,
  `b_location` varchar(255) NOT NULL,
  `title` varchar(30) NOT NULL,
  `author` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_admin_library`
--

INSERT INTO `user_admin_library` (`bid`, `pid`, `p_location`, `b_location`, `title`, `author`, `description`, `genre`) VALUES
('001', '001', 'Covers\\\\001.jpg', 'Books\\\\001.pdf', 'Pride and Prejudice', 'Jane Austen', 'Vanity and pride are different things, though the words are often used synonymously. A person may be proud without being vain.', 'Romance'),
('002', '002', 'Covers\\\\002.jpg', 'Books\\\\002.pdf', 'The Great Gatsby', 'F. Scott Fitzgerald', 'There are only the pursued, the pursuing, the busy, and the tired.', 'Mystery'),
('003', '003', 'Covers\\\\003.jpg', 'Books\\\\003.pdf', 'Frankenstein', 'Mary Wollstonecraft ', 'I am malicious because I am miserable. Am I not shunned and hated by all mankind?', 'Fiction'),
('004', '004', 'Covers\\\\004.jpg', 'Books\\\\004.pdf', 'Dracula', 'Bram Stoker', 'I am longing to be with you, and by the sea, where we can talk together freely and build our castles in the air.', 'Fiction'),
('005', '005', 'Covers\\\\005.jpg', 'Books\\\\005.pdf', 'Great Expectations', 'Charles Dickens', 'Heaven knows we need never be ashamed of our tears, for they are rain upon the blinding dust of earth, overlying our hard hearts.', 'Literary'),
('006', '006', 'Covers\\\\006.jpg', 'Books\\\\006.pdf', 'IKIGAI', 'Francesc Miralles an', 'We all have an ikigai.', 'Self-Help'),
('007', '007', 'Covers\\\\007.jpg', 'Books\\\\007.pdf', 'The Complete Sherlock Holmes', 'Arthur Conan Doyle', 'As a rule, the more bizarre a thing is, the less mysterious it proves to be.', 'Mystery'),
('008', '008', 'Covers\\\\008.jpg', 'Books\\\\008.pdf', 'The Little Prince', 'Antoine de Saint Exu', 'The most beautiful things in the world cannot be seen or touched, they are felt with the heart.', 'Fiction'),
('009', '009', 'Covers\\\\009.jpg', 'Books\\\\009.pdf', 'The Da Vinci Code', 'Dan Brown', 'Today is one. But there are many tomorrows.', 'Mystery'),
('010', '010', 'Covers\\\\010.jpg', 'Books\\\\010.pdf', 'To Kill A Mockingbird', 'Harper Lee', 'Shoot all the bluejays you want, if you can hit them, but remember it is a sin to kill a mockingbird.', 'Fiction'),
('011', '011', 'Covers\\\\011.jpg', 'Books\\\\011.pdf', 'The Book Thief', 'Markus Zusak', 'I have hated words and I have loved them, and I hope I have made them right.', 'Fiction'),
('012', '012', 'Covers\\\\012.jpg', 'Books\\\\012.pdf', 'The Catcher In The Rye', 'J. D. Salinger', 'Certain things they should stay the way they are.', 'Literary');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`bid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_admin_library`
--
ALTER TABLE `user_admin_library`
  ADD PRIMARY KEY (`bid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
