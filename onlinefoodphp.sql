-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 01:44 PM
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
-- Database: `onlinefoodphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adm_id` int(11) NOT NULL,
  `username` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `code` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adm_id`, `username`, `password`, `email`, `code`, `date`) VALUES
(1, 'lokendra', 'f1a87a376de49673c0530f2b2c2d2dc0', '', '', '2025-08-02 12:07:21'),
(2, 'marmik', '2f729959f604dde534abee7c46de0b24', 'marmik21@email.com', '', '2025-07-31 09:33:21'),
(3, 'marmik21', 'b68f7b995e1eb61922cd10663d0a6723', 'marmik21@email.com', '', '2025-07-31 09:37:55'),
(6, 'raju', '67719c4c2dae2189c6a83110e9461c15', 'rahu@gmail.com', '', '2026-03-31 07:33:18');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `c_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','flat') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` datetime NOT NULL,
  `usage_limit` int(11) NOT NULL DEFAULT 0,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(255) DEFAULT '',
  `rs_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`c_id`, `code`, `type`, `value`, `min_order`, `max_discount`, `expiry_date`, `usage_limit`, `used_count`, `is_active`, `description`, `rs_id`, `created_at`) VALUES
(1, 'PIZZA20', 'percent', 30.00, 100.00, 20.00, '2026-06-10 01:20:00', 2, 0, 1, '', 9, '2026-03-16 09:27:16'),
(2, 'HOLI20', 'percent', 10.00, 100.00, 60.00, '2026-04-12 12:04:00', 3, 0, 0, '', 9, '2026-03-16 10:40:41');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_partners`
--

