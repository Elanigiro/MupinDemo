-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Oct 22, 2022 at 03:52 PM
-- Server version: 10.9.2-MariaDB-1:10.9.2+maria~ubu2204
-- PHP Version: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mupin`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_book` (IN `id` VARCHAR(20) CHARSET utf8mb4, IN `title` VARCHAR(100) CHARSET utf8mb4, IN `author_firstname` VARCHAR(50) CHARSET utf8mb4, IN `author_lastname` VARCHAR(50) CHARSET utf8mb4, IN `publisher_name` VARCHAR(100) CHARSET utf8mb4, IN `year_val` VARCHAR(4), IN `pages` SMALLINT(5), IN `isbn` VARCHAR(13), IN `notes` TEXT CHARSET utf8mb4, IN `url` VARCHAR(2048))  MODIFIES  DATA SQL SECURITY INVOKER BEGIN

DECLARE nested_transaction INT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    IF NOT nested_transaction
    THEN
        ROLLBACK;
    END IF;
    
    RESIGNAL;
END;

SET nested_transaction = @@in_transaction;

IF NOT nested_transaction
THEN
	START TRANSACTION;
END IF;

SELECT p.pub_id 
INTO @pub_id_var 
FROM publisher p 
WHERE p.pub_name = publisher_name 
LIMIT 1;

SELECT a.aut_id
INTO @aut_id_var 
FROM author a 
WHERE (a.first_name = author_firstname) AND (a.last_name = author_lastname) 
LIMIT 1;


IF @pub_id_var IS NULL 
THEN 
	INSERT INTO publisher(pub_name) VALUES(publisher_name);
    SELECT LAST_INSERT_ID()
	INTO @pub_id_var;
END IF;

IF @aut_id_var IS NULL 
THEN 
	INSERT INTO author(first_name, last_name) VALUES (author_firstname, author_lastname);
    SELECT LAST_INSERT_ID() 
	INTO @aut_id_var;
END IF;

SELECT c.cat_id INTO @book_cat_id FROM category c WHERE c.table_name = 'book';
INSERT INTO inventory(inventory.inv_id, inventory.cat) VALUES (id, @book_cat_id);

INSERT INTO book(book.book_id, book.title, book.author, book.publisher, book.year, book.pages, book.isbn, book.notes, book.url)
	   VALUES (id, title, @aut_id_var, @pub_id_var, year_val, pages, isbn, notes, url);
       
IF NOT nested_transaction
THEN
	COMMIT;
END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_computer` (IN `id` VARCHAR(20) CHARSET utf8mb4, IN `company_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `model` VARCHAR(100) CHARSET utf8mb4, IN `year_val` VARCHAR(4), IN `cpu_fk` VARCHAR(100) CHARSET utf8mb4, IN `hz` VARCHAR(50), IN `ram` VARCHAR(50), IN `storage_val` VARCHAR(50), IN `os_name_fk` VARCHAR(50) CHARSET utf8mb4, IN `os_version_fk` VARCHAR(20) CHARSET utf8mb4, IN `notes` TEXT CHARSET utf8mb4, IN `url` VARCHAR(2048))  MODIFIES  DATA SQL SECURITY INVOKER BEGIN

DECLARE nested_transaction INT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    IF NOT nested_transaction
    THEN
        ROLLBACK;
    END IF;
    
    RESIGNAL;
END;

SET nested_transaction = @@in_transaction;

IF NOT nested_transaction
THEN
	START TRANSACTION;
END IF;

SELECT c.company_id
INTO @company_id_var 
FROM company c 
WHERE c.company_name = company_name_fk
LIMIT 1;

SELECT c.cpu_id
INTO @cpu_id_var 
FROM cpu c
WHERE c.cpu_name = cpu_fk
LIMIT 1;

SELECT o.os_id
INTO @os_id_var 
FROM os o
WHERE (o.os_name = os_name_fk) AND (o.os_version = os_version_fk)
LIMIT 1;


IF @company_id_var IS NULL 
THEN 
	INSERT INTO company(company.company_name) VALUES(company_name_fk);
    SELECT LAST_INSERT_ID()
	INTO @company_id_var;
END IF;

IF @cpu_id_var IS NULL 
THEN 
	INSERT INTO cpu(cpu.cpu_name) VALUES (cpu_fk);
    SELECT LAST_INSERT_ID() 
	INTO @cpu_id_var;
END IF;

IF (@os_id_var IS NULL) AND ((os_name_fk IS NOT NULL) AND (os_version_fk IS NOT NULL)) 
THEN 
	INSERT INTO os(os.os_name, os.os_version) VALUES (os_name_fk, os_version_fk);
    SELECT LAST_INSERT_ID() 
	INTO @os_id_var;
END IF;

