-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 18, 2026 lúc 07:06 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `cafe_trungnguyen`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `Id` int(11) NOT NULL,
  `CustomerId` int(11) NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`Id`, `CustomerId`, `CreatedAt`) VALUES
(1, 2, '2026-04-15 09:26:28'),
(2, 1, '2026-04-15 20:03:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_item`
--

CREATE TABLE `cart_item` (
  `Id` int(11) NOT NULL,
  `CartId` int(11) NOT NULL,
  `StoreProductId` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_item`
--

INSERT INTO `cart_item` (`Id`, `CartId`, `StoreProductId`, `Quantity`, `Price`) VALUES
(3, 1, 87, 1, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Content` varchar(500) DEFAULT NULL,
  `CreateAt` datetime DEFAULT current_timestamp(),
  `UpdateAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`Id`, `Title`, `Content`, `CreateAt`, `UpdateAt`) VALUES
(1, 'Cà phê', 'Thưởng thức hương vị đậm đà...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(2, 'Trà', 'Thanh lọc cơ thể và xoa dịu tâm hồn...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(3, 'Nước ép', 'Ngọt lành và tươi mát từ trái cây...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(4, 'Sinh tố', 'Những ly sinh tố mịn màng...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(5, 'Đồ ăn nhẹ', 'Những món ăn nhẹ đầy hấp dẫn...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(6, 'Các món khác', 'Từ nước lọc, sữa tươi...', '2025-11-17 14:52:54', '2025-11-17 14:52:54'),
(10, 'sữa đậu nahf', '312312', '2026-04-17 11:50:03', '2026-04-17 11:50:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer`
--

CREATE TABLE `customer` (
  `Id` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Address` varchar(300) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Email` varchar(50) NOT NULL,
  `Img` varchar(300) DEFAULT NULL,
  `RegisteredAt` datetime DEFAULT current_timestamp(),
  `UpdateAt` datetime DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Password` varchar(200) NOT NULL,
  `RandomKey` varchar(100) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `Role` int(11) DEFAULT 0,
  `ProvinceId` int(11) DEFAULT 0,
  `DistrictId` int(11) DEFAULT 0,
  `WardCode` varchar(10) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customer`
--

INSERT INTO `customer` (`Id`, `FirstName`, `LastName`, `Address`, `Phone`, `Email`, `Img`, `RegisteredAt`, `UpdateAt`, `DateOfBirth`, `Password`, `RandomKey`, `IsActive`, `Role`, `ProvinceId`, `DistrictId`, `WardCode`) VALUES
(1, 'Nhật Thành', 'Nguyễn', '05 Nguyễn Đình Chiểu Phường Bắc Nha Trang Tỉnh Khánh Hòa', '0866492106', 'thanh.nn.64cntt@ntu.edu.vn', 'avatar-default.png', '2026-04-15 09:06:12', '2026-04-17 10:05:21', '2004-05-13', '$2y$10$65Kznmf7S7j7QH2/YrmaAuFHcXug48sS5xWHSqFKOp4xcy9.obWvi', '', 1, 0, 0, 0, ''),
(2, 'Văn A', 'Nguyễn', '123 Đường Láng, Đống Đa, Hà Nội', '0382395208', 'kinxedo78@gmail.com', 'avatar-default.png', '2026-04-15 09:21:26', NULL, '2003-11-11', '$2y$10$nkRRJXk5WoP5UheracXbEuj7S2DcHolqPFMdrBkfQeCJ/MEotjK0y', '', 1, 1, 0, 0, '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employee`
--

CREATE TABLE `employee` (
  `Id` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `RoleId` int(11) NOT NULL,
  `Salary` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `employee`
--

INSERT INTO `employee` (`Id`, `FullName`, `StoreId`, `RoleId`, `Salary`) VALUES
(1, 'Nguyễn Văn Nam', 1, 1, 7000000.00),
(2, 'Trần Thị Lan', 1, 2, 6500000.00),
(3, 'Lê Minh Tuấn', 1, 3, 6000000.00),
(4, 'Phạm Thị Hồng', 1, 4, 12000000.00),
(5, 'Hoàng Văn Sơn', 1, 5, 5500000.00),
(6, 'Vũ Thị Mai', 2, 1, 7000000.00),
(7, 'Đinh Văn Hoàng', 2, 2, 6500000.00),
(8, 'Ngô Thị Thanh', 2, 3, 6000000.00),
(9, 'Bùi Văn Quang', 2, 4, 12000000.00),
(10, 'Phan Thị Trang', 2, 5, 5500000.00),
(11, 'Nguyễn Thị Nhung', 3, 1, 7000000.00),
(12, 'Trần Văn Dũng', 3, 2, 6500000.00),
(13, 'Lê Thị Bích', 3, 3, 6000000.00),
(14, 'Phạm Văn Tài', 3, 4, 12000000.00),
(15, 'Hoàng Thị Hạnh', 3, 5, 5500000.00),
(16, 'Vũ Văn Phúc', 4, 1, 7000000.00),
(17, 'Đinh Thị Oanh', 4, 2, 6500000.00),
(18, 'Ngô Văn Kiên', 4, 3, 6000000.00),
(19, 'Bùi Thị Lan', 4, 4, 12000000.00),
(20, 'Phan Văn Cường', 4, 5, 5500000.00),
(21, 'Nguyễn Văn Hùng', 5, 1, 7000000.00),
(22, 'Trần Thị Dung', 5, 2, 6500000.00),
(23, 'Lê Văn Thành', 5, 3, 6000000.00),
(24, 'Phạm Thị Thu', 5, 4, 12000000.00),
(25, 'Hoàng Văn Long', 5, 5, 5500000.00),
(26, 'Nguyễn Nhật Thành', 1, 1, 5000000.00),
(28, '<script> alert(123) </script>', 1, 1, 213321321.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employeerole`
--

CREATE TABLE `employeerole` (
  `Id` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `employeerole`
--

INSERT INTO `employeerole` (`Id`, `RoleName`) VALUES
(1, 'Pha chế'),
(2, 'Thu ngân'),
(3, 'Phục vụ'),
(4, 'Quản lý'),
(5, 'Dọn dẹp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `page`
--

CREATE TABLE `page` (
  `Id` int(11) NOT NULL,
  `ParentId` int(11) DEFAULT NULL,
  `Title` varchar(100) NOT NULL,
  `PageIndex` int(11) DEFAULT NULL,
  `IsVisible` tinyint(1) DEFAULT 1,
  `Url` varchar(300) DEFAULT NULL,
  `StoreId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `Id` int(11) NOT NULL,
  `CustomerId` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `Total` decimal(18,2) NOT NULL,
  `PaymentMethod` varchar(20) DEFAULT 'cod',
  `Carrier` varchar(100) DEFAULT NULL,
  `TrackingCode` varchar(100) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`Id`, `CustomerId`, `StoreId`, `Total`, `PaymentMethod`, `Carrier`, `TrackingCode`, `Status`, `CreatedAt`) VALUES
(1, 2, 3, 25000.00, 'cod', NULL, NULL, 'đang giao', '2026-04-15 09:26:41'),
(2, 2, 3, 20000.00, 'bank', NULL, NULL, 'đã giao', '2026-04-15 09:27:28'),
(4, 1, 2, 25000.00, 'bank', NULL, NULL, 'đã giao', '2026-04-15 20:03:08'),
(5, 1, 2, 25000.00, 'cod', NULL, NULL, 'hủy', '2026-04-15 20:05:05'),
(7, 1, 3, 40000.00, 'bank', NULL, NULL, 'đang xử lý', '2026-04-16 09:31:40'),
(8, 1, 2, 40000.00, 'bank', NULL, NULL, 'đang xử lý', '2026-04-16 11:20:44'),
(9, 1, 2, 40501.00, 'cod', NULL, NULL, 'đang giao', '2026-04-17 10:09:08'),
(10, 1, 3, 35501.00, 'bank', NULL, NULL, 'đã giao', '2026-04-17 10:14:53'),
(11, 2, 5, 40501.00, 'cod', NULL, NULL, 'đang xử lý', '2026-04-18 11:32:52'),
(12, 1, 4, 40501.00, 'cod', NULL, NULL, 'đang giao', '2026-04-18 11:39:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `paymentdetail`
--

CREATE TABLE `paymentdetail` (
  `Id` int(11) NOT NULL,
  `PaymentId` int(11) NOT NULL,
  `StoreProductId` int(11) NOT NULL,
  `Price` decimal(18,2) NOT NULL,
  `Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `paymentdetail`
--

INSERT INTO `paymentdetail` (`Id`, `PaymentId`, `StoreProductId`, `Price`, `Quantity`) VALUES
(1, 1, 89, 25000.00, 1),
(2, 2, 86, 20000.00, 1),
(4, 4, 66, 25000.00, 1),
(5, 5, 66, 25000.00, 1),
(7, 7, 88, 25000.00, 1),
(8, 8, 65, 25000.00, 1),
(9, 9, 66, 25000.00, 1),
(10, 10, 86, 20000.00, 1),
(11, 11, 133, 25000.00, 1),
(12, 12, 111, 25000.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Content` varchar(500) DEFAULT NULL,
  `Img` varchar(300) DEFAULT NULL,
  `Price` decimal(18,2) NOT NULL,
  `Weight` int(11) DEFAULT 500,
  `Rate` decimal(3,2) DEFAULT NULL,
  `CreateAt` datetime DEFAULT current_timestamp(),
  `UpdateAt` datetime DEFAULT NULL,
  `CategoryId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product`
--

INSERT INTO `product` (`Id`, `Title`, `Content`, `Img`, `Price`, `Weight`, `Rate`, `CreateAt`, `UpdateAt`, `CategoryId`) VALUES
(1, 'Cà phê sữa đá', 'Một ly cà phê sữa đá đậm đà và thơm ngon, pha chế từ cà phê nguyên chất cùng sữa đặc, thích hợp cho những ngày làm việc bận rộn.', 'Hình-App_3006021-Cà-Phê-Sữa.jpg', 25000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(2, 'Trà đào cam sả', 'Trà đào cam sả thanh mát, mang đến sự kết hợp hài hòa giữa vị ngọt của đào, vị thanh của cam và mùi thơm nhẹ từ sả.', '30.-Tra-Dao-Cam-Sa.png', 30000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 2),
(3, 'Nước ép cam', 'Nước ép cam nguyên chất, cung cấp vitamin C dồi dào, giúp tăng cường sức đề kháng và giải khát tuyệt vời.', 'camvat.jpg', 25000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 3),
(4, 'Bánh mì nướng bơ', 'Bánh mì giòn tan, thấm đẫm bơ tỏi thơm lừng, một món ăn nhẹ lý tưởng để nhâm nhi cùng cà phê hoặc trà.', 'lam-banh-mi-nuong-bo-toi.jpg', 25000.00, 500, 4.70, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 5),
(5, 'Bánh flan', 'Món bánh flan mềm mịn, ngọt nhẹ và thơm mùi caramel, là lựa chọn hoàn hảo cho những bữa ăn nhẹ hoặc tráng miệng.', 'Flan-trung.jpg', 10000.00, 500, 4.60, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 5),
(6, 'Cà phê đen nóng', 'Một ly cà phê đen nóng đậm vị, giữ trọn hương thơm nguyên bản, dành cho những ai yêu thích sự đơn giản mà tinh tế.', 'images-_1_ (1).jpg', 20000.00, 500, 4.40, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(7, 'Trà sữa trân châu đường đen', 'Trà sữa trân châu đường đen với vị béo ngọt hòa quyện, trân châu dai giòn, hương vị hấp dẫn không thể bỏ qua.', 'mua-nguyen-lieu-lam-tra-sua-tran-chau-duong-den-o-dau_20240527014654.jpg', 25000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 2),
(8, 'Nước ép dưa hấu', 'Nước ép dưa hấu tươi ngon, ngọt mát và giải nhiệt cho ngày hè oi bức.', 'Hình-App_3006021-Ép-Dưa-Hấu.jpg', 25000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 3),
(9, 'Sinh tố xoài', 'Sinh tố xoài ngọt lịm, đậm vị xoài chín.', 'sinh-to-xoai.jpg', 35000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 4),
(10, 'Bánh ngọt choco', 'Bánh ngọt chocolate mềm mịn, thơm ngon.', 'images-_2_ (1).jpg', 27000.00, 500, 4.70, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 5),
(11, 'Espresso', 'Espresso đậm vị, phù hợp cho người yêu thích cà phê mạnh.', 'espresso-la-gi.jpg', 28000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(12, 'Trà gừng', 'Trà gừng nóng ấm, tốt cho sức khỏe.', 'Trung-Nguyen_Tet1188-1x1-1-800x800.jpg', 30000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 2),
(13, 'Nước ép táo', 'Nước ép táo ngọt ngào, tốt cho sức khỏe và sắc đẹp.', 'cong-dung-cua-nuoc-ep-tao-va-thoi-diem-uong-nuoc-ep-tao-tot-nhat-nuoc_tao_1-1596696321-81-width800height500.jpg', 30000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 3),
(14, 'Sinh tố dâu tây', 'Sinh tố dâu tây chua ngọt, thơm ngon và bổ dưỡng.', 'sinh-to-dau-tay.jpg', 40000.00, 500, 4.50, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 4),
(15, 'Snack khoai tây', 'Snack khoai tây giòn rụm, thích hợp cho các buổi trò chuyện.', 'orion-vi-ga-teriyaki-osaka-goi-lon-va-goi-nho-banh-keo-an-vat-imnuts-1_9ca3d29e67c34d26bc27b89f219abfde_master.jpg', 10000.00, 500, 4.60, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 5),
(16, 'Sữa chua', 'Bổ sung dưỡng chất cho sức khỏe, làm da sáng trẻ đẹp', 'sua_chua_an_vinamilk_it_duong__hop_100gr__d4904f4a20c5499f8d7670b8c0ff0094.jpg', 20000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 6),
(17, 'Latte', 'Latte sữa béo, vị nhẹ nhàng dễ uống, phù hợp cho mọi người.', '24.-Picasso-Latte-2-_-No.png', 30000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 3),
(18, 'Cappuccino', 'Cappuccino đậm đà, thơm ngon với lớp bọt sữa mịn màng.', 'istockphoto-505168330-612x612.jpg', 30000.00, 500, 5.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(19, 'Cà phê đá', 'Cà phê đá nguyên chất, thơm ngon, phù hợp cho những ai yêu thích vị cà phê đậm đà.', 'pngtree-iced-coffee-png-image_9237463.png', 20000.00, 500, 5.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(20, 'Cà phê sữa', 'Cà phê sữa ngọt dịu, kết hợp giữa vị cà phê và sữa đặc truyền thống.', 'images.jpg', 25000.00, 500, 5.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(21, 'Cà phê dừa', 'Cà phê kết hợp với nước cốt dừa béo ngậy, độc đáo và thơm ngon.', 'images-_2_.jpg', 25000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(22, 'Cà phê trứng', 'Sở hữu kiểu dáng mạnh mẽ, gam màu sang trọng, âm thanh sống động, hiệu ứng đèn LED RGB đẹp mắt, kết nối không dây nhanh chóng mang đến cho bạn những trải nghiệm tuyệt vời.', 'images-_1_.jpg', 25000.00, 500, 4.00, '2025-11-17 14:54:38', '2025-11-17 14:54:38', 1),
(24, 'trà sữa', 'ádfasdfas', '69df8ed7a7835.png', 25000.00, 500, 0.00, '2026-04-15 20:12:55', NULL, 1),
(26, 'cà phê đen', '123321', '69e1bb9fb6bd7.png', 1222221.00, 500, 0.00, '2026-04-17 11:48:31', NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `productreview`
--

CREATE TABLE `productreview` (
  `Id` int(11) NOT NULL,
  `StoreProductId` int(11) NOT NULL,
  `CustomerId` int(11) NOT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Comment` varchar(500) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `productreview`
--

INSERT INTO `productreview` (`Id`, `StoreProductId`, `CustomerId`, `Rating`, `Comment`, `CreatedAt`) VALUES
(26, 84, 2, 4, 'đâsdwdadas', '2026-04-15 09:51:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `revenue`
--

CREATE TABLE `revenue` (
  `Id` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `Month` int(11) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `Total` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `revenue`
--

INSERT INTO `revenue` (`Id`, `StoreId`, `Month`, `Year`, `Total`) VALUES
(1, 5, 4, 2026, 40501.00),
(2, 4, 4, 2026, 40501.00),
(3, 2, 4, 2026, 25000.00),
(4, 3, 4, 2026, 55501.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipment`
--

CREATE TABLE `shipment` (
  `Id` int(11) NOT NULL,
  `PaymentId` int(11) NOT NULL,
  `Carrier` varchar(100) DEFAULT NULL,
  `TrackingCode` varchar(100) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Latitude` decimal(9,6) DEFAULT NULL,
  `Longitude` decimal(9,6) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shipment`
--

INSERT INTO `shipment` (`Id`, `PaymentId`, `Carrier`, `TrackingCode`, `Status`, `Latitude`, `Longitude`, `UpdatedAt`) VALUES
(1, 1, 'DEMO', 'DEMO1776220001', 'ready_to_pick', NULL, NULL, '2026-04-15 09:26:41'),
(2, 2, 'DEMO', 'DEMO1776220048', 'ready_to_pick', NULL, NULL, '2026-04-15 09:27:28'),
(4, 4, 'DEMO', 'DEMO1776258188', 'ready_to_pick', NULL, NULL, '2026-04-15 20:03:08'),
(5, 5, 'DEMO', 'DEMO1776258305', 'ready_to_pick', NULL, NULL, '2026-04-15 20:05:05'),
(7, 7, 'DEMO', 'DEMO1776306700', 'ready_to_pick', NULL, NULL, '2026-04-16 09:31:40'),
(8, 8, 'DEMO', 'DEMO1776313244', 'ready_to_pick', NULL, NULL, '2026-04-16 11:20:44'),
(9, 9, 'DEMO', 'DEMO1776395348', 'ready_to_pick', NULL, NULL, '2026-04-17 10:09:08'),
(10, 10, 'DEMO', 'DEMO1776395693', 'ready_to_pick', NULL, NULL, '2026-04-17 10:14:53'),
(11, 11, 'DEMO', 'DEMO1776486772', 'ready_to_pick', NULL, NULL, '2026-04-18 11:32:52'),
(12, 12, 'DEMO', 'DEMO1776487146', 'ready_to_pick', NULL, NULL, '2026-04-18 11:39:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `store`
--

CREATE TABLE `store` (
  `Id` int(11) NOT NULL,
  `StoreName` varchar(200) NOT NULL,
  `Address` varchar(300) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `OpenTime` varchar(20) DEFAULT NULL,
  `CloseTime` varchar(20) DEFAULT NULL,
  `DistrictId` int(11) DEFAULT NULL,
  `WardCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `store`
--

INSERT INTO `store` (`Id`, `StoreName`, `Address`, `Phone`, `OpenTime`, `CloseTime`, `DistrictId`, `WardCode`) VALUES
(1, 'Trung Nguyên Legend – Võ Trứ', '148 Võ Trứ, Phường Tân Lập, Nha Trang, Khánh Hòa', '0258 3516 279', '06:30', '21:30', NULL, NULL),
(2, 'Trung Nguyên Legend – Lê Thánh Tôn', '44‑46 Lê Thánh Tôn, Phường Lộc Thọ, Nha Trang, Khánh Hòa', '0918 572 620', '06:30', '22:00', NULL, NULL),
(3, 'Trung Nguyên Legend – Vĩnh Phước', 'Vĩnh Phước, Nha Trang, Khánh Hòa', '0911 622 947', '06:30', '22:00', NULL, NULL),
(4, 'Trung Nguyên Legend – 19/5', '7549+RJG Đường 19/5, Vĩnh Điềm Trung, Nha Trang, Khánh Hòa', '0978 099 788', '07:00', '21:30', NULL, NULL),
(5, 'Trung Nguyên E‑Coffee – Lý Tự Trọng', '38 Lý Tự Trọng, Phường Lộc Thọ, Nha Trang, Khánh Hòa', '088.673.1188', '07:00', '21:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `storeproduct`
--

CREATE TABLE `storeproduct` (
  `Id` int(11) NOT NULL,
  `StoreId` int(11) NOT NULL,
  `ProductId` int(11) NOT NULL,
  `IsAvailable` tinyint(1) DEFAULT 1,
  `DiscountPercent` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `storeproduct`
--

INSERT INTO `storeproduct` (`Id`, `StoreId`, `ProductId`, `IsAvailable`, `DiscountPercent`) VALUES
(24, 1, 1, 0, 0),
(25, 1, 2, 1, 25),
(26, 1, 3, 1, 25),
(27, 1, 4, 0, 0),
(28, 1, 5, 1, 25),
(29, 1, 6, 0, 0),
(30, 1, 7, 1, 0),
(31, 1, 8, 1, 0),
(32, 1, 9, 1, 0),
(33, 1, 10, 1, 0),
(34, 1, 11, 0, 0),
(35, 1, 12, 1, 0),
(36, 1, 13, 1, 0),
(37, 1, 14, 1, 0),
(38, 1, 15, 1, 0),
(39, 1, 16, 1, 0),
(40, 1, 17, 1, 0),
(41, 1, 18, 0, 0),
(42, 1, 19, 1, 0),
(43, 1, 20, 0, 0),
(44, 1, 21, 0, 0),
(45, 1, 22, 1, 0),
(46, 2, 1, 1, 0),
(47, 2, 2, 1, 25),
(48, 2, 3, 0, 25),
(49, 2, 4, 1, 0),
(50, 2, 5, 1, 25),
(51, 2, 6, 0, 0),
(52, 2, 7, 1, 0),
(53, 2, 8, 1, 0),
(54, 2, 9, 1, 0),
(55, 2, 10, 1, 0),
(56, 2, 11, 0, 0),
(57, 2, 12, 1, 0),
(58, 2, 13, 0, 0),
(59, 2, 14, 1, 0),
(60, 2, 15, 1, 0),
(61, 2, 16, 1, 0),
(62, 2, 17, 1, 0),
(63, 2, 18, 1, 0),
(64, 2, 19, 0, 0),
(65, 2, 20, 1, 0),
(66, 2, 21, 1, 0),
(67, 2, 22, 1, 0),
(68, 3, 1, 1, 0),
(69, 3, 2, 1, 25),
(70, 3, 3, 0, 25),
(71, 3, 4, 1, 0),
(72, 3, 5, 1, 25),
(73, 3, 6, 1, 0),
(74, 3, 7, 1, 0),
(75, 3, 8, 1, 0),
(76, 3, 9, 1, 0),
(77, 3, 10, 1, 0),
(78, 3, 11, 0, 0),
(79, 3, 12, 1, 0),
(80, 3, 13, 1, 0),
(81, 3, 14, 1, 0),
(82, 3, 15, 0, 0),
(83, 3, 16, 1, 0),
(84, 3, 17, 1, 0),
(85, 3, 18, 1, 0),
(86, 3, 19, 1, 0),
(87, 3, 20, 0, 0),
(88, 3, 21, 1, 0),
(89, 3, 22, 1, 0),
(90, 4, 1, 1, 0),
(91, 4, 2, 1, 25),
(92, 4, 3, 0, 25),
(93, 4, 4, 1, 0),
(94, 4, 5, 1, 25),
(95, 4, 6, 0, 0),
(96, 4, 7, 1, 0),
(97, 4, 8, 1, 0),
(98, 4, 9, 1, 0),
(99, 4, 10, 1, 0),
(100, 4, 11, 1, 0),
(101, 4, 12, 1, 0),
(102, 4, 13, 1, 0),
(103, 4, 14, 1, 0),
(104, 4, 15, 1, 0),
(105, 4, 16, 1, 0),
(106, 4, 17, 1, 0),
(107, 4, 18, 0, 0),
(108, 4, 19, 1, 0),
(109, 4, 20, 1, 0),
(110, 4, 21, 0, 0),
(111, 4, 22, 1, 0),
(112, 5, 1, 1, 0),
(113, 5, 2, 1, 25),
(114, 5, 3, 1, 25),
(115, 5, 4, 1, 0),
(116, 5, 5, 1, 25),
(117, 5, 6, 1, 0),
(118, 5, 7, 1, 0),
(119, 5, 8, 1, 0),
(120, 5, 9, 0, 0),
(121, 5, 10, 1, 0),
(122, 5, 11, 0, 0),
(123, 5, 12, 0, 0),
(124, 5, 13, 1, 0),
(125, 5, 14, 0, 0),
(126, 5, 15, 1, 0),
(127, 5, 16, 1, 0),
(128, 5, 17, 1, 0),
(129, 5, 18, 1, 0),
(130, 5, 19, 1, 0),
(131, 5, 20, 1, 0),
(132, 5, 21, 0, 0),
(133, 5, 22, 1, 0),
(136, 5, 24, 1, 0),
(139, 5, 26, 1, 0),
(140, 4, 26, 1, 0),
(141, 2, 26, 1, 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_cart_customer` (`CustomerId`);

--
-- Chỉ mục cho bảng `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_cartitem_cart` (`CartId`),
  ADD KEY `fk_cartitem_storeproduct` (`StoreProductId`);

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unique_email` (`Email`),
  ADD UNIQUE KEY `unq_email` (`Email`),
  ADD UNIQUE KEY `unique_phone` (`Phone`),
  ADD UNIQUE KEY `unq_phone` (`Phone`);

--
-- Chỉ mục cho bảng `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `StoreId` (`StoreId`),
  ADD KEY `RoleId` (`RoleId`);

--
-- Chỉ mục cho bảng `employeerole`
--
ALTER TABLE `employeerole`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `StoreId` (`StoreId`);

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `CustomerId` (`CustomerId`),
  ADD KEY `StoreId` (`StoreId`);

--
-- Chỉ mục cho bảng `paymentdetail`
--
ALTER TABLE `paymentdetail`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_paymentdetail_payment` (`PaymentId`),
  ADD KEY `fk_paymentdetail_storeproduct` (`StoreProductId`);

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `CategoryId` (`CategoryId`);

--
-- Chỉ mục cho bảng `productreview`
--
ALTER TABLE `productreview`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unq_customer_store_product` (`CustomerId`,`StoreProductId`),
  ADD KEY `fk_review_storeproduct` (`StoreProductId`);

--
-- Chỉ mục cho bảng `revenue`
--
ALTER TABLE `revenue`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unq_store_time` (`StoreId`,`Month`,`Year`),
  ADD UNIQUE KEY `unq_store_month_year` (`StoreId`,`Month`,`Year`),
  ADD KEY `StoreId` (`StoreId`);

--
-- Chỉ mục cho bảng `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `PaymentId` (`PaymentId`);

--
-- Chỉ mục cho bảng `store`
--
ALTER TABLE `store`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `storeproduct`
--
ALTER TABLE `storeproduct`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unique_store_product` (`StoreId`,`ProductId`),
  ADD UNIQUE KEY `unq_sp` (`StoreId`,`ProductId`),
  ADD KEY `StoreId` (`StoreId`),
  ADD KEY `ProductId` (`ProductId`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `category`
--
ALTER TABLE `category`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `customer`
--
ALTER TABLE `customer`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `employee`
--
ALTER TABLE `employee`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `employeerole`
--
ALTER TABLE `employeerole`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `page`
--
ALTER TABLE `page`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `paymentdetail`
--
ALTER TABLE `paymentdetail`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `product`
--
ALTER TABLE `product`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `productreview`
--
ALTER TABLE `productreview`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `revenue`
--
ALTER TABLE `revenue`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `shipment`
--
ALTER TABLE `shipment`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `store`
--
ALTER TABLE `store`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `storeproduct`
--
ALTER TABLE `storeproduct`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`CustomerId`) REFERENCES `customer` (`Id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `fk_cartitem_cart` FOREIGN KEY (`CartId`) REFERENCES `cart` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cartitem_storeproduct` FOREIGN KEY (`StoreProductId`) REFERENCES `storeproduct` (`Id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`StoreId`) REFERENCES `store` (`Id`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`RoleId`) REFERENCES `employeerole` (`Id`);

--
-- Các ràng buộc cho bảng `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `page_ibfk_1` FOREIGN KEY (`StoreId`) REFERENCES `store` (`Id`);

--
-- Các ràng buộc cho bảng `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`CustomerId`) REFERENCES `customer` (`Id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`StoreId`) REFERENCES `store` (`Id`);

--
-- Các ràng buộc cho bảng `paymentdetail`
--
ALTER TABLE `paymentdetail`
  ADD CONSTRAINT `fk_paymentdetail_payment` FOREIGN KEY (`PaymentId`) REFERENCES `payment` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_paymentdetail_storeproduct` FOREIGN KEY (`StoreProductId`) REFERENCES `storeproduct` (`Id`);

--
-- Các ràng buộc cho bảng `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`CategoryId`) REFERENCES `category` (`Id`);

--
-- Các ràng buộc cho bảng `productreview`
--
ALTER TABLE `productreview`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`CustomerId`) REFERENCES `customer` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_storeproduct` FOREIGN KEY (`StoreProductId`) REFERENCES `storeproduct` (`Id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `revenue`
--
ALTER TABLE `revenue`
  ADD CONSTRAINT `revenue_ibfk_1` FOREIGN KEY (`StoreId`) REFERENCES `store` (`Id`);

--
-- Các ràng buộc cho bảng `shipment`
--
ALTER TABLE `shipment`
  ADD CONSTRAINT `shipment_ibfk_1` FOREIGN KEY (`PaymentId`) REFERENCES `payment` (`Id`);

--
-- Các ràng buộc cho bảng `storeproduct`
--
ALTER TABLE `storeproduct`
  ADD CONSTRAINT `storeproduct_ibfk_1` FOREIGN KEY (`StoreId`) REFERENCES `store` (`Id`),
  ADD CONSTRAINT `storeproduct_ibfk_2` FOREIGN KEY (`ProductId`) REFERENCES `product` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