CREATE TABLE `delivery_partners` (
  `dp_id` int(11) NOT NULL,
  `name` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(222) NOT NULL,
  `vehicle` varchar(100) NOT NULL DEFAULT '',
  `vehicle_no` varchar(50) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `delivery_partners`
--

INSERT INTO `delivery_partners` (`dp_id`, `name`, `email`, `phone`, `password`, `vehicle`, `vehicle_no`, `address`, `status`, `is_available`, `date`) VALUES
(1, 'raju', 'raju@gmail.com', '3445566756', '67719c4c2dae2189c6a83110e9461c15', 'Motorcycle', 'GJ01AB1234', 'ahemdabad', 1, 0, '2026-03-30 11:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `d_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL,
  `title` varchar(222) NOT NULL,
  `slogan` varchar(222) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `img` varchar(222) NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`d_id`, `rs_id`, `title`, `slogan`, `price`, `img`, `discount_pct`) VALUES
(5, 7, 'Pink Spaghetti Gamberoni', 'Spaghetti in a fresh tomato sauce. This dish originates from Southern Italy and with the combination of garlic, chilli and pasta. Garnish each with remaining tablespoon parsley.', 329.00, '685ec93693186.png', 0.00),
(17, 5, 'Paneer Butter Masala', 'Paneer Butter Masala is a rich, creamy, and mildly spiced tomato-based curry made with paneer (Indian cottage cheese). The dish is known for its smooth, buttery texture and vibrant orange-red color.', 250.00, '685ec7e574021.png', 0.00),
(18, 5, 'Palak Paneer', 'Palak Paneer is a healthy, flavorful, and vibrant North Indian curry made with palak (fresh spinach) and paneer (Indian cottage cheese). This dish is known for its bright green color and creamy spinach gravy that’s both no', 275.00, '685ec86115981.png', 0.00),
(19, 7, 'Pepe Rosa Pizza', 'Pepe Rosa Pizza is a gourmet-style Italian pizza known for its creamy pink sauce, a delightful blend of red (tomato) and white (cream) sauces — hence the name \"Pepe Rosa\" (which means “pink pepper” or “pink” in Italian).', 459.00, '685ecaf1ec0a9.png', 0.00),
(20, 11, 'Veg Fried Rice', 'Veg Fried Rice is a classic Indo-Chinese dish made by stir-frying cooked rice with a mix of fresh vegetables, soy sauce, and aromatic spices. It’s one of the most popular and versatile dishes in Asian cuisine, loved for it', 299.00, '685eccd4bda7f.png', 0.00),
(22, 6, 'Lachha Paratha', 'Lachha Paratha is a popular North Indian multi-layered, flaky flatbread made from whole wheat or refined flour (maida). Known for its crispy, golden-brown layers and soft interior, it’s a favorite in Indian restaurants and', 329.00, '685ecdbd0144e.png', 0.00),
(23, 6, 'Chole Kulche', 'Chole Kulche is a delicious and filling North Indian dish that combines spicy chickpeas (chole) with soft, leavened bread (kulcha). It’s especially popular as a street food in Delhi, Punjab, and other parts of Northern Ind', 249.00, '685ece529fbeb.png', 0.00),
(24, 9, 'Chilli Cheese Dragon Rolls', 'Chilli Cheese Dragon Rolls are a spicy, Indo-Chinese fusion appetizer that combines the gooey richness of melted cheese with the kick of green chilies and flavorful Asian seasonings, all wrapped inside a crispy roll. Popul', 249.00, '685ed087e7378.png', 0.00),
(25, 5, 'Cheese Butter Masala', 'Cheese Butter Masala is a rich and creamy North Indian curry made with soft cubes of cheese (paneer or processed cheese) cooked in a velvety tomato, cashew, and butter-based gravy. The dish is mildly spiced, slightly sweet', 280.00, '68bace3305e5b.jpg', 0.00),
(26, 5, 'Sarson-Da-Saag ', 'Sarson da Saag is a traditional Punjabi dish made from fresh mustard greens (sarson) slow-cooked with spinach, bathua (goosefoot leaves), and spices. The greens are simmered until soft and then tempered with onions, ginger', 210.00, '68bacf2f1ebac.jpg', 0.00),
(27, 5, 'Lasooni Mirch Ajmeri', 'Lasooni Mirch Ajmeri is a flavorful Rajasthani-inspired dish made with roasted green chilies cooked in a rich garlic (lasooni) and yogurt-based gravy. It carries the bold spiciness of chilies, balanced by the tang of curd ', 320.00, '68bacff329630.jpg', 0.00),
(28, 5, 'Makki Ki Roti', 'Makki ki Roti is a traditional North Indian flatbread made from maize flour (cornmeal). It has a rustic flavor, slightly coarse texture, and is usually prepared on a hot griddle with ghee.  This golden, hearty roti is most', 60.00, '68bad0e0072aa.jpg', 0.00),
(29, 5, 'Butter/Tawa Roti', 'Butter Roti is a soft and wholesome Indian flatbread made from whole wheat flour, cooked on a hot griddle or tandoor, and finished with a generous layer of butter. The butter enhances its flavor, making it rich, aromatic, ', 40.00, '68bad15d633c3.jpg', 0.00),
(30, 5, 'Cheesy Garlic Naan', 'Cheesy Garlic Naan is a soft and fluffy Indian flatbread infused with garlic and stuffed with gooey, melted cheese. Cooked in a tandoor or on a griddle, it’s brushed with butter and garnished with fresh herbs, making it ir', 50.00, '68bad25578a6a.jpg', 0.00),
(31, 9, 'Italian Lasagna', 'Lasagna is a classic Italian baked dish made with layers of flat pasta sheets, rich tomato sauce, creamy béchamel or ricotta, and a savory filling such as minced meat, vegetables, or both. Each layer is topped with generou', 480.00, '68bad910f38dd.jpg', 0.00),
(32, 9, 'Classic Cold Coffee', 'Classic Cold Coffee is a refreshing and creamy beverage made by blending chilled milk, instant coffee, sugar, and ice cubes to create a smooth, frothy drink. Often topped with a scoop of ice cream or a drizzle of chocolate', 240.00, '68bad99b7529c.png', 0.00),
(33, 9, 'Hot Coffee', 'Hot Coffee is a comforting beverage made by brewing roasted coffee beans to extract their rich, bold flavors. Served steaming, it can be enjoyed black for a strong, robust taste or with milk and sugar for a smoother, cream', 200.00, '68bada234c2a2.jpg', 0.00),
(34, 9, 'Thin Crust Margarita Pizza', 'Thin Crust Margherita Pizza is a classic Italian delight featuring a crisp, golden-brown crust topped with tangy tomato sauce, fresh mozzarella cheese, and fragrant basil leaves. The thin base allows the simple yet bold fl', 540.00, '68badb213af70.jpg', 0.00),
(35, 9, 'Thin Crust Pepperoni Pizza', 'Thin Crust Pepperoni Pizza is a savory favorite with a crispy, golden crust layered with zesty tomato sauce, melted mozzarella cheese, and topped with slices of smoky, spiced pepperoni. The thin base gives it a crunchy tex', 490.00, '68badbcc07cf9.png', 0.00),
(36, 9, 'Creamy Pesto Pasta', 'Creamy Pesto Pasta is a rich and flavorful dish made with al dente pasta tossed in a velvety sauce of fresh basil pesto, cream, and Parmesan cheese. The creamy texture perfectly balances the herby, nutty, and garlicky flav', 300.00, '68badc4416f22.jpg', 0.00),
(37, 9, 'Chorizo Pasta', 'Creamy Chorizo Pasta is a hearty and flavorful dish that combines al dente pasta with smoky, spicy chorizo sausage cooked in a rich, creamy sauce. The chorizo infuses the sauce with bold, savory flavors, while garlic, herb', 350.00, '68badd1db2550.jpg', 0.00),
(38, 11, 'Cottage Cheese Chilli', 'Cottage Cheese Chilli, popularly known as Chilli Paneer, is a flavorful Indo-Chinese dish made with crispy cubes of cottage cheese tossed in a spicy, tangy sauce. Stir-fried with bell peppers, onions, garlic, and a blend o', 385.00, '68bade1c7cc53.png', 0.00),
(39, 11, 'Som Tam', 'Som Tam, also known as Thai Green Papaya Salad, is a refreshing and zesty dish from Thailand. Made with shredded unripe papaya, tomatoes, green beans, and peanuts, it’s tossed with a tangy dressing of lime juice, fish sauc', 295.00, '68badecb25ed2.png', 0.00),
(40, 11, 'Manchurian', 'Manchurian is a popular Indo-Chinese dish that blends Chinese cooking techniques with Indian flavors. It features deep-fried vegetable or chicken balls tossed in a tangy, spicy, and slightly sweet sauce made with soy sauce', 220.00, '68badf5031e60.png', 0.00),
(41, 11, 'Chow Chow Potatos', 'Chow Chow Potatoes is a flavorful Indo-Chinese style dish made with crispy fried potato cubes tossed in a spicy, tangy, and slightly sweet sauce. Cooked with garlic, ginger, spring onions, and bell peppers, the dish delive', 275.00, '68badfecb89cc.png', 0.00),
(42, 11, 'Thai Noodles', 'Thai Noodles is a vibrant and aromatic dish made with stir-fried noodles tossed in a flavorful blend of soy sauce, garlic, chili, and fresh herbs. Often paired with vegetables, tofu, chicken, or shrimp, it offers a balance', 260.00, '68bbb1b32f6a6.png', 0.00),
(43, 11, 'Chow Mein Noodles', 'Chow Mein Noodles is a popular Indo-Chinese dish made by stir-frying boiled noodles with a mix of colorful vegetables, soy sauce, and spices. It is known for its smoky flavor, crunchy texture from sautéed veggies, and the ', 295.00, '68bbb276c8602.png', 0.00),
(44, 11, 'Schezwan Fried Rice', 'Schezwan Fried Rice is a spicy and flavorful Indo-Chinese dish made by stir-frying rice with vegetables, soy sauce, and fiery Schezwan sauce. It has a bold, tangy, and garlicky taste with a hint of heat that makes it irres', 225.00, '68bbb94f1ba17.png', 0.00),
(45, 5, 'Mango Lassi', 'Mango Lassi is a refreshing and creamy Indian drink made with ripe mangoes, yogurt, milk, and a touch of sugar or honey. Smooth and naturally sweet, it has a rich fruity flavor balanced with the coolness of yogurt. Often g', 180.00, '68bbbb16da4e4.png', 0.00),
(46, 5, 'Buttermilk ', 'Buttermilk is a light, refreshing, and tangy yogurt-based drink popular in Indian households. Made by blending curd with water, salt, and spices like cumin, ginger, or curry leaves, it is not only cooling but also aids dig', 100.00, '68bbbb80f0ac9.png', 0.00),
(47, 9, 'Virjin Mojito', 'Virgin Mojito is a refreshing non-alcoholic beverage made with fresh mint leaves, lime juice, sugar, and soda or sparkling water. It’s a perfect balance of tangy, sweet, and minty flavors, served chilled over ice. Light an', 160.00, '68bbbc5c91471.png', 0.00),
(48, 7, 'Pesto Pizza', 'Pesto Pizza is a flavorful twist on the classic pizza, made with a vibrant base of fresh basil pesto instead of tomato sauce. Topped with mozzarella cheese, cherry tomatoes, and sometimes vegetables or chicken, it offers a', 770.00, '68bbbf649e727.png', 0.00),
(49, 7, 'Thin Crust Aglio Olio Pizza', 'Thin Crust Aglio Olio Pizza is a light and flavorful Italian-inspired pizza that combines the simplicity of aglio olio pasta with the crispiness of a thin crust. Topped with olive oil, garlic, chili flakes, herbs, and a sp', 790.00, '68bbc0563a502.png', 0.00),
(50, 7, 'Thin Crust Greca Pizza[12 inches]', 'Thin Crust Greca Pizza is a Mediterranean-inspired delight featuring a crisp, golden crust topped with fresh ingredients like olives, feta cheese, onions, tomatoes, and aromatic herbs. Light yet packed with flavor, it brin', 799.00, '68bbc11f0b5f7.png', 0.00),
(51, 7, 'ARRABIATA Red Sauce Pasta', 'Arrabiata Red Sauce Pasta is a bold and spicy Italian dish made with penne pasta tossed in a tangy tomato-based sauce. Flavored with garlic, olive oil, and a kick of red chili flakes, it delivers a perfect balance of heat ', 569.00, '68bbc1cba7408.png', 0.00),
(52, 7, 'Alfredo With Cheddar Crisps', 'Alfredo with Cheddar Crisps is a rich and creamy pasta dish made with a velvety Alfredo sauce, blending butter, cream, and parmesan for a smooth texture. It is topped with crispy cheddar crisps that add a crunchy, cheesy t', 559.00, '68bbc3078c9b7.png', 0.00),
(53, 8, 'Paneer Cigar Rolls', 'Paneer Cigar Rolls are a crispy and flavorful snack made by stuffing spiced paneer (Indian cottage cheese) mixture into thin pastry sheets, rolled up like cigars, and deep-fried until golden brown. Crunchy on the outside a', 539.00, '68bd0983a5ce6.png', 0.00),
(54, 8, 'Pesto Olive Tikka With Truffle Oil(220 gms)', 'Pesto Olive Tikka with Truffle Oil is a gourmet fusion dish that combines the richness of Indian tikka with Italian flavors. Paneer or vegetables are marinated in a fresh basil pesto sauce blended with olives, then grilled', 630.00, '68bd0a6edc174.png', 0.00),
(55, 8, 'Aglio E Olio Spaghetti (250 gms)', 'Aglio e Olio Spaghetti is a simple yet flavorful Italian classic made with spaghetti tossed in olive oil infused with garlic, chili flakes, and fresh parsley. Light and aromatic, this dish highlights the richness of extra ', 590.00, '68bd0b93d81d2.png', 0.00),
(56, 8, 'Hazelnut Cold Coffee', 'Hazelnut Cold Coffee is a refreshing chilled beverage made with rich coffee blended with milk, ice, and a hint of nutty hazelnut flavor. Smooth, creamy, and aromatic, it’s the perfect fusion of bold coffee and sweet nuttin', 319.00, '68bd0c3ce6c95.png', 0.00),
(57, 8, 'Loaded Nachos', 'Loaded Nachos are a delicious, shareable snack made with crispy tortilla chips generously topped with melted cheese, beans, fresh veggies, jalapeños, and flavorful sauces. Often served with guacamole, salsa, or sour cream,', 299.00, '68bd0ccde67b7.png', 0.00),
(58, 8, 'Avocado And Ricotta Pesto Toast', 'Avocado and Ricotta Pesto Toast is a wholesome and flavorful dish made with creamy avocado slices and smooth ricotta layered on toasted bread, topped with a drizzle of aromatic basil pesto. Nutritious yet indulgent, it com', 590.00, '68bd0d7e3d809.png', 0.00),
(59, 8, 'Cheese Garlic Bread', 'Cheese Garlic Bread is a warm and comforting appetizer made with bread slices topped with a buttery garlic spread and a generous layer of melted cheese. Crispy on the outside and soft inside, it combines the rich flavors o', 269.00, '68bd0e49baefe.png', 0.00),
(60, 8, 'Garlic, Spinach, Corn And Broccoli Neapolitan Pizza', 'Garlic, Spinach, Corn, and Broccoli Neapolitan Pizza is a wholesome twist on the classic Italian favorite. Made with a soft, wood-fired Neapolitan crust, it’s topped with fresh spinach, sweet corn, crunchy broccoli, and a ', 1090.00, '68bd0f47a16a4.png', 0.00),
(61, 8, 'Ala Mexicana Pizza(10inch || 25cms)', 'Ala Mexicana Pizza is a zesty fusion dish that brings bold Mexican flavors to a classic pizza base. Topped with spicy jalapeños, bell peppers, onions, sweet corn, and seasoned herbs, it delivers a perfect balance of heat, ', 700.00, '68bd103b668cd.png', 0.00),
(62, 6, 'Dal Makhani ', 'Dal Makhani is a rich and creamy North Indian delicacy made with whole black lentils (urad dal) and red kidney beans (rajma), slow-cooked with butter, cream, tomatoes, and aromatic spices. Its velvety texture and smoky fla', 280.00, '68bd1143bdad6.png', 0.00),
(63, 6, 'Cheese Chilli Garlic Paratha', 'Cheese Chilli Garlic Paratha is a flavorful stuffed Indian flatbread made with a spicy filling of melted cheese, green chilies, and garlic. Pan-cooked with a touch of butter or ghee, it’s soft on the inside and slightly cr', 220.00, '68bd12229d767.png', 0.00),
(64, 6, 'Amritsari Patiala Lassi', 'Patiala Lassi is a traditional Punjabi beverage known for its richness and creamy texture, served in large tall glasses. Made with thick yogurt, sugar, and often flavored with cardamom or rose water, it is topped with a ge', 149.00, '68bd12c901d10.png', 0.00),
(65, 6, 'Stuffed Paratha With Curd', 'Stuffed Paratha is a popular Indian flatbread filled with a savory mixture such as spiced potatoes, paneer, cauliflower, or lentils. Cooked on a griddle with ghee or butter, it’s crispy on the outside and soft inside, offe', 270.00, '68bd1469042fa.png', 0.00),
(66, 6, 'Aloo Onion Paratha', 'Aloo Onion Paratha is a delicious North Indian flatbread stuffed with a flavorful mix of mashed potatoes, finely chopped onions, and spices. Cooked on a hot griddle with butter or ghee, it turns golden and crispy while sta', 249.00, '68bd143861c8a.png', 0.00),
(67, 6, 'Hyderabadi Veg Biryani ', 'Hyderabadi Veg Biryani is a fragrant and royal rice dish made with basmati rice, fresh seasonal vegetables, and a blend of aromatic spices. Cooked using the traditional dum style, the layers of spiced vegetables and rice a', 200.00, '68bd154a5c282.png', 0.00),
(68, 6, 'Veg Briyani', 'Veg Biryani is a flavorful and aromatic rice dish made with fragrant basmati rice, mixed vegetables, and a blend of traditional Indian spices. Cooked in layers, it combines the richness of spices like cardamom, cloves, and', 180.00, '68bd15a95b004.png', 0.00),
(69, 13, 'pizza', 'pizza', 200.00, '69bd630d48f2a.jpg', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `remark`
--

CREATE TABLE `remark` (
  `id` int(11) NOT NULL,
  `frm_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `remark` mediumtext NOT NULL,
  `remarkDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `remark`
--

INSERT INTO `remark` (`id`, `frm_id`, `status`, `remark`, `remarkDate`) VALUES
(1, 2, 'in process', 'none', '2022-05-01 05:17:49'),
(2, 3, 'in process', 'none', '2022-05-27 11:01:30'),
(3, 2, 'closed', 'thank you for your order!', '2022-05-27 11:11:41'),
(4, 3, 'closed', 'none', '2022-05-27 11:42:35'),
(5, 4, 'in process', 'none', '2022-05-27 11:42:55'),
(6, 1, 'rejected', 'none', '2022-05-27 11:43:26'),
(7, 7, 'in process', 'none', '2022-05-27 13:03:24'),
(8, 8, 'in process', 'none', '2022-05-27 13:03:38'),
(9, 9, 'rejected', 'thank you', '2022-05-27 13:03:53'),
(10, 7, 'closed', 'thank you for your ordering with us', '2022-05-27 13:04:33'),
(11, 8, 'closed', 'thanks ', '2022-05-27 13:05:24'),
(12, 5, 'closed', 'none', '2022-05-27 13:18:03'),
(13, 15, 'in process', 'In process', '2025-09-05 06:54:41'),
(14, 16, 'closed', 'Have a great day', '2025-09-05 06:55:29'),
(15, 15, 'closed', 'Have a great day', '2025-09-05 06:56:12'),
(16, 17, 'in process', 'dispatching', '2025-09-07 05:49:35'),
(17, 17, 'closed', 'Thanks for ordering', '2025-09-07 06:12:40'),
(18, 19, 'rejected', 'cancled', '2025-09-07 06:13:27'),
(19, 20, 'in process', 'On processing', '2025-09-08 07:23:14'),
(20, 23, 'in process', 'on processing', '2025-09-08 10:28:34'),
(21, 24, 'closed', 'delivered', '2025-09-08 10:29:18'),
(22, 28, 'closed', 'fwfwdf', '2025-09-15 13:19:28'),
(23, 30, 'in process', 'dsvsd', '2025-09-15 13:26:00'),
(24, 31, 'in process', 'scsac', '2025-09-15 13:26:36'),
(25, 29, 'rejected', 'cancelled', '2025-09-15 13:27:03'),
(26, 32, 'closed', 'aca', '2025-09-15 13:27:21'),
(27, 35, 'in process', 'waiting ', '2025-09-17 04:33:26'),
(28, 35, 'closed', 'delivered', '2025-09-17 04:33:43'),
(29, 36, 'closed', 'Delivered', '2025-09-17 04:48:14'),
(30, 37, 'closed', 'Delivered', '2025-09-17 04:48:39'),
(31, 30, 'closed', 'Delivered', '2025-09-17 04:48:54'),
(32, 31, 'closed', 'Delivered\r\n', '2025-09-17 04:49:15'),
(33, 39, 'closed', 'Delivered\r\n', '2025-09-17 04:49:30'),
(34, 38, 'closed', 'Delivered', '2025-09-17 04:49:49'),
(35, 41, 'closed', 'Delivered\r\n', '2025-09-17 04:55:57'),
(36, 40, 'rejected', 'nai karvu mare', '2025-09-17 04:56:18'),
(37, 42, 'closed', 'Delivered', '2025-09-18 10:49:39'),
(38, 43, 'closed', 'deliverd', '2025-09-18 10:50:49'),
(39, 48, 'on the way', 'Order is on the way', '2026-03-15 10:10:35'),
(40, 48, 'in process', 'Order is being prepared', '2026-03-15 10:43:00'),
(41, 45, 'in process', 'Order is being prepared', '2026-03-16 05:16:36'),
(42, 48, 'on the way', 'Order is on the way', '2026-03-16 05:19:44'),
(43, 49, 'in process', 'Order accepted by restaurant', '2026-03-16 05:29:00'),
(44, 49, 'on the way', 'Order is on the way', '2026-03-16 05:29:07'),
(45, 49, 'in process', 'Order is being prepared', '2026-03-16 05:29:19'),
(46, 49, 'on the way', 'Order is on the way', '2026-03-16 09:06:14'),
(47, 55, 'confirmed', 'Order confirmed by restaurant', '2026-03-30 11:42:18'),
(48, 55, 'in process', 'Delivery partner assigned: raju', '2026-03-30 11:43:10'),
(49, 56, 'in process', 'Order accepted by restaurant', '2026-03-30 11:59:17'),
(50, 56, 'in process', 'Order accepted by restaurant', '2026-03-30 11:59:53'),
(51, 58, 'in process', 'Order is being prepared', '2026-03-30 12:00:26'),
(52, 59, 'confirmed', 'Order confirmed by restaurant', '2026-03-30 12:04:08'),
(53, 59, 'in process', 'Delivery partner assigned: raju', '2026-03-31 07:46:39'),
(54, 59, 'on the way', 'Order picked up, on the way', '2026-03-31 07:47:03'),
(55, 59, 'closed', 'Order delivered by raju', '2026-03-31 07:47:13'),
(56, 60, 'confirmed', 'Order confirmed by restaurant', '2026-03-31 15:03:51'),
(57, 60, 'on the way', 'Order is on the way', '2026-03-31 15:04:06'),
(58, 60, 'in process', 'Order is being prepared', '2026-03-31 15:06:18'),
(59, 61, 'confirmed', 'Order confirmed by restaurant', '2026-04-02 07:48:01'),
(60, 61, 'on the way', 'Order is on the way', '2026-04-02 07:50:24'),
(61, 62, 'confirmed', 'Order confirmed by restaurant', '2026-04-02 12:36:58'),
(62, 62, 'in process', 'Order is being prepared', '2026-04-02 12:37:13'),
(63, 62, 'closed', 'Order delivered successfully', '2026-04-07 06:06:34'),
(64, 63, 'confirmed', 'Order confirmed by restaurant', '2026-04-20 06:24:10'),
(65, 63, 'on the way', 'Order is on the way', '2026-04-20 06:24:20'),
(66, 63, 'closed', 'Order delivered successfully', '2026-04-20 06:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `rs_id` int(11) NOT NULL,
  `c_id` int(11) NOT NULL,
  `title` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL DEFAULT '',
  `phone` varchar(222) NOT NULL,
  `url` varchar(222) NOT NULL,
  `o_hr` varchar(222) NOT NULL,
  `c_hr` varchar(222) NOT NULL,
  `o_days` varchar(222) NOT NULL,
  `address` text NOT NULL,
  `image` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `res_email` varchar(150) DEFAULT NULL,
  `res_password` varchar(64) DEFAULT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`rs_id`, `c_id`, `title`, `email`, `password`, `phone`, `url`, `o_hr`, `c_hr`, `o_days`, `address`, `image`, `date`, `res_email`, `res_password`, `discount_pct`) VALUES
(5, 0, 'Legends Of Punjab by Pepperazzi', 'legendsofpunjab@gmail.com', 'a1986b81ba5ad05a32f9a147e72d0f64', '  074050 12175', 'https://www.legendsofpunjab.com', '--Select your Hours--', '--Select your Hours--', '--Select your Days--', ' 1st Floor Tirupati House,\r\nNear Panjrapole Cross Roads,\r\nGulbai Tekra,\r\nAhmedabad ', '685ebd346e7df.png', '2026-03-15 10:46:01', 'restaurant5@example.com', 'c9232df970888a8252348500cbf43341', 0.00),
(6, 5, 'Jassi De Parathe', 'info@jassideparathe.com', 'c33b559a72d82426c3456f19122d10a7', '+91 95102 22000', 'https://jassideparathe.com', '12pm', '11pm', 'Mon-Sun', '28, Sardar Centre,\r\nOpp. Vastrapur Lake,\r\nVastrapur, Ahmedabad-380015\r\nGujarat, India.', '685ebed4469c9.jpg', '2026-03-15 09:09:54', 'restaurant6@example.com', 'c33b559a72d82426c3456f19122d10a7', 0.00),
(7, 2, 'Sale & Pepe - Ristorante Italiano', 'sale&peperistoranteitaliano@email.com', 'c33b559a72d82426c3456f19122d10a7', '9227814817', 'sale&peperistoranteitaliano.com', '11am', '12am', 'Mon-Sun', 'un Avenue One, 1st Floor, 105, Shyamal, to, Manik Baug Rd, Ambawadi, Ahmedabad, Gujarat 380015', '685ec01b59a14.jpg', '2026-03-15 09:09:54', 'restaurant7@example.com', 'c33b559a72d82426c3456f19122d10a7', 0.00),
(8, 2, 'The House of Makeba', 'info@thehouseofmakeba.com', 'c33b559a72d82426c3456f19122d10a7', '74900 44477', 'https://www.thehouseofmakeba.com/', '12pm', '12am', 'Mon-Sun', '8th Floor, 3rd Eye Vision, IIM Rd, above Maruti Nexa Showroom, Panjara Pol, University Area, Ahmedabad, Gujarat 380009', '685ec1253acc5.png', '2026-03-15 09:09:54', 'restaurant8@example.com', 'c33b559a72d82426c3456f19122d10a7', 0.00),
(9, 2, 'Mocha', 'franchise@mocha.co.in', 'c33b559a72d82426c3456f19122d10a7', '97252 00002', 'https://mocha.co.in/index.html', '12pm', '12am', 'Mon-Sun', 'Ground Floor, Devashish Business Park, 6-9, Premchand Nagar Rd, opposite Krishna Complex, Bodakdev, Ahmedabad, Gujarat 380054', '685ec21bdb4ef.jpg', '2026-03-16 10:40:50', 'restaurant9@example.com', 'c33b559a72d82426c3456f19122d10a7', 7.00),
(11, 3, 'Wok On Fire', 'https://www.wokonfire.in/store', 'c33b559a72d82426c3456f19122d10a7', '7203066666', 'https://www.wokonfire.in/store', '11am', '8pm', '24hr-x7', ' Zodiac Plaza Besides Nabard Officers Colony Opposite Kotak Mahindra Bank, Commerce College Rd, Ahmedabad, Gujarat 380009', '68c809817b04a.png', '2026-03-15 09:09:54', 'restaurant11@example.com', 'c33b559a72d82426c3456f19122d10a7', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `res_category`
--

CREATE TABLE `res_category` (
  `c_id` int(11) NOT NULL,
  `c_name` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `res_category`
--

INSERT INTO `res_category` (`c_id`, `c_name`, `date`) VALUES
(1, 'Continental', '2022-05-27 08:07:35'),
(2, 'Italian', '2021-04-07 08:45:23'),
(3, 'Chinese', '2021-04-07 08:45:25'),
(4, 'American', '2021-04-07 08:45:28'),
(5, 'Punjabi', '2025-06-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `username` varchar(222) NOT NULL,
  `f_name` varchar(222) NOT NULL,
  `l_name` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `address` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `f_name`, `l_name`, `email`, `phone`, `password`, `address`, `status`, `date`) VALUES
(9, 'shubham', 'Shubham', 'Bhavsar', 'shubhambhavsar2004@gmail.com', '09374012345', 'e10adc3949ba59abbe56e057f20f883e', 'F-10, Vikram Apartment\r\nNear Shreyas Crossing\r\nAmbawadi, Ahmedabad', 1, '2025-07-30 16:18:47'),
(10, 'Marmik Brahmkshatriya', 'Marmik', 'rahmkshatriya', 'marmik@gmail.com', '8780091312', 'e10adc3949ba59abbe56e057f20f883e', 'Q-4 Vikram Appartment', 1, '2025-07-31 06:30:48'),
(11, '123', '123', '123', 'zapicreureke-8160@yopmail.com', '6351725755', 'e10adc3949ba59abbe56e057f20f883e', '123', 1, '2025-07-31 10:58:48'),
(12, 'Marmik_123', 'Marmik', 'Brahmkshatriya', 'qwer1234@gmail.com', '7894561023', 'fcea920f7412b5da7be0cf42b8c93759', 'cewfwefwef', 1, '2025-09-04 04:41:01'),
(13, 'vedant', 'vedant', 'bhavsar', 'vedant@gmail.com', '1236547890', 'ae09aef752212506fff0e94ead5746c5', 'dwwevwfwvw', 1, '2025-09-05 04:36:00'),
(14, 'Dineshbhai', 'dinesh', 'brahmkshatriya', 'dinesh@gmail.com', '9568741230', '72ea9b10ad081b41a57c4982649ee7fd', 'evfevefrvb', 1, '2025-09-05 04:44:04'),
(15, 'HarshJoshi', 'Harsh', 'Joshi', 'harshjoshi@gmail.com', '7894561230', '789f215f55165cb68d8cd7d1ac2868a7', 'fdfvefveev', 1, '2025-09-07 05:27:41'),
(16, 'saloni', 'saloni', 'rana', 'saloni@gmail.com', '12345678909', '8e53189bb7119dfcf3abcf4520c2ee19', 'efervevefav', 1, '2025-09-08 10:26:51'),
(17, 'Aryan', 'Aryan', 'Shah', 'aryanshah@gmail.com', '9898456310', '165669f10483da5f34d1b4ccc25bf308', 'Vkiram Appartment,\r\nShreyash Crossing', 1, '2025-09-16 03:46:29'),
(18, 'TanmayOza', 'Tanmay', 'Oza', 'tanmayoza@gmail.com', '8787149005', '66c19b4cdb81d3ff4296c33ed350cd98', 'Q-4, Vkiram Appartment,\r\nShreyash Crossing,\r\nAmbawadi,\r\nAhmedabad-380015', 1, '2025-09-17 04:52:43'),
(19, 'lokendra1', 'lokendra', 'dodiya', 'lokendra@gmail.com', '6758495869', 'f1a87a376de49673c0530f2b2c2d2dc0', 'ahemdabad', 1, '2026-03-15 09:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `users_orders`
--

CREATE TABLE `users_orders` (
  `o_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(222) NOT NULL,
  `dish_img` varchar(222) NOT NULL DEFAULT '',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(222) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `d_id` int(11) DEFAULT NULL,
  `delivery_name` varchar(222) NOT NULL DEFAULT '',
  `dp_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users_orders`
--

INSERT INTO `users_orders` (`o_id`, `u_id`, `rs_id`, `title`, `dish_img`, `quantity`, `price`, `status`, `date`, `d_id`, `delivery_name`, `dp_id`, `rating`) VALUES
(15, 13, 0, 'Palak Paneer', '', 1, 275.00, 'closed', '2025-09-05 06:56:12', NULL, '', NULL, NULL),
(16, 13, 0, 'Paneer Butter Masala', '', 1, 250.00, 'closed', '2025-09-05 06:55:29', NULL, '', NULL, NULL),
(24, 16, 0, 'Cheesy Garlic Naan', '', 1, 50.00, 'closed', '2025-09-08 10:29:18', NULL, '', NULL, NULL),
(28, 15, 0, 'Chilli Cheese Dragon Rolls', '', 3, 747.00, 'closed', '2025-09-15 13:19:28', NULL, '', NULL, NULL),
(29, 15, 0, 'Italian Lasagna', '', 2, 960.00, 'rejected', '2025-09-15 13:27:03', NULL, '', NULL, NULL),
(30, 15, 0, 'Chole Kulche', '', 5, 1245.00, 'closed', '2025-09-17 04:48:54', NULL, '', NULL, NULL),
(31, 15, 0, 'Lachha Paratha', '', 1, 329.00, 'closed', '2025-09-17 04:49:15', NULL, '', NULL, NULL),
(32, 15, 0, 'Pink Spaghetti Gamberoni', '', 1, 329.00, 'closed', '2025-09-15 13:27:21', NULL, '', NULL, NULL),
(35, 17, 0, 'Paneer Butter Masala', '', 1, 250.00, 'closed', '2025-09-17 04:33:43', NULL, '', NULL, NULL),
(36, 17, 0, 'Lachha Paratha', '', 1, 329.00, 'closed', '2025-09-17 04:48:14', NULL, '', NULL, NULL),
(37, 17, 0, 'Pepe Rosa Pizza', '', 1, 459.00, 'closed', '2025-09-17 04:48:39', NULL, '', NULL, NULL),
(38, 17, 0, 'Italian Lasagna', '', 1, 480.00, 'closed', '2025-09-17 04:49:49', NULL, '', NULL, NULL),
(39, 17, 0, 'Paneer Butter Masala', '', 1, 250.00, 'closed', '2025-09-17 04:49:30', NULL, '', NULL, NULL),
(40, 18, 0, 'Paneer Butter Masala', '', 1, 250.00, 'rejected', '2025-09-17 04:56:18', NULL, '', NULL, NULL),
(41, 18, 0, 'Chow Mein Noodles', '', 1, 295.00, 'closed', '2025-09-17 04:55:57', NULL, '', NULL, NULL),
(42, 15, 0, 'Pink Spaghetti Gamberoni', '', 4, 1316.00, 'closed', '2025-09-18 10:49:39', NULL, '', NULL, NULL),
(43, 15, 0, 'Dal Makhani ', '', 2, 560.00, 'closed', '2025-09-18 10:50:49', NULL, '', NULL, NULL),
(44, 15, 0, 'Italian Lasagna', '', 1, 480.00, 'in process', '2025-09-20 04:15:06', NULL, '', NULL, NULL),
(45, 15, 0, 'Paneer Butter Masala', '', 1, 250.00, 'in process', '2025-09-20 04:15:06', NULL, '', NULL, NULL),
(46, 15, 0, 'Cottage Cheese Chilli', '', 1, 385.00, 'in process', '2025-09-20 04:15:06', NULL, '', NULL, NULL),
(47, 15, 0, 'Veg Fried Rice', '', 1, 299.00, 'in process', '2025-09-20 04:15:06', NULL, '', NULL, NULL),
(48, 19, 0, 'Chilli Cheese Dragon Rolls', '', 1, 249.00, 'on the way', '2026-03-16 05:19:44', NULL, '', NULL, NULL),
(49, 19, 9, 'Chilli Cheese Dragon Rolls', '', 3, 747.00, 'on the way', '2026-03-16 09:06:14', NULL, '', NULL, NULL),
(50, 19, 9, 'Chilli Cheese Dragon Rolls', '', 1, 249.00, 'in process', '2026-03-16 11:46:28', NULL, '', NULL, NULL),
(51, 19, 11, 'Veg Fried Rice', '685eccd4bda7f.png', 1, 299.00, 'in process', '2026-03-16 12:07:57', NULL, 'dodiya', NULL, NULL),
(52, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'in process', '2026-03-16 12:21:54', NULL, 'raju', NULL, NULL),
(53, 19, 6, 'Lachha Paratha', '685ecdbd0144e.png', 1, 329.00, 'in process', '2026-03-16 12:26:28', NULL, 'amnu', NULL, NULL),
(54, 19, 13, 'pizza', '69bd630d48f2a.jpg', 3, 600.00, 'in process', '2026-03-20 15:10:22', NULL, 'lokendra dodiya', NULL, NULL),
(55, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'confirmed', '2026-03-30 11:43:10', NULL, 'raju', 1, NULL),
(56, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'in process', '2026-03-30 11:52:35', NULL, 'lokendra dodiya', NULL, NULL),
(57, 19, 7, 'Pink Spaghetti Gamberoni', '685ec93693186.png', 1, 329.00, 'pending', '2026-03-30 11:56:53', NULL, 'lokendra dodiya', NULL, NULL),
(58, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'in process', '2026-03-30 12:00:26', NULL, 'lokendra dodiya', NULL, NULL),
(59, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'closed', '2026-03-31 07:47:13', NULL, 'lokendra dodiya', 1, NULL),
(60, 19, 9, 'Italian Lasagna', '68bad910f38dd.jpg', 1, 446.40, 'in process', '2026-03-31 15:06:18', NULL, 'lokendra dodiya', NULL, NULL),
(61, 19, 9, 'Classic Cold Coffee', '68bad99b7529c.png', 1, 223.20, 'on the way', '2026-04-02 07:50:24', NULL, 'lokendra dodiya', NULL, NULL),
(62, 19, 9, 'Chilli Cheese Dragon Rolls', '685ed087e7378.png', 1, 231.57, 'closed', '2026-04-07 06:06:45', NULL, 'lokendra dodiya', NULL, 4),
(63, 19, 9, 'Italian Lasagna', '68bad910f38dd.jpg', 1, 446.40, 'closed', '2026-04-20 06:24:55', NULL, 'lokendra dodiya', NULL, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adm_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`c_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `delivery_partners`
--
ALTER TABLE `delivery_partners`
  ADD PRIMARY KEY (`dp_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `remark`
--
ALTER TABLE `remark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`rs_id`);

--
-- Indexes for table `res_category`
--
ALTER TABLE `res_category`
  ADD PRIMARY KEY (`c_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `users_orders`
--
ALTER TABLE `users_orders`
  ADD PRIMARY KEY (`o_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_partners`
--
ALTER TABLE `delivery_partners`
  MODIFY `dp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `d_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `remark`
--
ALTER TABLE `remark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `rs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `res_category`
--
ALTER TABLE `res_category`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users_orders`
--
ALTER TABLE `users_orders`
  MODIFY `o_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