SELECT c.cat_id INTO @computer_cat_id FROM category c WHERE c.table_name = 'computer';
INSERT INTO inventory(inventory.inv_id, inventory.cat) VALUES (id, @computer_cat_id);

INSERT INTO computer(computer.computer_id, computer.company, computer.model, computer.year, computer.cpu, computer.clock_hz, computer.ram_byte, computer.storage_byte, computer.os, computer.notes, computer.url)
	   VALUES (id, @company_id_var, model, year_val, @cpu_id_var, hz, ram, storage_val, @os_id_var, notes, url);
       
IF NOT nested_transaction
THEN
	COMMIT;
END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_magazine` (IN `id` VARCHAR(20) CHARSET utf8mb4, IN `title` VARCHAR(100) CHARSET utf8mb4, IN `magno` INT(10) UNSIGNED, IN `year_val` VARCHAR(4), IN `pub_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `notes` TEXT CHARSET utf8mb4, IN `url` VARCHAR(2048))  MODIFIES  DATA SQL SECURITY INVOKER BEGIN

DECLARE nested_transaction INT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    IF NOT nested_transaction
    THEN
        ROLLBACK;
    END IF;
    
    RESIGNAL;
END;

SET nested_transaction = @@in_transaction;

IF NOT nested_transaction
THEN
	START TRANSACTION;
END IF;

SELECT p.pub_id
INTO @pub_id_var 
FROM publisher p
WHERE p.pub_name = pub_name_fk
LIMIT 1;

IF @pub_id_var IS NULL 
THEN 
	INSERT INTO publisher(publisher.pub_name) VALUES(pub_name_fk);
    SELECT LAST_INSERT_ID()
	INTO @pub_id_var;
END IF;

SELECT c.cat_id INTO @magazine_cat_id FROM category c WHERE c.table_name = 'magazine';
INSERT INTO inventory(inventory.inv_id, inventory.cat) VALUES (id, @magazine_cat_id);

INSERT INTO magazine(magazine.mag_id, magazine.title, magazine.mag_no, magazine.year, magazine.publisher, magazine.notes, magazine.url)
	   VALUES (id, title, magno, year_val, @pub_id_var, notes, url);
       
IF NOT nested_transaction
THEN
	COMMIT;
