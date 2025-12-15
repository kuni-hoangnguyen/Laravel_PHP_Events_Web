-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for events_web
CREATE DATABASE IF NOT EXISTS `events_web` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `events_web`;

-- Dumping structure for table events_web.admin_logs
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_table` varchar(100) DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT (now()),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_admin_logs_admin_action` (`admin_id`,`action`),
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `admin_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.admin_logs: ~40 rows (approximately)
INSERT INTO `admin_logs` (`log_id`, `admin_id`, `user_id`, `action`, `target_table`, `target_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
	(1, 1, NULL, 'approve_event', 'events', 1, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-10 14:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(2, 1, NULL, 'approve_event', 'events', 2, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-08 10:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(3, 1, NULL, 'approve_event', 'events', 3, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-09 16:00:00", "approved_by": 1}', '192.168.1.101', '2025-12-04 15:16:57'),
	(4, 1, NULL, 'create_coupon', 'coupons', 4, NULL, '{"code": "TETHOLIDAY", "status": "active", "discount_percent": 30}', '192.168.1.100', '2025-12-04 15:16:57'),
	(5, 1, NULL, 'update_user_role', 'user_roles', NULL, NULL, '{"action": "assign_admin_role", "role_id": 1, "user_id": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(6, NULL, 4, 'create_event', 'events', 1, NULL, '{"title": "Hội thảo AI Việt Nam 2025", "organizer_id": 4}', '192.168.1.50', '2025-12-04 15:16:57'),
	(7, NULL, 4, 'purchase_tickets', 'tickets', 1, NULL, '{"quantity": 1, "ticket_id": 1, "ticket_type_id": 1}', '192.168.1.50', '2025-12-04 15:16:57'),
	(8, NULL, 5, 'purchase_tickets', 'tickets', 2, NULL, '{"quantity": 1, "ticket_id": 2, "ticket_type_id": 2}', '192.168.1.51', '2025-12-04 15:16:57'),
	(9, 1, NULL, 'approve_event', 'events', 9, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-14 10:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(10, 1, NULL, 'approve_event', 'events', 10, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-14 11:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(11, 1, NULL, 'approve_event', 'events', 11, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-14 12:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(12, 1, NULL, 'approve_event', 'events', 13, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-14 13:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(13, 1, NULL, 'approve_event', 'events', 14, '{"approved": 0}', '{"approved": 1, "approved_at": "2025-11-14 14:00:00", "approved_by": 1}', '192.168.1.100', '2025-12-04 15:16:57'),
	(14, 1, NULL, 'create_user', 'users', 11, NULL, '{"role": "organizer", "email": "lan.nguyen@techcorp.vn", "user_id": 11, "full_name": "Nguyễn Thị Lan"}', '192.168.1.100', '2025-12-04 15:16:57'),
	(15, 1, NULL, 'update_coupon', 'coupons', 1, '{"status": "active"}', '{"status": "expired", "used_count": 100}', '192.168.1.101', '2025-12-04 15:16:57'),
	(16, 1, NULL, 'process_refund', 'refunds', 2, '{"status": "pending"}', '{"status": "approved", "processed_at": "2025-11-14 15:00:00"}', '192.168.1.100', '2025-12-04 15:16:57'),
	(17, 1, NULL, 'delete_user', 'users', 15, '{"email": "mai.vo@hospital.gov.vn", "phone": "0945555555", "user_id": 15, "full_name": "Võ Thị Mai", "avatar_url": "/avatars/mai_vo.jpg", "created_at": "2025-12-04T08:16:57.000000Z", "updated_at": "2025-12-04T08:16:57.000000Z", "email_verified_at": "2025-11-04T07:00:00.000000Z"}', NULL, '127.0.0.1', '2025-12-04 15:20:57'),
	(18, 1, NULL, 'delete_event', 'events', 14, '{"deleted_at": null}', '{"deleted_at": "2025-12-04T08:22:38.618534Z"}', '127.0.0.1', '2025-12-04 15:22:38'),
	(19, 1, NULL, 'reject_event', 'events', 12, '{"approved": -1}', '{"approved": -1, "rejection_reason": "Không phù hợp với quy định"}', '127.0.0.1', '2025-12-04 15:33:26'),
	(20, NULL, 2, 'purchase_tickets', 'tickets', 20, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 500000, "payment_method": "Tiền mặt", "ticket_type_id": "3"}', '127.0.0.1', '2025-12-04 18:37:49'),
	(21, NULL, 21, 'purchase_tickets', 'tickets', 23, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 300000, "payment_method": "QR", "ticket_type_id": "2"}', '127.0.0.1', '2025-12-04 19:44:13'),
	(22, NULL, 21, 'purchase_tickets', 'tickets', 24, NULL, '{"event_id": "2", "quantity": "1", "total_amount": 400000, "payment_method": "QR", "ticket_type_id": "6"}', '127.0.0.1', '2025-12-04 19:45:34'),
	(23, NULL, 21, 'purchase_tickets', 'tickets', 25, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 150000, "payment_method": "PayOS", "ticket_type_id": "1"}', '127.0.0.1', '2025-12-04 20:16:19'),
	(24, NULL, 21, 'purchase_tickets', 'tickets', 26, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 500000, "payment_method": "PayOS", "ticket_type_id": "3"}', '127.0.0.1', '2025-12-04 20:17:11'),
	(25, NULL, 21, 'purchase_tickets', 'tickets', 27, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 150000, "payment_method": "PayOS", "ticket_type_id": "1"}', '127.0.0.1', '2025-12-04 20:19:10'),
	(26, NULL, 21, 'purchase_tickets', 'tickets', 28, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 500000, "payment_method": "PayOS", "ticket_type_id": "3"}', '127.0.0.1', '2025-12-04 20:19:20'),
	(27, NULL, 21, 'purchase_tickets', 'tickets', 29, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 150000, "payment_method": "PayOS", "ticket_type_id": "1"}', '127.0.0.1', '2025-12-04 20:20:11'),
	(28, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": null, "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 1, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 1, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": null, "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "1", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "1", "max_attendees": "500"}', '127.0.0.1', '2025-12-04 20:23:46'),
	(29, NULL, 2, 'purchase_tickets', 'tickets', 30, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 10000, "payment_method": "PayOS", "ticket_type_id": "27"}', '127.0.0.1', '2025-12-04 20:23:56'),
	(30, NULL, 2, 'purchase_tickets', 'tickets', 31, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 10000, "payment_method": "PayOS", "ticket_type_id": "27"}', '127.0.0.1', '2025-12-04 20:29:11'),
	(31, NULL, 2, 'purchase_tickets', 'tickets', 32, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 10000, "payment_method": "PayOS", "ticket_type_id": "27"}', '127.0.0.1', '2025-12-04 20:31:54'),
	(32, NULL, 2, 'purchase_tickets', 'tickets', 33, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 10000, "payment_method": "PayOS", "ticket_type_id": "27"}', '127.0.0.1', '2025-12-04 20:31:57'),
	(33, NULL, 1, 'purchase_tickets', 'tickets', 34, NULL, '{"event_id": "1", "quantity": "1", "total_amount": 10000, "payment_method": "PayOS", "ticket_type_id": "27"}', '127.0.0.1', '2025-12-04 20:44:37'),
	(34, NULL, 2, 'request_cancellation', 'events', 5, NULL, '{"cancellation_reason": "Thời tiết bão số 11 ập tới khiến cho sự kiện không có khả năng tiến hành.", "cancellation_requested": true}', '127.0.0.1', '2025-12-05 17:33:54'),
	(35, 1, NULL, 'approve_cancellation', 'events', 5, '{"status": "cancelled", "cancellation_requested": true}', '{"status": "cancelled", "cancellation_requested": false}', '127.0.0.1', '2025-12-05 17:34:49'),
	(36, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": null, "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 1, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 1, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": null, "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "2", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "10", "max_attendees": "500"}', '127.0.0.1', '2025-12-05 17:38:38'),
	(37, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": null, "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 2, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 10, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://localhost/storage/events/banners/2025/12/8d83f249-cdd7-4984-850a-9777f988a57c.jpg", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "2", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "10", "max_attendees": "500"}', '127.0.0.1', '2025-12-06 21:57:41'),
	(38, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://localhost/storage/events/banners/2025/12/8d83f249-cdd7-4984-850a-9777f988a57c.jpg", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 2, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 10, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://localhost/storage/events/banners/2025/12/f8a47c14-c214-4674-8b90-d6a7f8312d8a.jpg", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "2", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "10", "max_attendees": "500"}', '127.0.0.1', '2025-12-06 21:58:14'),
	(39, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://127.0.0.1:8000/storage/storage/events/banners/2025/12/f8a47c14-c214-4674-8b90-d6a7f8312d8a.jpg", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 2, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 10, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://127.0.0.1:8000/storage/events/banners/2025/12/4d7cec4c-3135-46c1-84a8-5e4535af2b6c.png", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "2", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "10", "max_attendees": "500"}', '127.0.0.1', '2025-12-06 22:05:20'),
	(40, NULL, 2, 'update_event', 'events', 1, '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://127.0.0.1:8000/storage/events/banners/2025/12/4d7cec4c-3135-46c1-84a8-5e4535af2b6c.png", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": 2, "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": 10, "max_attendees": 500}', '{"title": "Hội thảo Công nghệ AI Việt Nam 2025", "end_time": "2025-12-15T10:00:00.000000Z", "banner_url": "http://127.0.0.1:8000/storage/events/banners/2025/12/b915f7ee-e783-4075-bc4f-22f01e323c2e.jpg", "start_time": "2025-12-15T01:30:00.000000Z", "category_id": "2", "description": "Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.", "location_id": "10", "max_attendees": "500"}', '127.0.0.1', '2025-12-06 22:06:12');

-- Dumping structure for table events_web.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.cache: ~19 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('categories_list', 's:567:"a:7:{i:0;s:10:"Công nghệ";i:1;s:11:"Kinh doanh"...}"', 1764843417),
	('events_popular', 's:1234:"a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}"', 1764839817),
	('events_upcoming', 's:2345:"a:5:{i:0;a:3:{s:8:"event_id";i:1;s:5:"title";s:35:"Hội thảo Công nghệ AI"...}"', 1764838917),
	('laravel-cache-mock_payment:21', 'a:3:{s:8:"order_id";s:21:"MOCK_QR_21_1764852253";s:6:"status";s:7:"pending";s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2025-12-04 19:44:13.406433";s:13:"timezone_type";i:3;s:8:"timezone";s:16:"Asia/Ho_Chi_Minh";}}', 1764938653),
	('laravel-cache-mock_payment:22', 'a:3:{s:8:"order_id";s:21:"MOCK_QR_22_1764852334";s:6:"status";s:7:"pending";s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2025-12-04 19:45:34.569121";s:13:"timezone_type";i:3;s:8:"timezone";s:16:"Asia/Ho_Chi_Minh";}}', 1764938734),
	('laravel-cache-payment_status:21', 'a:3:{s:6:"status";s:4:"paid";s:8:"order_id";s:21:"MOCK_QR_21_1764852253";s:10:"updated_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2025-12-04 19:44:23.806207";s:13:"timezone_type";i:3;s:8:"timezone";s:16:"Asia/Ho_Chi_Minh";}}', 1764938663),
	('laravel-cache-payment_status:22', 'a:3:{s:6:"status";s:4:"paid";s:8:"order_id";s:21:"MOCK_QR_22_1764852334";s:10:"updated_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2025-12-04 19:45:36.150646";s:13:"timezone_type";i:3;s:8:"timezone";s:16:"Asia/Ho_Chi_Minh";}}', 1764938736),
	('laravel-cache-rate_limit:user:1:127.0.0.1', 'i:2;', 1764855933),
	('laravel-cache-rate_limit:user:1:127.0.0.1:timer', 'i:1764855933;', 1764855933),
	('laravel-cache-rate_limit:user:2:127.0.0.1', 'i:3;', 1764855172),
	('laravel-cache-rate_limit:user:2:127.0.0.1:timer', 'i:1764855172;', 1764855172),
	('laravel-cache-rate_limit:user:21:127.0.0.1', 'i:1;', 1764854471),
	('laravel-cache-rate_limit:user:21:127.0.0.1:timer', 'i:1764854471;', 1764854471),
	('notifications_count_4', 's:1:"3"', 1764837117),
	('seniksevents-cache-rate_limit:ip:127.0.0.1', 'i:1;', 1765032486),
	('seniksevents-cache-rate_limit:ip:127.0.0.1:timer', 'i:1765032486;', 1765032486),
	('seniksevents-cache-rate_limit:user:2:127.0.0.1', 'i:1;', 1765036221),
	('seniksevents-cache-rate_limit:user:2:127.0.0.1:timer', 'i:1765036221;', 1765036221),
	('user_4_profile', 's:890:"a:5:{s:7:"user_id";i:4;s:9:"full_name";s:15:"Phạm Thị Dung"...}"', 1764838017);

-- Dumping structure for table events_web.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.cache_locks: ~0 rows (approximately)

-- Dumping structure for table events_web.coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `coupon_id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_percent` int NOT NULL,
  `max_uses` int NOT NULL DEFAULT '1',
  `used_count` int DEFAULT '0',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `status` enum('active','expired','disabled') DEFAULT 'active',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_coupons_code` (`code`),
  KEY `idx_coupons_status` (`status`),
  CONSTRAINT `chk_coupon_dates` CHECK ((`valid_to` > `valid_from`)),
  CONSTRAINT `chk_coupon_uses` CHECK (((`used_count` <= `max_uses`) and (`used_count` >= 0))),
  CONSTRAINT `chk_discount_percent` CHECK (((`discount_percent` >= 0) and (`discount_percent` <= 100)))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.coupons: ~6 rows (approximately)
INSERT INTO `coupons` (`coupon_id`, `code`, `discount_percent`, `max_uses`, `used_count`, `valid_from`, `valid_to`, `status`) VALUES
	(1, 'DANGKYSOOM', 20, 100, 0, '2025-11-01 00:00:00', '2025-12-31 23:59:59', 'active'),
	(2, 'SINHVIEN50', 50, 500, 0, '2025-11-01 00:00:00', '2026-12-31 23:59:59', 'active'),
	(3, 'CHAODON10', 10, 1000, 0, '2025-11-01 00:00:00', '2026-06-30 23:59:59', 'active'),
	(4, 'TETDUONG', 30, 200, 0, '2025-11-01 00:00:00', '2026-02-28 23:59:59', 'active'),
	(5, 'GIANGVIEN25', 25, 150, 0, '2025-11-01 00:00:00', '2026-12-31 23:59:59', 'active'),
	(6, 'KHOINGHIEP40', 40, 50, 0, '2025-11-01 00:00:00', '2026-03-31 23:59:59', 'active');

-- Dumping structure for table events_web.events
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `organizer_id` int unsigned NOT NULL,
  `category_id` int NOT NULL,
  `location_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','ended','cancelled') DEFAULT 'upcoming',
  `max_attendees` int DEFAULT NULL,
  `created_at` datetime DEFAULT (now()),
  `updated_at` datetime DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `approved` tinyint DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `cancellation_requested` tinyint(1) DEFAULT '0',
  `cancellation_reason` text,
  `cancellation_requested_at` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_events_organizer` (`organizer_id`),
  KEY `idx_events_category` (`category_id`),
  KEY `idx_events_location` (`location_id`),
  KEY `idx_events_status` (`status`),
  KEY `idx_events_start_time` (`start_time`),
  KEY `idx_events_approved` (`approved`),
  KEY `idx_events_title` (`title`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`category_id`),
  CONSTRAINT `events_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `event_locations` (`location_id`),
  CONSTRAINT `events_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `chk_event_time` CHECK ((`end_time` > `start_time`)),
  CONSTRAINT `chk_max_attendees` CHECK (((`max_attendees` is null) or (`max_attendees` > 0)))
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.events: ~14 rows (approximately)
INSERT INTO `events` (`event_id`, `organizer_id`, `category_id`, `location_id`, `title`, `description`, `start_time`, `end_time`, `banner_url`, `status`, `max_attendees`, `created_at`, `updated_at`, `deleted_at`, `approved`, `approved_at`, `approved_by`, `cancellation_requested`, `cancellation_reason`, `cancellation_requested_at`) VALUES
	(1, 2, 2, 10, 'Hội thảo Công nghệ AI Việt Nam 2025', 'Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.', '2025-12-15 08:30:00', '2025-12-15 17:00:00', NULL, 'upcoming', 500, '2025-12-04 15:16:57', '2025-12-16 05:15:15', NULL, 1, '2025-11-10 14:00:00', 1, 0, NULL, NULL),
	(2, 3, 2, 2, 'Diễn đàn Khởi nghiệp Việt Nam', 'Sự kiện kết nối các startup Việt Nam với nhà đầu tư và mentor. Cơ hội tuyệt vời để học hỏi kinh nghiệm khởi nghiệp, gặp gỡ đối tác tiềm năng và tìm kiếm nguồn vốn đầu tư.', '2025-12-20 09:00:00', '2025-12-20 18:00:00', NULL, 'upcoming', 800, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 1, '2025-11-08 10:00:00', 1, 0, NULL, NULL),
	(3, 7, 1, 3, 'Workshop Lập trình Web với Laravel', 'Khóa học thực hành 2 ngày về Laravel Framework. Từ cơ bản đến nâng cao, xây dựng ứng dụng web hoàn chỉnh. Phù hợp cho sinh viên và lập trình viên mới bắt đầu.', '2025-12-22 08:00:00', '2025-12-23 17:00:00', NULL, 'upcoming', 100, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 1, '2025-11-09 16:00:00', 1, 0, NULL, NULL),
	(4, 9, 3, 4, 'Đêm nhạc "Những Khúc Hát Xưa"', 'Đêm nhạc tái hiện những ca khúc bất hủ của nhạc Việt với sự tham gia của các ca sĩ nổi tiếng. Không gian ấm cúng, đầy cảm xúc cho những ai yêu nhạc truyền thống.', '2025-12-25 19:30:00', '2025-12-25 22:00:00', NULL, 'upcoming', 1500, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 1, '2025-11-11 09:00:00', 1, 0, NULL, NULL),
	(5, 2, 5, 5, 'Khóa học "Kỹ năng Thuyết trình Hiệu quả"', '3 ngày học tập chuyên sâu về kỹ năng thuyết trình, giao tiếp công sở và thuyết phục khách hàng. Với nhiều bài tập thực hành và phản hồi từ chuyên gia.', '2025-12-28 08:30:00', '2025-12-30 17:30:00', NULL, 'cancelled', 80, '2025-12-04 15:16:57', '2025-12-05 17:34:49', NULL, 1, '2025-11-12 11:00:00', 1, 0, 'Thời tiết bão số 11 ập tới khiến cho sự kiện không có khả năng tiến hành.', '2025-12-05 17:33:54'),
	(6, 3, 4, 6, 'Giải đấu Esports "VN Championship"', 'Giải đấu game online lớn nhất Việt Nam với tổng giải thưởng 1 tỷ đồng. Thi đấu các game phổ biến như LMHT, PUBG Mobile, FIFA Online 4.', '2026-01-05 09:00:00', '2026-01-07 22:00:00', NULL, 'upcoming', 15000, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 0, NULL, NULL, 0, NULL, NULL),
	(7, 7, 6, 7, 'Festival Yoga & Meditation "Tìm về chính mình"', '3 ngày retreat yoga và thiền định tại resort sang trọng. Kết hợp giữa thực hành yoga, thiền, ăn chay và các hoạt động chăm sóc sức khỏe tinh thần.', '2026-01-12 06:00:00', '2026-01-14 18:00:00', NULL, 'upcoming', 200, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 1, '2025-11-13 08:00:00', 1, 0, NULL, NULL),
	(8, 9, 7, 8, 'Triển lãm Nghệ thuật Đương đại Việt Nam', 'Triển lãm quy mô lớn giới thiệu tác phẩm của 50 nghệ sĩ Việt Nam. Bao gồm hội họa, điêu khắc, nghệ thuật số và các tác phẩm installation độc đáo.', '2026-01-18 10:00:00', '2026-01-25 20:00:00', NULL, 'upcoming', 5000, '2025-12-04 15:16:57', '2025-12-04 15:16:57', NULL, 1, '2025-11-13 15:00:00', 1, 0, NULL, NULL),
	(9, 11, 1, 9, 'Bootcamp Lập trình Full-Stack 2025', 'Khóa học intensive 7 ngày về lập trình Full-Stack: React, Node.js, MongoDB. Từ zero đến hero với project thực tế và mentorship 1-on-1.', '2026-01-20 08:00:00', '2026-01-26 18:00:00', NULL, 'upcoming', 50, '2025-12-04 15:16:57', '2025-12-08 17:41:42', NULL, 1, '2025-11-14 10:00:00', 1, 0, NULL, NULL),
	(10, 13, 7, 10, 'Workshop "Thiết kế UI/UX cho Mobile App"', '2 ngày học thiết kế giao diện và trải nghiệm người dùng cho ứng dụng di động. Sử dụng Figma, Adobe XD và các tool design hiện đại.', '2026-02-01 09:00:00', '2026-02-02 17:00:00', NULL, 'upcoming', 80, '2025-12-04 15:16:57', '2025-12-08 17:41:42', NULL, 1, '2025-11-14 11:00:00', 1, 0, NULL, NULL),
	(11, 19, 3, 1, 'Đêm Chung kết "Vietnam\'s Got Talent Startup"', 'Đêm chung kết cuộc thi tài năng dành cho startup Việt Nam. 20 startup hàng đầu sẽ tranh tài và tìm kiếm nhà đầu tư tiềm năng.', '2026-02-14 18:30:00', '2026-02-14 22:00:00', NULL, 'upcoming', 2000, '2025-12-04 15:16:57', '2025-12-08 17:41:43', NULL, 1, '2025-11-14 12:00:00', 1, 0, NULL, NULL),
	(12, 11, 5, 2, 'Hội thảo "Kỹ năng Lãnh đạo trong Kỷ nguyên số"', 'Học cách lãnh đạo team trong môi trường công nghệ số. Với sự tham gia của các CEO hàng đầu Việt Nam và chuyên gia quốc tế.', '2026-02-28 08:30:00', '2026-02-28 17:30:00', NULL, 'upcoming', 300, '2025-12-04 15:16:57', '2025-12-08 17:41:43', NULL, -1, NULL, NULL, 0, NULL, NULL),
	(13, 13, 6, 3, 'Retreat "Tìm lại cân bằng cuộc sống"', '3 ngày 2 đêm retreat tại resort 5 sao. Kết hợp yoga, meditation, healthy eating và các workshop về work-life balance.', '2026-03-15 14:00:00', '2026-03-17 12:00:00', NULL, 'upcoming', 100, '2025-12-04 15:16:57', '2025-12-08 17:41:43', NULL, 1, '2025-11-14 13:00:00', 1, 0, NULL, NULL),
	(14, 19, 4, 4, 'Giải Marathon "Chạy vì Trái Đất Xanh"', 'Giải chạy marathon từ thiện gây quỹ trồng cây xanh. 3 cự ly: 5km, 10km, 21km. Có giải thưởng và quà tặng ý nghĩa.', '2026-03-22 05:30:00', '2026-03-22 12:00:00', NULL, 'upcoming', 5000, '2025-12-04 15:16:57', '2025-12-08 17:41:44', '2025-12-04 15:22:38', 1, '2025-11-14 14:00:00', 1, 0, NULL, NULL);

-- Dumping structure for table events_web.event_categories
CREATE TABLE IF NOT EXISTS `event_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.event_categories: ~7 rows (approximately)
INSERT INTO `event_categories` (`category_id`, `category_name`, `description`) VALUES
	(1, 'Công nghệ', 'Hội thảo công nghệ, workshop và buổi gặp mặt kỹ thuật'),
	(2, 'Kinh doanh', 'Hội thảo kinh doanh, sự kiện networking và hội thảo chuyên môn'),
	(3, 'Giải trí', 'Buổi hòa nhạc, chương trình biểu diễn và sự kiện giải trí'),
	(4, 'Thể thao', 'Sự kiện thể thao, giải đấu và các cuộc thi'),
	(5, 'Giáo dục', 'Workshop giáo dục, khóa học và bài gi강'),
	(6, 'Sức khỏe', 'Sự kiện chăm sóc sức khỏe, lớp tập thể dục'),
	(7, 'Nghệ thuật', 'Triển lãm nghệ thuật, sự kiện văn hóa và workshop sáng tạo');

-- Dumping structure for table events_web.event_locations
CREATE TABLE IF NOT EXISTS `event_locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `capacity` int DEFAULT '0',
  PRIMARY KEY (`location_id`),
  KEY `idx_events_city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.event_locations: ~10 rows (approximately)
INSERT INTO `event_locations` (`location_id`, `name`, `address`, `city`, `capacity`) VALUES
	(1, 'Trung tâm Hội nghị Quốc gia', '123 Đường Lê Duẩn, Quận 1', 'Hồ Chí Minh', 3000),
	(2, 'Nhà văn hóa Thanh niên', '456 Phố Huế, Hai Bà Trưng', 'Hà Nội', 800),
	(3, 'Trung tâm Triển lãm Sài Gòn', '789 Nguyễn Văn Linh, Quận 7', 'Hồ Chí Minh', 2500),
	(4, 'Đại học Bách khoa Hà Nội', '1 Đại Cồ Việt, Hai Bà Trưng', 'Hà Nội', 1200),
	(5, 'Sân vận động Mỹ Đình', '2 Đường Phạm Hùng, Nam Từ Liêm', 'Hà Nội', 40000),
	(6, 'Khách sạn Rex Sài Gòn', '141 Nguyễn Huệ, Quận 1', 'Hồ Chí Minh', 500),
	(7, 'Công viên Tao Đàn', '1 Trương Định, Quận 1', 'Hồ Chí Minh', 1500),
	(8, 'Trung tâm Hòa Bình', '27 Lý Thường Kiệt, Hoàn Kiếm', 'Hà Nội', 600),
	(9, 'Resort FLC Quy Nhon', '123 Đường Nguyễn Tất Thành', 'Quy Nhon', 1000),
	(10, 'Vinpearl Land Nha Trang', '456 Trần Phú, Lộc Thọ', 'Nha Trang', 2000);

-- Dumping structure for table events_web.event_maps
CREATE TABLE IF NOT EXISTS `event_maps` (
  `map_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `map_image_url` varchar(255) NOT NULL,
  `note` text,
  PRIMARY KEY (`map_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_maps_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.event_maps: ~7 rows (approximately)
INSERT INTO `event_maps` (`map_id`, `event_id`, `map_image_url`, `note`) VALUES
	(1, 1, '/images/maps/ai-conference-floor-plan.jpg', 'Sơ đồ mặt bằng hội trường với khu vực VIP và networking area'),
	(2, 2, '/images/maps/startup-forum-layout.png', 'Layout không gian triển lãm startup và khu vực pitch'),
	(3, 3, '/images/maps/laravel-workshop-classroom.jpg', 'Sơ đồ phòng học workshop với bàn máy tính và projector'),
	(4, 4, '/images/maps/concert-seating-chart.jpg', 'Sơ đồ chỗ ngồi đêm nhạc với phân khu VIP, VVIP và thường'),
	(5, 5, '/images/maps/presentation-training-room.png', 'Layout phòng đào tạo với không gian thuyết trình và ghế khán giả'),
	(6, 7, '/images/maps/yoga-festival-outdoor.jpg', 'Sơ đồ khu vực yoga ngoài trời với các zone thiền và workshop'),
	(7, 8, '/images/maps/art-exhibition-gallery.png', 'Bố trí triển lãm nghệ thuật với các khu vực tác phẩm theo chủ đề');

-- Dumping structure for table events_web.event_tags
CREATE TABLE IF NOT EXISTS `event_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(100) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.event_tags: ~12 rows (approximately)
INSERT INTO `event_tags` (`tag_id`, `tag_name`) VALUES
	(11, 'Bootcamp'),
	(4, 'Đào tạo'),
	(12, 'Đỉnh cao'),
	(10, 'Gặp mặt'),
	(5, 'Hội nghị'),
	(1, 'Hội thảo'),
	(3, 'Kết nối'),
	(8, 'Lễ hội'),
	(7, 'Thi đấu'),
	(6, 'Triển lãm'),
	(9, 'Webinar'),
	(2, 'Workshop');

-- Dumping structure for table events_web.event_tag_map
CREATE TABLE IF NOT EXISTS `event_tag_map` (
  `event_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`event_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `event_tag_map_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `event_tag_map_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `event_tags` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bảng many-to-many: Event có thể có nhiều tags để tăng khả năng tìm kiếm';

-- Dumping data for table events_web.event_tag_map: ~35 rows (approximately)
INSERT INTO `event_tag_map` (`event_id`, `tag_id`) VALUES
	(1, 1),
	(2, 1),
	(4, 1),
	(12, 1),
	(3, 2),
	(5, 2),
	(7, 2),
	(9, 2),
	(10, 2),
	(13, 2),
	(2, 3),
	(1, 4),
	(3, 4),
	(5, 4),
	(9, 4),
	(10, 4),
	(12, 4),
	(5, 5),
	(12, 5),
	(8, 6),
	(6, 7),
	(11, 7),
	(14, 7),
	(4, 8),
	(6, 8),
	(7, 8),
	(8, 8),
	(11, 8),
	(13, 8),
	(14, 8),
	(1, 9),
	(3, 11),
	(9, 11),
	(2, 12),
	(11, 12);

-- Dumping structure for table events_web.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table events_web.favorites
CREATE TABLE IF NOT EXISTS `favorites` (
  `user_id` int unsigned NOT NULL,
  `event_id` int NOT NULL,
  `created_at` datetime DEFAULT (now()),
  PRIMARY KEY (`user_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bảng many-to-many: User có thể favorite nhiều events quan tâm';

-- Dumping data for table events_web.favorites: ~18 rows (approximately)
INSERT INTO `favorites` (`user_id`, `event_id`, `created_at`) VALUES
	(4, 3, '2025-12-04 15:16:57'),
	(4, 5, '2025-12-04 15:16:57'),
	(5, 4, '2025-12-04 15:16:57'),
	(5, 6, '2025-12-04 15:16:57'),
	(6, 7, '2025-12-04 15:16:57'),
	(6, 8, '2025-12-04 15:16:57'),
	(8, 2, '2025-12-04 15:16:57'),
	(8, 3, '2025-12-04 15:16:57'),
	(10, 1, '2025-12-04 15:16:57'),
	(10, 5, '2025-12-04 15:16:57'),
	(12, 9, '2025-12-04 15:16:57'),
	(12, 11, '2025-12-04 15:16:57'),
	(14, 10, '2025-12-04 15:16:57'),
	(16, 14, '2025-12-04 15:16:57'),
	(17, 11, '2025-12-04 15:16:57'),
	(17, 13, '2025-12-04 15:16:57'),
	(18, 9, '2025-12-04 15:16:57'),
	(20, 10, '2025-12-04 15:16:57');

-- Dumping structure for table events_web.incident_reports
CREATE TABLE IF NOT EXISTS `incident_reports` (
  `incident_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `reporter_id` int unsigned NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT (now()),
  `resolved_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`incident_id`),
  KEY `event_id` (`event_id`),
  KEY `reporter_id` (`reporter_id`),
  CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `incident_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.incident_reports: ~7 rows (approximately)
INSERT INTO `incident_reports` (`incident_id`, `event_id`, `reporter_id`, `description`, `status`, `created_at`, `resolved_at`, `updated_at`) VALUES
	(1, 1, 4, 'Hệ thống âm thanh bị nghịch âm trong 10 phút đầu buổi hội thảo. Đã được khắc phục kịp thời.', 'resolved', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57'),
	(2, 2, 8, 'Có người tham gia không đúng dress code, gây ảnh hưởng đến không khí sự kiện.', 'closed', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57'),
	(3, 4, 5, 'Một số ghế VIP bị hỏng, cần thay thế trước giờ diễn ra sự kiện.', 'in_progress', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57'),
	(4, 3, 14, 'Máy chiếu bị hỏng trong 30 phút đầu workshop Laravel. Đã thay máy dự phòng và tiếp tục bình thường.', 'resolved', '2025-12-04 15:16:57', '2025-12-22 09:30:00', '2025-12-04 15:16:57'),
	(5, 9, 12, 'WiFi tại venue không ổn định, ảnh hưởng đến việc code practice. Cần kiểm tra trước các event tech.', 'in_progress', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57'),
	(6, 11, 17, 'Một số startup không chuẩn bị đầy đủ demo, gây delay cho chương trình. Cần checklist rõ ràng hơn.', 'open', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57'),
	(7, 14, 16, 'Thời tiết mưa to vào sáng marathon, cần plan B cho các sự kiện outdoor.', 'open', '2025-12-04 15:16:57', NULL, '2025-12-04 15:16:57');

-- Dumping structure for table events_web.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.jobs: ~0 rows (approximately)

-- Dumping structure for table events_web.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.job_batches: ~0 rows (approximately)

-- Dumping structure for table events_web.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table events_web.migrations: ~2 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2025_01_01_000001_add_qr_payment_method', 1),
	(2, '2025_12_05_051655_add_created_at_to_payments_table', 2);

-- Dumping structure for table events_web.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT (now()),
  PRIMARY KEY (`notification_id`),
  KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  KEY `idx_notifications_created` (`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.notifications: ~25 rows (approximately)
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `action_url`, `created_at`) VALUES
	(1, 4, 'Thanh toán thành công', 'Bạn đã thanh toán thành công vé tham dự "Hội thảo Công nghệ AI Việt Nam 2025". Mã QR: QR001AIVN2025STU004', 'success', 1, NULL, '2025-12-04 15:16:57'),
	(2, 5, 'Sự kiện sắp diễn ra', 'Sự kiện "Hội thảo Công nghệ AI Việt Nam 2025" sẽ bắt đầu trong 3 ngày. Đừng quên mang theo vé và CCCD.', 'info', 1, NULL, '2025-12-04 15:16:57'),
	(3, 6, 'Cập nhật sự kiện', 'Thời gian đăng ký "Đêm nhạc Những Khúc Hát Xưa" đã được gia hạn đến 18:00 ngày 25/12/2025.', 'info', 0, NULL, '2025-12-04 15:16:57'),
	(4, 8, 'Sự kiện mới', 'Có sự kiện mới phù hợp với sở thích của bạn: "Workshop Lập trình Web với Laravel". Đăng ký ngay!', 'info', 0, NULL, '2025-12-04 15:16:57'),
	(5, 10, 'Vé chờ thanh toán', 'Bạn có 1 vé đang chờ thanh toán. Vui lòng hoàn tất thanh toán trong 24h để giữ chỗ.', 'warning', 0, NULL, '2025-12-04 15:16:57'),
	(6, 4, 'Khuyến mãi đặc biệt', 'Sử dụng mã STUDENT50 để được giảm 50% cho tất cả sự kiện giáo dục. Có hiệu lực đến cuối năm!', 'success', 1, NULL, '2025-12-04 15:16:57'),
	(7, 5, 'Nhắc nhở check-in', 'Đừng quên check-in tại sự kiện "Hội thảo AI" bằng mã QR. Quầy đăng ký mở cửa từ 8:00 sáng.', 'info', 1, NULL, '2025-12-04 15:16:57'),
	(8, 12, 'Thanh toán thành công', 'Bạn đã thanh toán thành công vé "Bootcamp Full-Stack 2025". Chuẩn bị tinh thần cho 7 ngày intensive learning!', 'success', 0, NULL, '2025-12-04 15:16:57'),
	(9, 14, 'Sự kiện yêu thích mở bán vé', 'Sự kiện "Workshop UI/UX cho Mobile App" đã mở bán vé. Số lượng có hạn, đăng ký ngay!', 'info', 0, NULL, '2025-12-04 15:16:57'),
	(11, 16, 'Sự kiện sắp diễn ra', 'Marathon "Chạy vì Trái Đất Xanh" sẽ bắt đầu trong 1 tuần. Hãy chuẩn bị thể lực tốt nhất!', 'info', 1, NULL, '2025-12-04 15:16:57'),
	(12, 17, 'Mã giảm giá đặc biệt', 'Chúc mừng bạn được chọn nhận mã VIPEXCLUSIVE30 - giảm 30% cho tất cả sự kiện Premium!', 'success', 0, NULL, '2025-12-04 15:16:57'),
	(13, 18, 'Cập nhật sự kiện', 'Địa điểm "Đêm Chung kết Startup Talent" đã được thay đổi sang Nhà văn hóa Thanh niên. Check email để biết chi tiết.', 'warning', 0, NULL, '2025-12-04 15:16:57'),
	(14, 20, 'Kết quả cuộc thi', 'Chúc mừng! Bạn đã trúng giải phụ trong cuộc thi "Dự đoán Winner Startup Talent" - 1 suất miễn phí workshop UI/UX!', 'success', 0, NULL, '2025-12-04 15:16:57'),
	(15, 11, 'Sự kiện bị từ chối', 'Sự kiện \'Hội thảo "Kỹ năng Lãnh đạo trong Kỷ nguyên số"\' đã bị từ chối. Lý do: Không phù hợp với quy định', 'warning', 0, NULL, '2025-12-04 15:33:26'),
	(16, 2, 'Thanh toán tiền mặt chờ xác nhận', 'Trần Thị Bình đã mua 1 vé cho sự kiện \'Hội thảo Công nghệ AI Việt Nam 2025\' bằng tiền mặt. Tổng tiền: 500.000 VND. Vui lòng xác nhận khi nhận được tiền.', 'info', 1, 'http://127.0.0.1:8000/events/1/pending-payments', '2025-12-04 18:37:49'),
	(17, 2, 'Mua vé thành công', 'Thanh toán cho sự kiện \'Hội thảo Công nghệ AI Việt Nam 2025\' đã được xác nhận thành công. Số tiền: 500.000 VND. Vé của bạn đã sẵn sàng!', 'success', 1, 'http://127.0.0.1:8000/tickets/20', '2025-12-04 18:37:53'),
	(18, 21, 'Chào mừng bạn đến với Events Management!', 'Tài khoản của bạn đã được tạo thành công. Vui lòng kiểm tra email để xác thực tài khoản.', 'success', 1, NULL, '2025-12-04 18:39:31'),
	(19, 21, 'Mua vé thành công', 'Thanh toán cho sự kiện \'Hội thảo Công nghệ AI Việt Nam 2025\' đã được xác nhận thành công. Số tiền: 300.000 VND. Vé của bạn đã sẵn sàng!', 'success', 1, 'http://127.0.0.1:8000/tickets/23', '2025-12-04 19:44:23'),
	(20, 21, 'Mua vé thành công', 'Thanh toán cho sự kiện \'Diễn đàn Khởi nghiệp Việt Nam\' đã được xác nhận thành công. Số tiền: 400.000 VND. Vé của bạn đã sẵn sàng!', 'success', 1, 'http://127.0.0.1:8000/tickets/24', '2025-12-04 19:45:36'),
	(21, 2, 'Mua vé thành công', 'Bạn đã mua thành công 1 vé cho sự kiện \'Hội thảo Công nghệ AI Việt Nam 2025\'. Tổng tiền: 10.000 VND', 'success', 1, NULL, '2025-12-04 20:32:28'),
	(22, 1, 'Mua vé thành công', 'Bạn đã mua thành công 1 vé cho sự kiện \'Hội thảo Công nghệ AI Việt Nam 2025\'. Tổng tiền: 10.000 VND', 'success', 1, NULL, '2025-12-04 20:45:09'),
	(23, 1, 'Yêu cầu hủy sự kiện', 'Organizer \'Trần Thị Bình\' đã yêu cầu hủy sự kiện \'Khóa học "Kỹ năng Thuyết trình Hiệu quả"\'. Lý do: Thời tiết bão số 11 ập tới khiến cho sự kiện không có khả năng tiến hành.', 'warning', 1, 'http://127.0.0.1:8000/admin/events?status=cancellation', '2025-12-05 17:33:54'),
	(24, 2, 'Yêu cầu hủy sự kiện đã được duyệt', 'Yêu cầu hủy sự kiện \'Khóa học "Kỹ năng Thuyết trình Hiệu quả"\' đã được admin chấp thuận. Sự kiện đã được đánh dấu là đã hủy.', 'info', 1, NULL, '2025-12-05 17:34:49'),
	(25, 8, 'Sự kiện đã bị hủy', 'Sự kiện \'Khóa học "Kỹ năng Thuyết trình Hiệu quả"\' mà bạn đã mua vé đã bị hủy. Vui lòng kiểm tra thông tin hoàn tiền nếu có.', 'warning', 0, 'http://127.0.0.1:8000/tickets', '2025-12-05 17:34:49'),
	(26, 12, 'Sự kiện đã bị hủy', 'Sự kiện \'Khóa học "Kỹ năng Thuyết trình Hiệu quả"\' mà bạn đã mua vé đã bị hủy. Vui lòng kiểm tra thông tin hoàn tiền nếu có.', 'warning', 0, 'http://127.0.0.1:8000/tickets', '2025-12-05 17:34:49');

-- Dumping structure for table events_web.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table events_web.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `method_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('success','failed','refunded') DEFAULT 'success',
  `transaction_id` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT (now()),
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `method_id` (`method_id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_transaction_id` (`transaction_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`),
  CONSTRAINT `chk_payment_amount` CHECK ((`amount` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.payments: ~31 rows (approximately)
INSERT INTO `payments` (`payment_id`, `ticket_id`, `method_id`, `amount`, `status`, `transaction_id`, `paid_at`, `created_at`) VALUES
	(1, 1, 1, 120000.00, 'success', 'VCB001VN20251108001', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(2, 2, 4, 300000.00, 'success', 'MOMO002VN20251108002', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(3, 3, 2, 500000.00, 'success', 'ACB003VN20251108003', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(4, 4, 1, 160000.00, 'success', 'VCB004VN20251108004', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(5, 6, 3, 800000.00, 'success', 'CASH006VN20251108006', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(6, 7, 1, 500000.00, 'success', 'VCB007VN20251108007', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(7, 8, 4, 500000.00, 'success', 'ZALO008VN20251108008', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(8, 9, 5, 750000.00, 'success', 'QR009VN20251108009', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(9, 10, 1, 4000000.00, 'success', 'VCB010VN20251114010', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(10, 11, 4, 7000000.00, 'success', 'MOMO011VN20251114011', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(12, 13, 1, 1200000.00, 'success', 'VCB013VN20251114013', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(13, 14, 3, 200000.00, 'success', 'CASH014VN20251114014', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(14, 16, 5, 3500000.00, 'success', 'QR016VN20251114016', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(15, 17, 1, 150000.00, 'success', 'VCB017VN20251114017', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(16, 18, 4, 250000.00, 'success', 'ZALO018VN20251114018', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(17, 19, 1, 400000.00, 'success', 'VCB019VN20251114019', '2025-12-04 15:16:57', '2025-12-04 08:16:57'),
	(18, 20, 3, 500000.00, 'success', 'CASH_1764848269_2', '2025-12-04 18:37:53', '2025-12-04 11:37:53'),
	(19, 21, 5, 500000.00, 'failed', 'VNPAY_21_1764851770_21', NULL, '2025-12-04 22:22:34'),
	(20, 22, 5, 500000.00, 'failed', 'VNPAY_22_1764851871_21', NULL, '2025-12-04 22:22:34'),
	(21, 23, 5, 300000.00, 'success', 'TXN_1764852253_21', '2025-12-04 19:44:23', '2025-12-04 12:44:23'),
	(22, 24, 5, 400000.00, 'success', 'TXN_1764852334_21', '2025-12-04 19:45:36', '2025-12-04 12:45:36'),
	(23, 25, 5, 150000.00, 'failed', 'PAYOS_1764854179_21', NULL, '2025-12-04 22:22:34'),
	(24, 26, 5, 500000.00, 'failed', 'PAYOS_1764854231_21', NULL, '2025-12-04 22:22:34'),
	(25, 27, 5, 150000.00, 'failed', 'PAYOS_1764854350_21', NULL, '2025-12-04 22:22:34'),
	(26, 28, 5, 500000.00, 'failed', 'PAYOS_1764854360_21', NULL, '2025-12-04 22:22:34'),
	(27, 29, 5, 150000.00, 'failed', '27411', NULL, '2025-12-04 22:22:34'),
	(28, 30, 5, 10000.00, 'failed', '28636', NULL, '2025-12-04 22:22:34'),
	(29, 31, 5, 10000.00, 'failed', '29951', NULL, '2025-12-04 22:22:34'),
	(30, 32, 5, 10000.00, 'failed', '30114', NULL, '2025-12-04 22:22:34'),
	(31, 33, 5, 10000.00, 'success', '31117', '2025-12-04 20:32:28', '2025-12-04 13:32:28'),
	(32, 34, 5, 10000.00, 'success', '32877', '2025-12-04 20:45:09', '2025-12-04 13:45:09');

-- Dumping structure for table events_web.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `method_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`method_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.payment_methods: ~5 rows (approximately)
INSERT INTO `payment_methods` (`method_id`, `name`, `description`) VALUES
	(1, 'Thẻ tín dụng', 'Thanh toán qua thẻ tín dụng hoặc thẻ ghi nợ'),
	(2, 'Chuyển khoản ngân hàng', 'Thanh toán chuyển khoản trực tiếp'),
	(3, 'Tiền mặt', 'Thanh toán bằng tiền mặt tại địa điểm'),
	(4, 'Ví điện tử', 'Thanh toán qua ứng dụng ví điện tử như MoMo, ZaloPay'),
	(5, 'PayOS', 'Thanh toán online qua PayOS. Hỗ trợ thẻ tín dụng, ví điện tử và chuyển khoản ngân hàng.');

-- Dumping structure for table events_web.refunds
CREATE TABLE IF NOT EXISTS `refunds` (
  `refund_id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `requester_id` int unsigned NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT (now()),
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`refund_id`),
  KEY `payment_id` (`payment_id`),
  KEY `requester_id` (`requester_id`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`),
  CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.refunds: ~3 rows (approximately)
INSERT INTO `refunds` (`refund_id`, `payment_id`, `requester_id`, `reason`, `status`, `created_at`, `processed_at`) VALUES
	(1, 5, 10, 'Không thể tham gia do lý do cá nhân đột xuất. Xin hoàn tiền vé investor.', 'pending', '2025-12-04 15:16:57', NULL),
	(2, 2, 8, 'Sự kiện bị hoãn, yêu cầu hoàn tiền theo chính sách.', 'approved', '2025-12-04 15:16:57', NULL),
	(4, 15, 18, 'Sự kiện bị hoãn từ tháng 2 sang tháng 3, không sắp xếp được lịch mới.', 'pending', '2025-12-04 15:16:57', NULL);

-- Dumping structure for table events_web.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int unsigned NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT (now()),
  `updated_at` datetime DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_user_event_review` (`event_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_reviews_event` (`event_id`),
  KEY `idx_reviews_rating` (`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `chk_rating` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.reviews: ~9 rows (approximately)
INSERT INTO `reviews` (`review_id`, `event_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
	(1, 1, 4, 5, 'Hội thảo rất bổ ích! Các diễn giả am hiểu sâu sắc về AI. Tài liệu chi tiết và thực tế. Sẽ giới thiệu cho bạn bè tham gia các sự kiện sau.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(2, 1, 5, 4, 'Nội dung hay, tuy nhiên phòng hơi nhỏ so với số lượng người tham gia. Âm thanh có lúc không rõ. Nhìn chung vẫn rất đáng tham gia.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(3, 1, 6, 5, 'Xuất sắc! Mình đã học được rất nhiều về xu hướng AI tại Việt Nam. Networking session rất hiệu quả, đã kết nối được với nhiều chuyên gia.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(4, 2, 8, 4, 'Diễn đàn khởi nghiệp rất thú vị. Nhiều startup có ý tưởng sáng tạo. Tuy nhiên thời gian hơi gấp gáp, mong có thêm thời gian thảo luận.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(5, 2, 12, 5, 'Diễn đàn khởi nghiệp tuyệt vời! Đã kết nối được với nhiều mentor và nhà đầu tư. Network rất chất lượng, tổ chức chuyên nghiệp.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(6, 3, 14, 4, 'Workshop Laravel rất thực tế. Giảng viên kinh nghiệm, bài tập phong phú. Chỉ tiếc là thời gian hơi gấp, mong có thêm thời gian practice.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(8, 5, 16, 3, 'Khóa học thuyết trình có nội dung tốt nhưng phòng học hơi chật. Một số bài tập chưa thực tế lắm. Trainer thì rất nhiệt tình.', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(9, 7, 17, 5, 'Festival Yoga tuyệt vời nhất mình từng tham gia! Resort đẹp, instructor chuyên nghiệp, thức ăn healthy ngon. Sẽ quay lại năm sau!', '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(10, 8, 18, 4, 'Triển lãm nghệ thuật rất đa dạng và phong phú. Tác phẩm chất lượng cao, không gian bố trí hợp lý. Giá vé hơi cao so với thời gian tham quan.', '2025-12-04 15:16:57', '2025-12-04 15:16:57');

-- Dumping structure for table events_web.review_reports
CREATE TABLE IF NOT EXISTS `review_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `reporter_id` int unsigned NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` datetime DEFAULT (now()),
  PRIMARY KEY (`report_id`),
  KEY `review_id` (`review_id`),
  KEY `reporter_id` (`reporter_id`),
  CONSTRAINT `review_reports_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`),
  CONSTRAINT `review_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.review_reports: ~3 rows (approximately)
INSERT INTO `review_reports` (`report_id`, `review_id`, `reporter_id`, `reason`, `status`, `created_at`) VALUES
	(1, 2, 6, 'Bình luận có nội dung tiêu cực không đúng sự thật về chất lượng sự kiện.', 'pending', '2025-12-04 15:16:57'),
	(2, 4, 5, 'Đánh giá spam, không có nội dung thực chất về sự kiện.', 'reviewed', '2025-12-04 15:16:57'),
	(3, 5, 18, 'Review có nội dung thiếu tích cực và không khách quan về chất lượng event.', 'pending', '2025-12-04 15:16:57');

-- Dumping structure for table events_web.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` enum('admin','organizer','attendee') NOT NULL,
  `description` text,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.roles: ~3 rows (approximately)
INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
	(1, 'admin', 'Quản trị viên hệ thống với quyền truy cập đầy đủ'),
	(2, 'organizer', 'Người tổ chức sự kiện có thể tạo và quản lý các sự kiện'),
	(3, 'attendee', 'Người dùng thông thường có thể tham gia các sự kiện');

-- Dumping structure for table events_web.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.sessions: ~2 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('31Pa1w1UBDaveimUJYh04kLKxcLccmBVGuVTLnOq', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY05XTk9IckxoYkIyb2pXUnhuMnJKeXdiWUI0N0x0dUtNdjU0OVNROCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1765043325),
	('wTfDmdlg5wi6pnzSgArusvRE4BvRKS3GcAaxKO05', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiYTVSa1JFTHVESW5mUmxYYTJsb1VHSG02cDFPb0JOa0xBRGpUdmlEayI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVmdW5kcyI7czo1OiJyb3V0ZSI7czoxOToiYWRtaW4ucmVmdW5kcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1765095826);

-- Dumping structure for table events_web.system_reports
CREATE TABLE IF NOT EXISTS `system_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `generated_by` int unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `report_type` enum('daily','weekly','monthly','custom') DEFAULT 'custom',
  `created_at` datetime DEFAULT (now()),
  PRIMARY KEY (`report_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `system_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.system_reports: ~5 rows (approximately)
INSERT INTO `system_reports` (`report_id`, `generated_by`, `title`, `content`, `report_type`, `created_at`) VALUES
	(1, 1, 'Báo cáo doanh thu tháng 11/2025', '{"total_revenue": 15500000, "total_tickets": 45, "popular_category": "Công nghệ"}', 'monthly', '2025-12-04 15:16:57'),
	(2, 1, 'Thống kê người dùng tuần 46', '{"new_users": 25, "active_events": 8, "completion_rate": 89.5}', 'weekly', '2025-12-04 15:16:57'),
	(3, 1, 'Báo cáo hoạt động tuần 47/2025', '{"period": "2025-11-11 to 2025-11-17", "stats": {"new_users": 10, "new_events": 6, "total_tickets_sold": 25, "revenue": 28540000, "popular_category": "Công nghệ", "completion_rate": 94.2}}', 'weekly', '2025-12-04 15:16:57'),
	(4, 1, 'Phân tích xu hướng người dùng tháng 11', '{"month": "2025-11", "user_behavior": {"most_active_time": "19:00-21:00", "popular_events": [1,2,4], "favorite_categories": ["Công nghệ", "Giải trí", "Kinh doanh"], "conversion_rate": 87.5, "return_rate": 65.2}}', 'monthly', '2025-12-04 15:16:57'),
	(5, 1, 'Báo cáo sự kiện hot nhất quý 4/2025', '{"quarter": "Q4-2025", "top_events": [{"event_id": 1, "tickets_sold": 425, "revenue": 5450000}, {"event_id": 4, "tickets_sold": 1230, "revenue": 4850000}], "trending_categories": ["AI/Tech", "Entertainment"], "growth_rate": "+23.5%"}', 'custom', '2025-12-04 15:16:57');

-- Dumping structure for table events_web.tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `ticket_type_id` int NOT NULL,
  `attendee_id` int unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `purchase_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('pending','paid','cancelled','used') DEFAULT 'pending',
  `coupon_id` int DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `qr_code` (`qr_code`),
  KEY `ticket_type_id` (`ticket_type_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `idx_tickets_attendee` (`attendee_id`),
  KEY `idx_tickets_payment_status` (`payment_status`),
  KEY `idx_tickets_purchase_time` (`purchase_time`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`ticket_type_id`),
  CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`attendee_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`),
  CONSTRAINT `chk_checked_in_at` CHECK (((`checked_in_at` is null) or (`checked_in_at` >= `purchase_time`))),
  CONSTRAINT `chk_payment_status` CHECK ((`payment_status` in (_utf8mb4'pending',_utf8mb4'paid',_utf8mb4'cancelled',_utf8mb4'used'))),
  CONSTRAINT `chk_quantity` CHECK ((`quantity` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.tickets: ~33 rows (approximately)
INSERT INTO `tickets` (`ticket_id`, `ticket_type_id`, `attendee_id`, `quantity`, `purchase_time`, `payment_status`, `coupon_id`, `qr_code`, `checked_in_at`) VALUES
	(1, 1, 4, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR001AIVN2025STU004', NULL),
	(2, 2, 5, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR002AIVN2025REG005', NULL),
	(3, 3, 6, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR003AIVN2025VIP006', NULL),
	(4, 4, 8, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR004STVN2025STP008', NULL),
	(5, 5, 10, 1, '2025-12-04 15:16:57', 'pending', NULL, 'QR005STVN2025INV010', NULL),
	(6, 7, 4, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR007LRVN2025EAR004', NULL),
	(7, 9, 5, 2, '2025-12-04 15:16:57', 'paid', NULL, 'QR009MUVN2025REG005', NULL),
	(8, 10, 6, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR010MUVN2025VIP006', NULL),
	(9, 12, 8, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR012PTVN2025STU008', NULL),
	(10, 13, 12, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR013FSVN2026EAR012', NULL),
	(11, 14, 14, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR014FSVN2026REG014', NULL),
	(13, 16, 16, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR016UIVN2026DES016', NULL),
	(14, 18, 17, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR018STVN2026AUD017', NULL),
	(15, 19, 18, 1, '2025-12-04 15:16:57', 'pending', NULL, 'QR019STVN2026INV018', NULL),
	(16, 21, 20, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR021WLVN2026SIN020', NULL),
	(17, 23, 12, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR023MRVN2026_5K012', NULL),
	(18, 24, 14, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR024MRVN202610K014', NULL),
	(19, 25, 16, 1, '2025-12-04 15:16:57', 'paid', NULL, 'QR025MRVN202621K016', NULL),
	(20, 3, 2, 1, '2025-12-04 18:37:49', 'paid', NULL, 'TICKET_20_6931728d4f9bb', NULL),
	(21, 3, 21, 1, '2025-12-04 19:36:10', 'cancelled', NULL, 'TICKET_21_6931803a07065', NULL),
	(22, 3, 21, 1, '2025-12-04 19:37:51', 'cancelled', NULL, 'TICKET_22_6931809f2c4d9', NULL),
	(23, 2, 21, 1, '2025-12-04 19:44:13', 'paid', NULL, 'TICKET_23_6931821d22cf6', NULL),
	(24, 6, 21, 1, '2025-12-04 19:45:34', 'paid', NULL, 'TICKET_24_6931826e571ac', NULL),
	(25, 1, 21, 1, '2025-12-04 20:16:19', 'cancelled', NULL, 'TICKET_25_693189a3c871e', NULL),
	(26, 3, 21, 1, '2025-12-04 20:17:11', 'cancelled', NULL, 'TICKET_26_693189d7a5b28', NULL),
	(27, 1, 21, 1, '2025-12-04 20:19:10', 'cancelled', NULL, 'TICKET_27_69318a4eab92c', NULL),
	(28, 3, 21, 1, '2025-12-04 20:19:20', 'cancelled', NULL, 'TICKET_28_69318a585b8d9', NULL),
	(29, 1, 21, 1, '2025-12-04 20:20:11', 'cancelled', NULL, 'TICKET_29_69318a8b1b648', NULL),
	(30, 27, 2, 1, '2025-12-04 20:23:56', 'cancelled', NULL, 'TICKET_30_69318b6ca6521', NULL),
	(31, 27, 2, 1, '2025-12-04 20:29:11', 'cancelled', NULL, 'TICKET_31_69318ca7a3a63', NULL),
	(32, 27, 2, 1, '2025-12-04 20:31:54', 'cancelled', NULL, 'TICKET_32_69318d4ac600b', NULL),
	(33, 27, 2, 1, '2025-12-04 20:31:57', 'paid', NULL, 'TICKET_33_69318d4d45557', NULL),
	(34, 27, 1, 1, '2025-12-04 20:44:37', 'paid', NULL, 'TICKET_34_693190454b7af', NULL);

-- Dumping structure for table events_web.ticket_types
CREATE TABLE IF NOT EXISTS `ticket_types` (
  `ticket_type_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_quantity` int NOT NULL DEFAULT '0',
  `remaining_quantity` int NOT NULL DEFAULT '0',
  `sale_start_time` datetime DEFAULT NULL,
  `sale_end_time` datetime DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ticket_type_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `ticket_types_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `chk_ticket_price` CHECK ((`price` >= 0)),
  CONSTRAINT `chk_ticket_quantities` CHECK (((`remaining_quantity` <= `total_quantity`) and (`remaining_quantity` >= 0))),
  CONSTRAINT `chk_ticket_sale_time` CHECK (((`sale_end_time` is null) or (`sale_start_time` is null) or (`sale_end_time` > `sale_start_time`)))
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.ticket_types: ~27 rows (approximately)
INSERT INTO `ticket_types` (`ticket_type_id`, `event_id`, `name`, `price`, `total_quantity`, `remaining_quantity`, `sale_start_time`, `sale_end_time`, `description`, `is_active`) VALUES
	(1, 1, 'Vé Sinh viên', 150000.00, 200, 180, '2025-11-15 00:00:00', '2025-12-14 23:59:00', 'Dành cho sinh viên có thẻ học sinh, sinh viên. Bao gồm tài liệu và ăn trưa.', 1),
	(2, 1, 'Vé Thường', 300000.00, 250, 219, '2025-11-15 00:00:00', '2025-12-14 23:59:00', 'Vé tham dự tiêu chuẩn. Bao gồm tài liệu, ăn trưa và coffee break.', 1),
	(3, 1, 'Vé VIP', 500000.00, 50, 44, '2025-11-15 00:00:00', '2025-12-14 23:59:00', 'Chỗ ngồi hàng đầu, gặp gỡ diễn giả, quà tặng đặc biệt.', 1),
	(4, 2, 'Vé Startup', 200000.00, 300, 250, '2025-11-20 00:00:00', '2025-12-19 23:59:59', 'Dành cho founder và nhân viên startup. Bao gồm networking lunch.', 1),
	(5, 2, 'Vé Investor', 800000.00, 100, 85, '2025-11-20 00:00:00', '2025-12-19 23:59:59', 'Dành cho nhà đầu tư và VC. Gặp gỡ riêng với các startup tiềm năng.', 1),
	(6, 2, 'Vé Thường', 400000.00, 400, 319, '2025-11-20 00:00:00', '2025-12-19 23:59:59', 'Vé tham dự tiêu chuẩn cho tất cả mọi người.', 1),
	(7, 3, 'Vé Early Bird', 800000.00, 30, 15, '2025-11-25 00:00:00', '2025-12-01 23:59:59', 'Giá ưu đãi sớm. Bao gồm tài liệu, laptop thuê và certificate.', 1),
	(8, 3, 'Vé Thường', 1200000.00, 70, 60, '2025-11-25 00:00:00', '2025-12-21 23:59:59', 'Vé tiêu chuẩn 2 ngày workshop đầy đủ.', 1),
	(9, 4, 'Vé Thường', 250000.00, 1000, 800, '2025-12-01 00:00:00', '2025-12-25 18:00:00', 'Chỗ ngồi khu vực thường. Bao gồm 1 thức uống.', 1),
	(10, 4, 'Vé VIP', 500000.00, 300, 250, '2025-12-01 00:00:00', '2025-12-25 18:00:00', 'Chỗ ngồi ưu tiên, buffet và meet & greet với ca sĩ.', 1),
	(11, 4, 'Vé VVIP', 1000000.00, 200, 180, '2025-12-01 00:00:00', '2025-12-25 18:00:00', 'Bàn riêng, champagne và photo với ca sĩ.', 1),
	(12, 5, 'Vé Sinh viên', 1500000.00, 30, 25, '2025-12-05 00:00:00', '2025-12-27 23:59:59', 'Ưu đãi đặc biệt cho sinh viên. Bao gồm tài liệu và certificate.', 1),
	(13, 5, 'Vé Thường', 2500000.00, 50, 40, '2025-12-05 00:00:00', '2025-12-27 23:59:59', 'Khóa học đầy đủ 3 ngày với chuyên gia hàng đầu.', 1),
	(14, 9, 'Vé Early Bird', 5000000.00, 20, 15, '2025-12-01 00:00:00', '2025-12-31 23:59:59', 'Giá ưu đãi sớm. Bao gồm tài liệu, laptop thuê, 1-on-1 mentorship và certificate.', 1),
	(15, 9, 'Vé Thường', 7000000.00, 30, 25, '2025-12-01 00:00:00', '2026-01-19 23:59:59', 'Vé tiêu chuẩn bootcamp 7 ngày đầy đủ.', 1),
	(16, 10, 'Vé Sinh viên', 800000.00, 30, 25, '2025-12-15 00:00:00', '2026-01-31 23:59:59', 'Ưu đãi cho sinh viên design và IT. Bao gồm software license 1 tháng.', 1),
	(17, 10, 'Vé Designer', 1200000.00, 35, 30, '2025-12-15 00:00:00', '2026-01-31 23:59:59', 'Dành cho designer đang làm việc. Bao gồm template và asset pack.', 1),
	(18, 10, 'Vé Team (5 người)', 4500000.00, 15, 12, '2025-12-15 00:00:00', '2026-01-31 23:59:59', 'Ưu đãi cho team. Giá bao gồm 5 người, team building và group project.', 1),
	(19, 11, 'Vé Khán giả', 200000.00, 1500, 1200, '2026-01-01 00:00:00', '2026-02-14 16:00:00', 'Chỗ ngồi khu vực thường, xem show và networking.', 1),
	(20, 11, 'Vé Investor', 1000000.00, 300, 250, '2026-01-01 00:00:00', '2026-02-14 16:00:00', 'Chỗ ngồi VIP, gặp riêng startup, buffet cao cấp.', 1),
	(21, 11, 'Vé Sponsor', 2000000.00, 200, 180, '2026-01-01 00:00:00', '2026-02-14 16:00:00', 'Chỗ ngồi VVIP, branding exposure, meet & greet với judges.', 1),
	(22, 13, 'Vé Single Room', 3500000.00, 50, 40, '2026-01-15 00:00:00', '2026-03-14 23:59:59', 'Phòng đơn 3 ngày 2 đêm, full-board, tất cả activities.', 1),
	(23, 13, 'Vé Twin Share', 2800000.00, 50, 45, '2026-01-15 00:00:00', '2026-03-14 23:59:59', 'Phòng đôi chia sẻ, phù hợp cho bạn bè hoặc cặp đôi.', 1),
	(24, 14, 'Vé 5km', 150000.00, 2000, 1800, '2026-02-01 00:00:00', '2026-03-21 23:59:59', 'Cự ly 5km, phù hợp mọi lứa tuổi. Bao gồm medal và BIB.', 1),
	(25, 14, 'Vé 10km', 250000.00, 1500, 1300, '2026-02-01 00:00:00', '2026-03-21 23:59:59', 'Cự ly 10km cho runner trung bình. Medal, BIB và áo kỷ niệm.', 1),
	(26, 14, 'Vé 21km', 400000.00, 1500, 1200, '2026-02-01 00:00:00', '2026-03-21 23:59:59', 'Half Marathon, thử thách cho runner giàu kinh nghiệm.', 1),
	(27, 1, 'Vé siêu ưu đãi', 10000.00, 10, 8, '2025-12-04 20:23:00', '2025-12-15 20:23:00', NULL, 1);

-- Dumping structure for table events_web.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT (now()),
  `updated_at` datetime DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  CONSTRAINT `chk_email_format` CHECK (regexp_like(`email`,_utf8mb4'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]{2,}$')),
  CONSTRAINT `chk_full_name_length` CHECK ((length(`full_name`) >= 2))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table events_web.users: ~20 rows (approximately)
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `avatar_url`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Nguyễn Văn An', 'admin@eventsvn.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0901234567', NULL, '2025-11-01 10:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(2, 'Trần Thị Bình', 'organizer1@eventsvn.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0912345678', NULL, '2025-11-01 11:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(3, 'Lê Minh Cường', 'organizer2@eventsvn.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0923456789', NULL, '2025-11-01 12:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(4, 'Phạm Thị Dung', 'user1@gmail.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0934567890', NULL, '2025-11-02 09:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(5, 'Hoàng Văn Em', 'user2@gmail.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0945678901', NULL, '2025-11-02 10:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(6, 'Võ Thị Phượng', 'user3@yahoo.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0956789012', NULL, '2025-11-02 11:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(7, 'Đặng Minh Giáp', 'organizer3@techvn.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0967890123', NULL, '2025-11-03 08:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(8, 'Bùi Thị Hoa', 'user4@hotmail.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0978901234', NULL, '2025-11-03 09:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(9, 'Ngô Văn Ích', 'organizer4@business.vn', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0989012345', NULL, '2025-11-03 10:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(10, 'Lý Thị Kim', 'user5@outlook.com', '$2y$12$unR6PK14lKw2tb/3186R..4G50KkgldguTz7ggFvPryKdu3PRAGdG', '0990123456', NULL, '2025-11-03 11:00:00', NULL, '2025-12-04 15:16:57', '2025-12-04 15:16:57'),
	(11, 'Nguyễn Thị Lan', 'lan.nguyen@techcorp.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0901111111', NULL, '2025-11-04 10:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:28'),
	(12, 'Trần Văn Minh', 'minh.tran@startup.io', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0912222222', NULL, '2025-11-04 11:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:28'),
	(13, 'Lê Thị Hương', 'huong.le@design.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0923333333', NULL, '2025-11-04 12:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:29'),
	(14, 'Phạm Văn Đức', 'duc.pham@university.edu.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0934444444', NULL, '2025-11-04 13:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:29'),
	(16, 'Hoàng Văn Nam', 'nam.hoang@bank.com.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0956666666', NULL, '2025-11-04 15:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:29'),
	(17, 'Đặng Thị Thu', 'thu.dang@media.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0967777777', NULL, '2025-11-04 16:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:30'),
	(18, 'Bùi Văn Toàn', 'toan.bui@logistics.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0978888888', NULL, '2025-11-05 08:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:31'),
	(19, 'Ngô Thị Yến', 'yen.ngo@restaurant.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0989999999', NULL, '2025-11-05 09:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:30'),
	(20, 'Lý Văn Hải', 'hai.ly@construction.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0990000000', NULL, '2025-11-05 10:00:00', NULL, '2025-12-04 15:16:57', '2025-12-08 17:41:31'),
	(21, 'Register', 'user6@gmail.com', '$2y$12$rDt5CiBjaLM1j1VkY6QPxOcRnTV4Qwb0CAml4susBKZOe71GB57Cm', NULL, NULL, '2025-12-04 18:39:40', NULL, '2025-12-04 18:39:25', '2025-12-16 05:14:47');

-- Dumping structure for table events_web.user_roles
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` int unsigned NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` datetime DEFAULT (now()),
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bảng many-to-many: User có thể có nhiều vai trò (admin, organizer, attendee)';

-- Dumping data for table events_web.user_roles: ~20 rows (approximately)
INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
	(1, 1, '2025-12-04 15:16:57'),
	(2, 2, '2025-12-04 15:16:57'),
	(3, 2, '2025-12-04 15:16:57'),
	(4, 3, '2025-12-04 15:16:57'),
	(5, 3, '2025-12-04 15:16:57'),
	(6, 3, '2025-12-04 15:16:57'),
	(7, 2, '2025-12-04 15:16:57'),
	(8, 3, '2025-12-04 15:16:57'),
	(9, 2, '2025-12-04 15:16:57'),
	(10, 3, '2025-12-04 15:16:57'),
	(11, 2, '2025-12-04 15:16:57'),
	(12, 3, '2025-12-04 15:16:57'),
	(13, 2, '2025-12-04 15:16:57'),
	(14, 3, '2025-12-04 15:16:57'),
	(16, 3, '2025-12-04 15:16:57'),
	(17, 3, '2025-12-04 15:16:57'),
	(18, 3, '2025-12-04 15:16:57'),
	(19, 2, '2025-12-04 15:16:57'),
	(20, 3, '2025-12-04 15:16:57'),
	(21, 3, '2025-12-04 18:39:25');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