END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_peripheral` (IN `id` VARCHAR(20) CHARSET utf8mb4, IN `company_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `model` VARCHAR(100) CHARSET utf8mb4, IN `type_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `notes` TEXT CHARSET utf8mb4, IN `url` VARCHAR(2048))  MODIFIES  DATA SQL SECURITY INVOKER BEGIN

DECLARE nested_transaction INT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    IF NOT nested_transaction
    THEN
        ROLLBACK;
    END IF;
    
    RESIGNAL;
END;

SET nested_transaction = @@in_transaction;

IF NOT nested_transaction
THEN
	START TRANSACTION;
END IF;

SELECT c.company_id
INTO @company_id_var 
FROM company c 
WHERE c.company_name = company_name_fk
LIMIT 1;

SELECT pt.type_id
INTO @pt_id_var 
FROM peripheraltype pt
WHERE pt.type_name = type_name_fk
LIMIT 1;

IF @company_id_var IS NULL 
THEN 
	INSERT INTO company(company.company_name) VALUES(company_name_fk);
    SELECT LAST_INSERT_ID()
	INTO @company_id_var;
END IF;

IF @pt_id_var IS NULL 
THEN 
	INSERT INTO peripheraltype(peripheraltype.type_name) VALUES (type_name_fk);
    SELECT LAST_INSERT_ID() 
	INTO @pt_id_var;
END IF;

SELECT c.cat_id INTO @peripheral_cat_id FROM category c WHERE c.table_name = 'peripheral';
INSERT INTO inventory(inventory.inv_id, inventory.cat) VALUES (id, @peripheral_cat_id);

INSERT INTO peripheral(peripheral.periph_id, peripheral.company, peripheral.model, peripheral.type, peripheral.notes, peripheral.url)
	   VALUES (id, @company_id_var, model, @pt_id_var, notes, url);
       
IF NOT nested_transaction
THEN
	COMMIT;
END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_software` (IN `id` VARCHAR(20) CHARSET utf8mb4, IN `company_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `title` VARCHAR(100) CHARSET utf8mb4, IN `os_name_fk` VARCHAR(50) CHARSET utf8mb4, IN `os_version_fk` VARCHAR(20) CHARSET utf8mb4, IN `type_name_fk` VARCHAR(100) CHARSET utf8mb4, IN `storage_val` VARCHAR(50) CHARSET utf8mb4, IN `notes` TEXT CHARSET utf8mb4, IN `url` VARCHAR(2048))  MODIFIES  DATA SQL SECURITY INVOKER BEGIN

DECLARE nested_transaction INT;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    IF NOT nested_transaction
    THEN
        ROLLBACK;
    END IF;
    
    RESIGNAL;
END;

SET nested_transaction = @@in_transaction;

IF NOT nested_transaction
THEN
	START TRANSACTION;
END IF;

SELECT c.company_id
INTO @company_id_var 
FROM company c 
WHERE c.company_name = company_name_fk
LIMIT 1;

SELECT o.os_id
INTO @os_id_var 
FROM os o
WHERE (o.os_name = os_name_fk) AND (o.os_version = os_version_fk)
LIMIT 1;

SELECT st.type_id
INTO @st_id_var 
FROM softwaretype st
WHERE st.type_name = type_name_fk
LIMIT 1;

IF @company_id_var IS NULL 
THEN 
	INSERT INTO company(company.company_name) VALUES(company_name_fk);
    SELECT LAST_INSERT_ID()
	INTO @company_id_var;
END IF;

IF @os_id_var IS NULL 
THEN 
	INSERT INTO os(os.os_name, os.os_version) VALUES (os_name_fk, os_version_fk);
    SELECT LAST_INSERT_ID() 
	INTO @os_id_var;
END IF;

IF @st_id_var IS NULL 
THEN 
	INSERT INTO softwaretype(softwaretype.type_name) VALUES (type_name_fk);
    SELECT LAST_INSERT_ID() 
	INTO @st_id_var;
END IF;

SELECT c.cat_id INTO @software_cat_id FROM category c WHERE c.table_name = 'software';
INSERT INTO inventory(inventory.inv_id, inventory.cat) VALUES (id, @software_cat_id);

INSERT INTO software(software.software_id, software.company, software.title, software.os, software.type, software.storage_device, software.notes, software.url)
	   VALUES (id, @company_id_var, title, @os_id_var, @st_id_var, storage_val, notes, url);
       
IF NOT nested_transaction
THEN
	COMMIT;
END IF;

END$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_book` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT b.book_id, b.title, b.author, b.publisher, b.year, b.pages, b.isbn, b.notes, b.url,
	   (COALESCE(book_main.relevance, 0) + COALESCE(book_second.relevance, 0) + COALESCE(author_main.relevance, 0) + COALESCE(publisher_main.relevance, 0)) as relevance
FROM book b
LEFT OUTER JOIN (SELECT b1.book_id as book_id, (3 * (MATCH(b1.book_id,b1.title,b1.isbn)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
        FROM book b1
        WHERE MATCH(b1.book_id,b1.title,b1.isbn)AGAINST(searchtxt IN BOOLEAN MODE)) AS book_main
    ON b.book_id = book_main.book_id
LEFT OUTER JOIN (SELECT b2.book_id as book_id, (1 * (MATCH(b2.notes,b2.url)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM book b2
				WHERE MATCH(b2.notes,b2.url)AGAINST(searchtxt IN BOOLEAN MODE)) AS book_second
 	ON b.book_id = book_second.book_id
LEFT OUTER JOIN (SELECT a.aut_id as aut_id, (2 * (MATCH(a.first_name,a.last_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM author a
				WHERE MATCH(a.first_name,a.last_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS author_main
    ON b.author = author_main.aut_id
LEFT OUTER JOIN (SELECT p.pub_id as pub_id, (2 * (MATCH(p.pub_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM publisher p
				 WHERE MATCH(p.pub_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS publisher_main
    ON b.publisher = publisher_main.pub_id
WHERE (COALESCE(book_main.relevance, 0) + COALESCE(book_second.relevance, 0) + COALESCE(author_main.relevance, 0) + COALESCE(publisher_main.relevance, 0)) <> 0
ORDER BY relevance DESC, b.title ASC$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_computer` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT c.computer_id, c.company, c.model, c.year, c.cpu, c.clock_hz, c.ram_byte, c.storage_byte, c.os, c.notes, c.url,
	   (COALESCE(computer_main.relevance, 0) + COALESCE(computer_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(cpu_main.relevance, 0) + COALESCE(os_main.relevance, 0)) as relevance
FROM computer c
LEFT OUTER JOIN (SELECT c1.computer_id as computer_id, (3 * (MATCH(c1.computer_id, c1.model)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM computer c1
                 WHERE MATCH(c1.computer_id, c1.model)AGAINST(searchtxt IN BOOLEAN MODE)) AS computer_main
    ON c.computer_id = computer_main.computer_id
LEFT OUTER JOIN (SELECT c2.computer_id as computer_id, (1 * (MATCH(c2.notes, c2.url)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM computer c2
				WHERE MATCH(c2.notes, c2.url)AGAINST(searchtxt IN BOOLEAN MODE)) AS computer_second
 	ON c.computer_id = computer_second.computer_id
LEFT OUTER JOIN (SELECT cpn.company_id as company_id, (2 * (MATCH(cpn.company_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM company cpn
				WHERE MATCH(cpn.company_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS company_main
    ON c.company = company_main.company_id
LEFT OUTER JOIN (SELECT cpu.cpu_id as cpu_id, (2 * (MATCH(cpu.cpu_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM cpu
				 WHERE MATCH(cpu.cpu_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS cpu_main
    ON c.cpu = cpu_main.cpu_id
LEFT OUTER JOIN (SELECT o.os_id as os_id, (2 * (MATCH(o.os_name, o.os_version)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM os o
				 WHERE MATCH(o.os_name, o.os_version)AGAINST(searchtxt IN BOOLEAN MODE)) AS os_main
    ON c.os = os_main.os_id
WHERE (COALESCE(computer_main.relevance, 0) + COALESCE(computer_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(cpu_main.relevance, 0) + COALESCE(os_main.relevance, 0)) <> 0
ORDER BY relevance DESC, c.model ASC$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_magazine` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT m.mag_id, m.title, m.mag_no, m.year, m.publisher, m.notes, m.url,
	   (COALESCE(magazine_main.relevance, 0) + COALESCE(magazine_second.relevance, 0) + COALESCE(publisher_main.relevance, 0)) as relevance
FROM magazine m
LEFT OUTER JOIN (SELECT m1.mag_id as mag_id, (3 * (MATCH(m1.mag_id, m1.title)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
        FROM magazine m1
        WHERE MATCH(m1.mag_id, m1.title)AGAINST(searchtxt IN BOOLEAN MODE)) AS magazine_main
    ON m.mag_id = magazine_main.mag_id
LEFT OUTER JOIN (SELECT m2.mag_id as mag_id, (1 * (MATCH(m2.notes, m2.url)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM magazine m2
				WHERE MATCH(m2.notes, m2.url)AGAINST(searchtxt IN BOOLEAN MODE)) AS magazine_second
 	ON m.mag_id = magazine_second.mag_id
LEFT OUTER JOIN (SELECT p.pub_id as pub_id, (2 * (MATCH(p.pub_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM publisher p
				WHERE MATCH(p.pub_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS publisher_main
    ON m.publisher = publisher_main.pub_id
WHERE (COALESCE(magazine_main.relevance, 0) + COALESCE(magazine_second.relevance, 0) + COALESCE(publisher_main.relevance, 0)) <> 0
ORDER BY relevance DESC, m.title ASC, m.mag_no ASC$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_peripheral` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT p.periph_id, p.company, p.model, p.type, p.notes, p.url,
	   (COALESCE(peripheral_main.relevance, 0) + COALESCE(peripheral_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(pertype_main.relevance, 0)) as relevance
FROM peripheral p
LEFT OUTER JOIN (SELECT p1.periph_id as periph_id, (3 * (MATCH(p1.periph_id, p1.model)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
        FROM peripheral p1
        WHERE MATCH(p1.periph_id, p1.model)AGAINST(searchtxt IN BOOLEAN MODE)) AS peripheral_main
    ON p.periph_id = peripheral_main.periph_id
LEFT OUTER JOIN (SELECT p2.periph_id as periph_id, (1 * (MATCH(p2.notes, p2.url)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM peripheral p2
				WHERE MATCH(p2.notes, p2.url)AGAINST(searchtxt IN BOOLEAN MODE)) AS peripheral_second
 	ON p.periph_id = peripheral_second.periph_id
LEFT OUTER JOIN (SELECT c.company_id as company_id, (2 * (MATCH(c.company_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM company c
				WHERE MATCH(c.company_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS company_main
    ON p.company = company_main.company_id
LEFT OUTER JOIN (SELECT pt.type_id as type_id, (2 * (MATCH(pt.type_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM peripheraltype pt
				 WHERE MATCH(pt.type_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS pertype_main
    ON p.type = pertype_main.type_id
WHERE (COALESCE(peripheral_main.relevance, 0) + COALESCE(peripheral_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(pertype_main.relevance, 0)) <> 0
ORDER BY relevance DESC, p.model ASC$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_software` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT s.software_id, s.company, s.title, s.os, s.type, s.storage_device, s.notes, s.url,
	   (COALESCE(software_main.relevance, 0) + COALESCE(software_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(os_main.relevance, 0) + COALESCE(stype_main.relevance, 0)) as relevance
FROM software s
LEFT OUTER JOIN (SELECT s1.software_id as software_id, (3 * (MATCH(s1.software_id, s1.title)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM software s1
                 WHERE MATCH(s1.software_id, s1.title)AGAINST(searchtxt IN BOOLEAN MODE)) AS software_main
    ON s.software_id = software_main.software_id
LEFT OUTER JOIN (SELECT s2.software_id as software_id, (1 * (MATCH(s2.storage_device, s2.notes, s2.url)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM software s2
				WHERE MATCH(s2.storage_device, s2.notes, s2.url)AGAINST(searchtxt IN BOOLEAN MODE)) AS software_second
 	ON s.software_id = software_second.software_id
LEFT OUTER JOIN (SELECT cpn.company_id as company_id, (2 * (MATCH(cpn.company_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
				FROM company cpn
				WHERE MATCH(cpn.company_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS company_main
    ON s.company = company_main.company_id
LEFT OUTER JOIN (SELECT o.os_id as os_id, (2 * (MATCH(o.os_name, o.os_version)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM os o
				 WHERE MATCH(o.os_name, o.os_version)AGAINST(searchtxt IN BOOLEAN MODE)) AS os_main
    ON s.os = os_main.os_id
LEFT OUTER JOIN (SELECT st.type_id as type_id, (2 * (MATCH(st.type_name)AGAINST(searchtxt IN BOOLEAN MODE))) as relevance
                 FROM softwaretype st
				 WHERE MATCH(st.type_name)AGAINST(searchtxt IN BOOLEAN MODE)) AS stype_main
    ON s.type = stype_main.type_id
WHERE (COALESCE(software_main.relevance, 0) + COALESCE(software_second.relevance, 0) + COALESCE(company_main.relevance, 0) + COALESCE(os_main.relevance, 0) + COALESCE(stype_main.relevance, 0)) <> 0
ORDER BY relevance DESC, s.title ASC, s.storage_device ASC$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_tag_all` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT i.inv_id, i.cat, SUM(tag_main.relevance) as relevance
FROM inventory i
INNER JOIN tag_inventory ti ON i.inv_id = ti.inv
INNER JOIN (SELECT t.tag_id as tag_id, MATCH(t.tag_string)AGAINST(searchtxt IN BOOLEAN MODE) as relevance
			FROM tag t
			WHERE MATCH(t.tag_string)AGAINST(searchtxt IN BOOLEAN MODE)) as tag_main
ON ti.tag = tag_main.tag_id
GROUP BY i.inv_id$$

CREATE DEFINER=`root`@`%` PROCEDURE `search_tag_catid` (IN `searchtxt` VARCHAR(255) CHARSET utf8mb4, IN `catname` VARCHAR(255) CHARSET utf8mb4)  READS SQL DATA SQL SECURITY INVOKER SELECT i.inv_id, i.cat, SUM(tag_main.relevance) as relevance
FROM inventory i
INNER JOIN category c ON (catname = c.table_name) AND (i.cat = c.cat_id)
INNER JOIN tag_inventory ti ON i.inv_id = ti.inv
INNER JOIN (SELECT t.tag_id as tag_id, MATCH(t.tag_string)AGAINST(searchtxt IN BOOLEAN MODE) as relevance
			FROM tag t
			WHERE MATCH(t.tag_string)AGAINST(searchtxt IN BOOLEAN MODE)) as tag_main
ON ti.tag = tag_main.tag_id
GROUP BY i.inv_id$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `aut_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` char(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `author` int(10) UNSIGNED NOT NULL,
  `publisher` int(10) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `pages` smallint(5) UNSIGNED DEFAULT NULL,
  `isbn` char(13) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `url` varchar(2048) DEFAULT NULL
) ;

--
-- Triggers `book`
--
DELIMITER $$
CREATE TRIGGER `BOOK_INV_CHECK` BEFORE INSERT ON `book` FOR EACH ROW BEGIN

SELECT c.cat_id INTO @expected_cat_id FROM category c WHERE c.table_name = 'book';

SELECT i.cat INTO @actual_cat_id FROM inventory i WHERE i.inv_id = NEW.book_id;

IF (@expected_cat_id <> @actual_cat_id)
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Record already in inventory in different category!';
END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(10) UNSIGNED NOT NULL,
  `table_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `table_name`) VALUES
(4, 'book'),
(2, 'computer'),
(3, 'magazine'),
(5, 'peripheral'),
(6, 'software');

--
-- Triggers `category`
--
DELIMITER $$
CREATE TRIGGER `check_table_exists` BEFORE INSERT ON `category` FOR EACH ROW BEGIN

IF (SELECT COUNT(*) 
	FROM information_schema.tables t
	WHERE 
		t.table_schema = 'mupin' 
  		AND 
		t.table_name = NEW.table_name) = 0
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Invalid table_name: table not found!';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL
) ;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `company_name`, `notes`) VALUES
(57, 'Fictional', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `computer`
--

CREATE TABLE `computer` (
  `computer_id` char(20) NOT NULL,
  `company` int(10) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` year(4) NOT NULL,
  `cpu` int(10) UNSIGNED NOT NULL,
  `clock_hz` varchar(50) NOT NULL,
  `ram_byte` varchar(50) NOT NULL,
  `storage_byte` varchar(50) DEFAULT NULL,
  `os` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `url` varchar(2048) DEFAULT NULL
) ;

--
-- Dumping data for table `computer`
--

INSERT INTO `computer` (`computer_id`, `company`, `model`, `year`, `cpu`, `clock_hz`, `ram_byte`, `storage_byte`, `os`, `notes`, `url`) VALUES
('CWMU0U8468567LP0LWYX', 57, 'Generic Model', 1970, 26, '100000000', '1024', '1000', 24, NULL, NULL);

--
-- Triggers `computer`
--
DELIMITER $$
CREATE TRIGGER `COMPUTER_INV_CHECK` BEFORE INSERT ON `computer` FOR EACH ROW BEGIN

SELECT c.cat_id INTO @expected_cat_id FROM category c WHERE c.table_name = 'computer';

SELECT i.cat INTO @actual_cat_id FROM inventory i WHERE i.inv_id = NEW.computer_id;

IF (@expected_cat_id <> @actual_cat_id)
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Record already in inventory in different category!';
END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cpu`
--

CREATE TABLE `cpu` (
  `cpu_id` int(10) UNSIGNED NOT NULL,
  `cpu_name` varchar(100) NOT NULL,
  `first_manufactured` year(4) DEFAULT NULL,
  `company` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL
) ;

--
-- Dumping data for table `cpu`
--

INSERT INTO `cpu` (`cpu_id`, `cpu_name`, `first_manufactured`, `company`, `notes`) VALUES
(26, 'Generic CPU', NULL, NULL, NULL);

--
-- Triggers `cpu`
--
DELIMITER $$
CREATE TRIGGER `check_no_other_null_duplicate` BEFORE INSERT ON `cpu` FOR EACH ROW BEGIN

IF 
	(SELECT COUNT(*) 
        FROM cpu c
        WHERE 
            c.cpu_name <=> NEW.cpu_name
            AND 
            c.first_manufactured <=> NEW.first_manufactured
            AND
            c.company <=> NEW.company) <> 0
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Invalid cpu: duplicate value!';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inv_id` char(20) NOT NULL,
  `cat` int(10) UNSIGNED NOT NULL
) ;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inv_id`, `cat`) VALUES
('CWMU0U8468567LP0LWYX', 2);

-- --------------------------------------------------------

--
-- Table structure for table `magazine`
--

CREATE TABLE `magazine` (
  `mag_id` char(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `mag_no` int(10) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `publisher` int(10) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `url` varchar(2048) DEFAULT NULL
) ;

--
-- Triggers `magazine`
--
DELIMITER $$
CREATE TRIGGER `MAG_INV_CHECK` BEFORE INSERT ON `magazine` FOR EACH ROW BEGIN

SELECT c.cat_id INTO @expected_cat_id FROM category c WHERE c.table_name = 'magazine';

SELECT i.cat INTO @actual_cat_id FROM inventory i WHERE i.inv_id = NEW.mag_id;

IF (@expected_cat_id <> @actual_cat_id)
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Record already in inventory in different category!';
END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `os`
--

CREATE TABLE `os` (
  `os_id` int(10) UNSIGNED NOT NULL,
  `os_name` varchar(50) NOT NULL,
  `os_version` varchar(20) NOT NULL,
  `company` int(10) UNSIGNED DEFAULT NULL
) ;

--
-- Dumping data for table `os`
--

INSERT INTO `os` (`os_id`, `os_name`, `os_version`, `company`) VALUES
(24, 'Generic OS', '1.0', NULL);

--
-- Triggers `os`
--
DELIMITER $$
CREATE TRIGGER `os_check_no_other_null_duplicate` BEFORE INSERT ON `os` FOR EACH ROW BEGIN

IF 
	(SELECT COUNT(*) 
        FROM os o
        WHERE 
            o.os_name <=> NEW.os_name
            AND 
            o.os_version <=> NEW.os_version
            AND
            o.company <=> NEW.company) <> 0
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Invalid os: duplicate value!';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `peripheral`
--

CREATE TABLE `peripheral` (
  `periph_id` char(20) NOT NULL,
  `company` int(10) UNSIGNED NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` int(10) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `url` varchar(2048) DEFAULT NULL
) ;

--
-- Triggers `peripheral`
--
DELIMITER $$
CREATE TRIGGER `PER_INV_CHECK` BEFORE INSERT ON `peripheral` FOR EACH ROW BEGIN

SELECT c.cat_id INTO @expected_cat_id FROM category c WHERE c.table_name = 'peripheral';

SELECT i.cat INTO @actual_cat_id FROM inventory i WHERE i.inv_id = NEW.periph_id;

IF (@expected_cat_id <> @actual_cat_id)
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Record already in inventory in different category!';
END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `peripheraltype`
--

CREATE TABLE `peripheraltype` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `type_name` varchar(100) NOT NULL
) ;

--
-- Dumping data for table `peripheraltype`
--

INSERT INTO `peripheraltype` (`type_id`, `type_name`) VALUES
(1, 'Floppy Drive'),
(8, 'GPU'),
(5, 'Keyboard'),
(7, 'Monitor'),
(6, 'Mouse'),
(2, 'Optical Disk Drive'),
(3, 'Printer'),
(4, 'Scanner');

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `pub_id` int(10) UNSIGNED NOT NULL,
  `pub_name` varchar(100) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `software`
--

CREATE TABLE `software` (
  `software_id` char(20) NOT NULL,
  `company` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `os` int(10) UNSIGNED NOT NULL,
  `type` int(10) UNSIGNED NOT NULL,
  `storage_device` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `url` varchar(2048) DEFAULT NULL
) ;

--
-- Triggers `software`
--
DELIMITER $$
CREATE TRIGGER `SOFT_INV_CHECK` BEFORE INSERT ON `software` FOR EACH ROW BEGIN

SELECT c.cat_id INTO @expected_cat_id FROM category c WHERE c.table_name = 'software';

SELECT i.cat INTO @actual_cat_id FROM inventory i WHERE i.inv_id = NEW.software_id;

IF (@expected_cat_id <> @actual_cat_id)
THEN
	SIGNAL SQLSTATE '42S02' SET MESSAGE_TEXT='Record already in inventory in different category!';
END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `softwaretype`
--

CREATE TABLE `softwaretype` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `type_name` varchar(100) NOT NULL
) ;

--
-- Dumping data for table `softwaretype`
--

INSERT INTO `softwaretype` (`type_id`, `type_name`) VALUES
(4, 'Driver'),
(1, 'Game'),
(3, 'IT'),
(2, 'Office');

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(10) UNSIGNED NOT NULL,
  `tag_string` varchar(50) NOT NULL
) ;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`tag_id`, `tag_string`) VALUES
(52, '70s'),
(51, 'Fictional PCs'),
(50, 'Private Collection');

-- --------------------------------------------------------

--
-- Table structure for table `tag_inventory`
--

CREATE TABLE `tag_inventory` (
  `inv` char(20) NOT NULL,
  `tag` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tag_inventory`
--

INSERT INTO `tag_inventory` (`inv`, `tag`) VALUES
('CWMU0U8468567LP0LWYX', 50),
('CWMU0U8468567LP0LWYX', 51),
('CWMU0U8468567LP0LWYX', 52);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`aut_id`);
ALTER TABLE `author` ADD FULLTEXT KEY `first_name` (`first_name`,`last_name`);

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `BOOK_AUT_FK` (`author`),
  ADD KEY `BOOK_PUB_FK` (`publisher`);
ALTER TABLE `book` ADD FULLTEXT KEY `book_id` (`book_id`,`title`,`isbn`);
ALTER TABLE `book` ADD FULLTEXT KEY `notes` (`notes`,`url`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `table_name` (`table_name`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `company_name` (`company_name`);
ALTER TABLE `company` ADD FULLTEXT KEY `company_name_2` (`company_name`);

--
-- Indexes for table `computer`
--
ALTER TABLE `computer`
  ADD PRIMARY KEY (`computer_id`),
  ADD KEY `PC_OS_FK` (`os`),
  ADD KEY `PC_COMPANY_FK` (`company`),
  ADD KEY `PC_CPU_FK` (`cpu`);
ALTER TABLE `computer` ADD FULLTEXT KEY `computer_id` (`computer_id`,`model`);
ALTER TABLE `computer` ADD FULLTEXT KEY `notes` (`notes`,`url`);

--
-- Indexes for table `cpu`
--
ALTER TABLE `cpu`
  ADD PRIMARY KEY (`cpu_id`),
  ADD UNIQUE KEY `cpu_name` (`cpu_name`,`first_manufactured`,`company`),
  ADD KEY `CPU_COMPANY_FK` (`company`);
ALTER TABLE `cpu` ADD FULLTEXT KEY `cpu_name_2` (`cpu_name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inv_id`),
  ADD KEY `INV_CAT_FK` (`cat`);

--
-- Indexes for table `magazine`
--
ALTER TABLE `magazine`
  ADD PRIMARY KEY (`mag_id`),
  ADD KEY `MAG_PUB_FK` (`publisher`);
ALTER TABLE `magazine` ADD FULLTEXT KEY `mag_id` (`mag_id`,`title`);
ALTER TABLE `magazine` ADD FULLTEXT KEY `notes` (`notes`,`url`);

--
-- Indexes for table `os`
--
ALTER TABLE `os`
  ADD PRIMARY KEY (`os_id`),
  ADD UNIQUE KEY `os_name` (`os_name`,`os_version`,`company`),
  ADD KEY `OS_COMPANY_FK` (`company`);
ALTER TABLE `os` ADD FULLTEXT KEY `os_name_2` (`os_name`,`os_version`);

--
-- Indexes for table `peripheral`
--
ALTER TABLE `peripheral`
  ADD PRIMARY KEY (`periph_id`),
  ADD KEY `PER_TYPE_FK` (`type`),
  ADD KEY `PER_COMPANY_FK` (`company`);
ALTER TABLE `peripheral` ADD FULLTEXT KEY `periph_id` (`periph_id`,`model`);
ALTER TABLE `peripheral` ADD FULLTEXT KEY `notes` (`notes`,`url`);

--
-- Indexes for table `peripheraltype`
--
ALTER TABLE `peripheraltype`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);
ALTER TABLE `peripheraltype` ADD FULLTEXT KEY `type_name_2` (`type_name`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`pub_id`),
  ADD UNIQUE KEY `pub_name` (`pub_name`);
ALTER TABLE `publisher` ADD FULLTEXT KEY `pub_name_2` (`pub_name`);

--
-- Indexes for table `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`software_id`),
  ADD KEY `SOFT_OS_FK` (`os`),
  ADD KEY `SOFT_COMPANY_FK` (`company`),
  ADD KEY `SOFT_TYPE_FK` (`type`);
ALTER TABLE `software` ADD FULLTEXT KEY `software_id` (`software_id`,`title`);
ALTER TABLE `software` ADD FULLTEXT KEY `storage_device` (`storage_device`,`notes`,`url`);

--
-- Indexes for table `softwaretype`
--
ALTER TABLE `softwaretype`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);
ALTER TABLE `softwaretype` ADD FULLTEXT KEY `type_name_2` (`type_name`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_string` (`tag_string`);
ALTER TABLE `tag` ADD FULLTEXT KEY `tag_string_2` (`tag_string`);

--
-- Indexes for table `tag_inventory`
--
ALTER TABLE `tag_inventory`
  ADD PRIMARY KEY (`inv`,`tag`),
  ADD KEY `TAG_FK` (`tag`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `aut_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cpu`
--
ALTER TABLE `cpu`
  MODIFY `cpu_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `os`
--
ALTER TABLE `os`
  MODIFY `os_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peripheraltype`
--
ALTER TABLE `peripheraltype`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publisher`
--
ALTER TABLE `publisher`
  MODIFY `pub_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `softwaretype`
--
ALTER TABLE `softwaretype`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `BOOK_AUT_FK` FOREIGN KEY (`author`) REFERENCES `author` (`aut_id`),
  ADD CONSTRAINT `BOOK_INV_FK` FOREIGN KEY (`book_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `BOOK_PUB_FK` FOREIGN KEY (`publisher`) REFERENCES `publisher` (`pub_id`);

--
-- Constraints for table `computer`
--
ALTER TABLE `computer`
  ADD CONSTRAINT `PC_COMPANY_FK` FOREIGN KEY (`company`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `PC_CPU_FK` FOREIGN KEY (`cpu`) REFERENCES `cpu` (`cpu_id`),
  ADD CONSTRAINT `PC_INV_FK` FOREIGN KEY (`computer_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `PC_OS_FK` FOREIGN KEY (`os`) REFERENCES `os` (`os_id`);

--
-- Constraints for table `cpu`
--
ALTER TABLE `cpu`
  ADD CONSTRAINT `CPU_COMPANY_FK` FOREIGN KEY (`company`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `INV_CAT_FK` FOREIGN KEY (`cat`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `magazine`
--
ALTER TABLE `magazine`
  ADD CONSTRAINT `MAG_INV_FK` FOREIGN KEY (`mag_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `MAG_PUB_FK` FOREIGN KEY (`publisher`) REFERENCES `publisher` (`pub_id`);

--
-- Constraints for table `os`
--
ALTER TABLE `os`
  ADD CONSTRAINT `OS_COMPANY_FK` FOREIGN KEY (`company`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `peripheral`
--
ALTER TABLE `peripheral`
  ADD CONSTRAINT `PER_COMPANY_FK` FOREIGN KEY (`company`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `PER_INV_FK` FOREIGN KEY (`periph_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `PER_TYPE_FK` FOREIGN KEY (`type`) REFERENCES `peripheraltype` (`type_id`);

--
-- Constraints for table `software`
--
ALTER TABLE `software`
  ADD CONSTRAINT `SOFT_COMPANY_FK` FOREIGN KEY (`company`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `SOFT_INV_FK` FOREIGN KEY (`software_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `SOFT_OS_FK` FOREIGN KEY (`os`) REFERENCES `os` (`os_id`),
  ADD CONSTRAINT `SOFT_TYPE_FK` FOREIGN KEY (`type`) REFERENCES `softwaretype` (`type_id`);

--
-- Constraints for table `tag_inventory`
--
ALTER TABLE `tag_inventory`
  ADD CONSTRAINT `INV_FK` FOREIGN KEY (`inv`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `TAG_FK` FOREIGN KEY (`tag`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
