-- ================================================================
-- EVENTS MANAGEMENT SYSTEM DATABASE SCHEMA
-- Version: 2.0
-- Description: Complete event management system with user roles,
--              events, tickets, payments, reviews, and admin features
-- ================================================================

-- Tắt foreign key checks để tránh lỗi constraint khi import
SET FOREIGN_KEY_CHECKS = 0;

-- ================================================================
-- USER MANAGEMENT TABLES
-- ================================================================

-- Bảng người dùng chính - lưu thông tin cơ bản của user
CREATE TABLE `users` (
  `user_id` int unsigned PRIMARY KEY AUTO_INCREMENT, -- ID duy nhất của user
  `full_name` varchar(100) NOT NULL,                 -- Họ tên đầy đủ
  `email` varchar(255) UNIQUE NOT NULL,              -- Email (unique, dùng để đăng nhập)
  `password_hash` varchar(255) NOT NULL,             -- Mật khẩu đã hash
  `phone` varchar(20),                               -- Số điện thoại (optional)
  `avatar_url` varchar(255),                         -- Link ảnh đại diện
  `email_verified_at` datetime NULL,                 -- Thời gian xác thực email
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian tạo tài khoản
  `updated_at` datetime DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP, -- Cập nhật cuối
  CONSTRAINT chk_email_format CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'), -- Validate email format
  CONSTRAINT chk_full_name_length CHECK (LENGTH(full_name) >= 2) -- Tên phải ít nhất 2 ký tự
);

-- Bảng vai trò - định nghĩa các quyền hạn trong hệ thống
CREATE TABLE `roles` (
  `role_id` int PRIMARY KEY AUTO_INCREMENT,          -- ID vai trò
  `role_name` ENUM('admin','organizer','attendee') NOT NULL UNIQUE, -- Tên vai trò (admin/organizer/attendee)
  `description` text                                 -- Mô tả chi tiết về vai trò
);

-- Bảng liên kết user-role (Many-to-Many) - một user có thể có nhiều vai trò
CREATE TABLE `user_roles` (
  `user_id` int unsigned NOT NULL,                   -- ID người dùng
  `role_id` int NOT NULL,                           -- ID vai trò
  `assigned_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian gán vai trò
  PRIMARY KEY (`user_id`, `role_id`)                -- Composite key để tránh trùng lặp
);

-- ================================================================
-- LARAVEL SYSTEM TABLES (Sessions, Cache, Jobs)
-- ================================================================

-- Bảng sessions - quản lý phiên đăng nhập của user (Laravel Sessions)
CREATE TABLE `sessions` (
  `id` varchar(255) PRIMARY KEY,                    -- Session ID duy nhất
  `user_id` int unsigned,                           -- ID user (null nếu guest)
  `ip_address` varchar(45),                         -- IP address của user
  `user_agent` text,                                -- Browser/device info
  `payload` longtext NOT NULL,                      -- Dữ liệu session được mã hóa
  `last_activity` int NOT NULL,                     -- Thời gian hoạt động cuối (Unix timestamp)
  INDEX `sessions_user_id_index` (`user_id`),       -- Index cho tìm kiếm theo user
  INDEX `sessions_last_activity_index` (`last_activity`) -- Index cho cleanup expired sessions
);

-- Bảng cache - lưu trữ cache data để tăng performance
CREATE TABLE `cache` (
  `key` varchar(255) PRIMARY KEY,                   -- Cache key duy nhất
  `value` mediumtext NOT NULL,                      -- Dữ liệu cache đã serialize
  `expiration` int NOT NULL                         -- Thời gian hết hạn (Unix timestamp)
);

-- Bảng cache_locks - ngăn chặn cache stampede, đảm bảo atomic operations
CREATE TABLE `cache_locks` (
  `key` varchar(255) PRIMARY KEY,                   -- Lock key
  `owner` varchar(255) NOT NULL,                    -- Process/thread sở hữu lock
  `expiration` int NOT NULL                         -- Thời gian hết hạn lock
);

-- Bảng jobs - queue các công việc chạy background
CREATE TABLE `jobs` (
  `id` bigint unsigned PRIMARY KEY AUTO_INCREMENT,  -- Job ID
  `queue` varchar(255) NOT NULL,                    -- Tên queue (default, emails, etc.)
  `payload` longtext NOT NULL,                      -- Dữ liệu job (class, method, params)
  `attempts` tinyint unsigned NOT NULL,             -- Số lần đã thử chạy
  `reserved_at` int unsigned NULL,                  -- Thời gian job được worker lấy
  `available_at` int unsigned NOT NULL,             -- Thời gian job có thể chạy
  `created_at` int unsigned NOT NULL,               -- Thời gian tạo job
  INDEX `jobs_queue_index` (`queue`)                -- Index để worker lấy job theo queue
);

-- Bảng job_batches - quản lý batch jobs (nhóm jobs liên quan)
CREATE TABLE `job_batches` (
  `id` varchar(255) PRIMARY KEY,                    -- Batch ID
  `name` varchar(255) NOT NULL,                     -- Tên batch
  `total_jobs` int NOT NULL,                        -- Tổng số jobs trong batch
  `pending_jobs` int NOT NULL,                      -- Số jobs chờ xử lý
  `failed_jobs` int NOT NULL,                       -- Số jobs thất bại
  `failed_job_ids` longtext NOT NULL,               -- Danh sách job IDs thất bại
  `options` mediumtext NULL,                        -- Tùy chọn batch
  `cancelled_at` int NULL,                          -- Thời gian hủy batch
  `created_at` int NOT NULL,                        -- Thời gian tạo batch
  `finished_at` int NULL                            -- Thời gian hoàn thành batch
);

-- Bảng failed_jobs - lưu các jobs thất bại để debug và retry
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned PRIMARY KEY AUTO_INCREMENT,  -- Failed job ID
  `uuid` varchar(255) UNIQUE NOT NULL,              -- UUID duy nhất
  `connection` text NOT NULL,                       -- Connection name
  `queue` text NOT NULL,                            -- Queue name
  `payload` longtext NOT NULL,                      -- Job data
  `exception` longtext NOT NULL,                    -- Exception details
  `failed_at` timestamp DEFAULT CURRENT_TIMESTAMP   -- Thời gian thất bại
);

-- ================================================================
-- EVENT MANAGEMENT TABLES
-- ================================================================

-- Bảng danh mục sự kiện - phân loại events theo chủ đề
CREATE TABLE `event_categories` (
  `category_id` int PRIMARY KEY AUTO_INCREMENT,     -- ID danh mục
  `category_name` varchar(100) NOT NULL UNIQUE,     -- Tên danh mục (Technology, Business, etc.)
  `description` text                                -- Mô tả chi tiết danh mục
);

-- Bảng địa điểm tổ chức - quản lý các venue cho events
CREATE TABLE `event_locations` (
  `location_id` int PRIMARY KEY AUTO_INCREMENT,     -- ID địa điểm
  `name` varchar(150) NOT NULL,                     -- Tên địa điểm
  `address` varchar(255) NOT NULL,                  -- Địa chỉ chi tiết
  `city` varchar(100) NOT NULL,                     -- Thành phố
  `capacity` int DEFAULT 0                          -- Sức chứa tối đa
);

-- Bảng sự kiện chính - lưu thông tin chi tiết của mỗi event
CREATE TABLE `events` (
  `event_id` int PRIMARY KEY AUTO_INCREMENT,        -- ID sự kiện
  `organizer_id` int unsigned NOT NULL,             -- ID người tổ chức (FK to users)
  `category_id` int NOT NULL,                       -- ID danh mục (FK to event_categories)
  `location_id` int NOT NULL,                       -- ID địa điểm (FK to event_locations)
  `title` varchar(200) NOT NULL,                    -- Tiêu đề sự kiện
  `description` text,                               -- Mô tả chi tiết sự kiện
  `start_time` datetime NOT NULL,                   -- Thời gian bắt đầu
  `end_time` datetime NOT NULL,                     -- Thời gian kết thúc
  `banner_url` varchar(255),                        -- Link ảnh banner
  `status` ENUM('upcoming','ongoing','ended','cancelled') DEFAULT 'upcoming', -- Trạng thái event
  `max_attendees` int DEFAULT NULL,                 -- Giới hạn số người tham gia (NULL = không giới hạn)
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian tạo event
  `updated_at` datetime DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP, -- Cập nhật cuối
  `deleted_at` datetime NULL,                       -- Soft delete - thời gian xóa
  `approved` boolean DEFAULT false,                 -- Trạng thái duyệt (cần admin approve)
  `approved_at` datetime NULL,                      -- Thời gian được duyệt
  `approved_by` int unsigned NULL,                  -- ID admin đã duyệt (FK to users)
  CONSTRAINT chk_event_time CHECK (end_time > start_time), -- Đảm bảo end_time > start_time
  CONSTRAINT chk_max_attendees CHECK (max_attendees IS NULL OR max_attendees > 0) -- Max attendees > 0
);

-- Bảng bản đồ sự kiện - lưu sơ đồ mặt bằng, layout venue
CREATE TABLE `event_maps` (
  `map_id` int PRIMARY KEY AUTO_INCREMENT,          -- ID bản đồ
  `event_id` int NOT NULL,                          -- ID sự kiện (FK to events)
  `map_image_url` varchar(255) NOT NULL,            -- Link ảnh bản đồ/sơ đồ
  `note` text                                       -- Ghi chú về bản đồ
);

-- Bảng tags - từ khóa để gắn thẻ cho events (tăng khả năng tìm kiếm)
CREATE TABLE `event_tags` (
  `tag_id` int PRIMARY KEY AUTO_INCREMENT,          -- ID tag
  `tag_name` varchar(100) NOT NULL UNIQUE           -- Tên tag (Conference, Workshop, etc.)
);

-- Bảng liên kết event-tag (Many-to-Many) - một event có thể có nhiều tags
CREATE TABLE `event_tag_map` (
  `event_id` int NOT NULL,                          -- ID sự kiện (FK to events)
  `tag_id` int NOT NULL,                            -- ID tag (FK to event_tags)
  PRIMARY KEY (`event_id`, `tag_id`)                -- Composite key tránh trùng lặp
);

-- ================================================================
-- TICKET MANAGEMENT TABLES
-- ================================================================

-- Bảng loại vé - định nghĩa các hạng vé khác nhau cho mỗi event
CREATE TABLE `ticket_types` (
  `ticket_type_id` int PRIMARY KEY AUTO_INCREMENT,  -- ID loại vé
  `event_id` int NOT NULL,                          -- ID sự kiện (FK to events)
  `name` varchar(100) NOT NULL,                     -- Tên loại vé (VIP, Regular, Student, etc.)
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,      -- Giá vé (VND)
  `total_quantity` int NOT NULL DEFAULT 0,          -- Tổng số vé phát hành
  `remaining_quantity` int NOT NULL DEFAULT 0,      -- Số vé còn lại
  `sale_start_time` datetime NULL,                  -- Thời gian bắt đầu bán (NULL = ngay lập tức)
  `sale_end_time` datetime NULL,                    -- Thời gian ngừng bán (NULL = đến khi hết)
  `description` text,                               -- Mô tả quyền lợi của loại vé
  `is_active` boolean DEFAULT true,                 -- Trạng thái kích hoạt loại vé
  CONSTRAINT chk_ticket_price CHECK (price >= 0),   -- Giá vé >= 0
  CONSTRAINT chk_ticket_quantities CHECK (remaining_quantity <= total_quantity AND remaining_quantity >= 0), -- Logic số lượng
  CONSTRAINT chk_ticket_sale_time CHECK (sale_end_time IS NULL OR sale_start_time IS NULL OR sale_end_time > sale_start_time) -- Logic thời gian bán
);

-- Bảng vé đã mua - lưu thông tin từng vé được mua bởi user
CREATE TABLE `tickets` (
  `ticket_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID vé
  `ticket_type_id` int NOT NULL,                    -- ID loại vé (FK to ticket_types)
  `attendee_id` int unsigned NOT NULL,              -- ID người mua (FK to users)
  `purchase_time` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian mua vé
  `payment_status` ENUM('pending','paid','cancelled') DEFAULT 'pending', -- Trạng thái thanh toán
  `coupon_id` int,                                  -- ID coupon được sử dụng (FK to coupons, nullable)
  `qr_code` varchar(255) UNIQUE                     -- Mã QR để check-in tại sự kiện
);

-- Bảng mã giảm giá - quản lý các coupon để giảm giá vé
CREATE TABLE `coupons` (
  `coupon_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID coupon
  `code` varchar(50) UNIQUE NOT NULL,               -- Mã coupon (EARLYBIRD, STUDENT50, etc.)
  `discount_percent` int NOT NULL,                  -- Phần trăm giảm giá (0-100%)
  `max_uses` int NOT NULL DEFAULT 1,                -- Số lần sử dụng tối đa
  `used_count` int DEFAULT 0,                       -- Số lần đã được sử dụng
  `valid_from` datetime NOT NULL,                   -- Thời gian bắt đầu có hiệu lực
  `valid_to` datetime NOT NULL,                     -- Thời gian hết hạn
  `status` ENUM('active','expired','disabled') DEFAULT 'active', -- Trạng thái coupon
  CONSTRAINT chk_discount_percent CHECK (discount_percent >= 0 AND discount_percent <= 100), -- Giảm giá 0-100%
  CONSTRAINT chk_coupon_dates CHECK (valid_to > valid_from), -- Logic thời gian
  CONSTRAINT chk_coupon_uses CHECK (used_count <= max_uses AND used_count >= 0) -- Logic số lần sử dụng
);

-- ================================================================
-- PAYMENT MANAGEMENT TABLES
-- ================================================================

-- Bảng phương thức thanh toán - các cách user có thể thanh toán
CREATE TABLE `payment_methods` (
  `method_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID phương thức
  `name` varchar(50) NOT NULL UNIQUE,               -- Tên phương thức (Credit Card, PayPal, etc.)
  `description` text                                -- Mô tả chi tiết phương thức
);

-- Bảng thanh toán - lưu thông tin các giao dịch thanh toán
CREATE TABLE `payments` (
  `payment_id` int PRIMARY KEY AUTO_INCREMENT,      -- ID giao dịch
  `ticket_id` int NOT NULL,                         -- ID vé được thanh toán (FK to tickets)
  `method_id` int NOT NULL,                         -- ID phương thức thanh toán (FK to payment_methods)
  `amount` decimal(10,2) NOT NULL,                  -- Số tiền thanh toán (VND)
  `status` ENUM('success','failed','refunded') DEFAULT 'success', -- Trạng thái giao dịch
  `transaction_id` varchar(100) UNIQUE,             -- Mã giao dịch từ payment gateway
  `paid_at` datetime DEFAULT (CURRENT_TIMESTAMP),   -- Thời gian thanh toán
  CONSTRAINT chk_payment_amount CHECK (amount >= 0) -- Số tiền >= 0
);

-- Bảng hoàn tiền - quản lý các yêu cầu hoàn tiền
CREATE TABLE `refunds` (
  `refund_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID yêu cầu hoàn tiền
  `payment_id` int NOT NULL,                        -- ID giao dịch cần hoàn (FK to payments)
  `requester_id` int unsigned NOT NULL,             -- ID người yêu cầu hoàn tiền (FK to users)
  `reason` text,                                    -- Lý do hoàn tiền
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending', -- Trạng thái xử lý
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian yêu cầu
  `processed_at` datetime                           -- Thời gian xử lý xong
);

-- ================================================================
-- REVIEW & INTERACTION TABLES
-- ================================================================

-- Bảng đánh giá sự kiện - user review và rating events sau khi tham gia
CREATE TABLE `reviews` (
  `review_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID review
  `event_id` int NOT NULL,                          -- ID sự kiện được review (FK to events)
  `user_id` int unsigned NOT NULL,                  -- ID người review (FK to users)
  `rating` int NOT NULL,                            -- Điểm đánh giá (1-5 sao)
  `comment` text,                                   -- Bình luận chi tiết
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian tạo review
  `updated_at` datetime DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP, -- Cập nhật cuối
  CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5), -- Rating từ 1-5
  UNIQUE KEY unique_user_event_review (`event_id`, `user_id`) -- Mỗi user chỉ review 1 lần/event
);

-- Bảng báo cáo review - user có thể report các review không phù hợp
CREATE TABLE `review_reports` (
  `report_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID báo cáo
  `review_id` int NOT NULL,                         -- ID review bị báo cáo (FK to reviews)
  `reporter_id` int unsigned NOT NULL,              -- ID người báo cáo (FK to users)
  `reason` text NOT NULL,                           -- Lý do báo cáo
  `status` ENUM('pending','reviewed','resolved') DEFAULT 'pending', -- Trạng thái xử lý
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP) -- Thời gian báo cáo
);

-- Bảng yêu thích - user có thể favorite các events quan tâm
CREATE TABLE `favorites` (
  `user_id` int unsigned NOT NULL,                  -- ID user (FK to users)
  `event_id` int NOT NULL,                          -- ID event được favorite (FK to events)
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian favorite
  PRIMARY KEY (`user_id`, `event_id`)               -- Composite key tránh trùng lặp
);

-- ================================================================
-- ADMIN & MONITORING TABLES
-- ================================================================

-- Bảng báo cáo sự cố - tracking các vấn đề xảy ra trong events
CREATE TABLE `incident_reports` (
  `incident_id` int PRIMARY KEY AUTO_INCREMENT,     -- ID sự cố
  `event_id` int NOT NULL,                          -- ID sự kiện xảy ra sự cố (FK to events)
  `reporter_id` int unsigned NOT NULL,              -- ID người báo cáo (FK to users)
  `description` text NOT NULL,                      -- Mô tả chi tiết sự cố
  `status` ENUM('open','in_progress','resolved','closed') DEFAULT 'open', -- Trạng thái xử lý
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP), -- Thời gian báo cáo
  `resolved_at` datetime,                           -- Thời gian giải quyết
  `updated_at` datetime DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP -- Cập nhật cuối
);

-- Bảng thông báo - hệ thống gửi notifications cho users
CREATE TABLE `notifications` (
  `notification_id` int PRIMARY KEY AUTO_INCREMENT, -- ID thông báo
  `user_id` int unsigned NOT NULL,                  -- ID user nhận thông báo (FK to users)
  `title` varchar(200),                             -- Tiêu đề thông báo
  `message` text NOT NULL,                          -- Nội dung thông báo
  `type` ENUM('info','warning','success','error') DEFAULT 'info', -- Loại thông báo
  `is_read` boolean DEFAULT false,                  -- Trạng thái đã đọc
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP) -- Thời gian tạo thông báo
);

-- Bảng log admin - tracking tất cả actions của admin để audit
CREATE TABLE `admin_logs` (
  `log_id` int PRIMARY KEY AUTO_INCREMENT,          -- ID log
  `admin_id` int unsigned NOT NULL,                 -- ID admin thực hiện action (FK to users)
  `action` varchar(255) NOT NULL,                   -- Mô tả action (create_event, approve_event, etc.)
  `target_table` varchar(100),                      -- Bảng bị tác động
  `target_id` int,                                  -- ID record bị tác động
  `old_values` JSON,                                -- Giá trị cũ (trước khi thay đổi)
  `new_values` JSON,                                -- Giá trị mới (sau khi thay đổi)
  `ip_address` varchar(45),                         -- IP address của admin
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP) -- Thời gian thực hiện action
);

-- Bảng báo cáo hệ thống - tự động generate reports về hoạt động
CREATE TABLE `system_reports` (
  `report_id` int PRIMARY KEY AUTO_INCREMENT,       -- ID báo cáo
  `generated_by` int unsigned NOT NULL,             -- ID user tạo báo cáo (FK to users)
  `title` varchar(150) NOT NULL,                    -- Tiêu đề báo cáo
  `content` text NOT NULL,                          -- Nội dung báo cáo (JSON hoặc HTML)
  `report_type` ENUM('daily','weekly','monthly','custom') DEFAULT 'custom', -- Loại báo cáo
  `created_at` datetime DEFAULT (CURRENT_TIMESTAMP) -- Thời gian tạo báo cáo
);

-- ================================================================
-- TABLE COMMENTS & RELATIONSHIPS
-- ================================================================

-- Bổ sung comments cho các bảng quan trọng
ALTER TABLE `user_roles` COMMENT = 'Bảng many-to-many: User có thể có nhiều vai trò (admin, organizer, attendee)';
ALTER TABLE `event_tag_map` COMMENT = 'Bảng many-to-many: Event có thể có nhiều tags để tăng khả năng tìm kiếm';
ALTER TABLE `favorites` COMMENT = 'Bảng many-to-many: User có thể favorite nhiều events quan tâm';

-- ================================================================
-- FOREIGN KEY CONSTRAINTS
-- ================================================================

ALTER TABLE `user_roles` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

ALTER TABLE `sessions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `events` ADD FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `events` ADD FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`category_id`);

ALTER TABLE `events` ADD FOREIGN KEY (`location_id`) REFERENCES `event_locations` (`location_id`);

ALTER TABLE `events` ADD FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `event_maps` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `event_tag_map` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `event_tag_map` ADD FOREIGN KEY (`tag_id`) REFERENCES `event_tags` (`tag_id`);

ALTER TABLE `ticket_types` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `tickets` ADD FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`ticket_type_id`);

ALTER TABLE `tickets` ADD FOREIGN KEY (`attendee_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `tickets` ADD FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`);

ALTER TABLE `refunds` ADD FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`);

ALTER TABLE `refunds` ADD FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `reviews` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `reviews` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `review_reports` ADD FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`);

ALTER TABLE `review_reports` ADD FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `favorites` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `favorites` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `incident_reports` ADD FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

ALTER TABLE `incident_reports` ADD FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `admin_logs` ADD FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `system_reports` ADD FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

-- ================================================================
-- PERFORMANCE INDEXES
-- Index là cấu trúc dữ liệu đặc biệt giúp tăng tốc độ truy vấn database
-- 
-- TẠI SAO CẦN INDEX?
-- - Tăng tốc độ SELECT queries từ hàng giây xuống milliseconds
-- - Giảm thời gian tìm kiếm từ O(n) xuống O(log n)
-- - Cải thiện performance cho WHERE, ORDER BY, JOIN
-- - Đặc biệt quan trọng với bảng có hàng triệu records
--
-- NHƯỢC ĐIỂM:
-- - Tốn thêm dung lượng lưu trữ (10-20% kích thước bảng)
-- - Làm chậm INSERT/UPDATE/DELETE vì phải cập nhật index
-- - Cần maintain và tối ưu định kỳ
--
-- NGUYÊN TẮC CHỌN INDEX:
-- 1. Các cột thường xuất hiện trong WHERE clause
-- 2. Các cột dùng để JOIN giữa các bảng
-- 3. Các cột dùng để ORDER BY
-- 4. Foreign key columns
-- 5. Unique columns để enforce constraints
-- ================================================================
-- USER INDEXES - Tăng tốc login và tìm kiếm user
CREATE INDEX idx_users_email ON users(email);                    -- Login: WHERE email = 'user@email.com'

-- EVENT INDEXES - Tăng tốc tìm kiếm và filter events
CREATE INDEX idx_events_organizer ON events(organizer_id);       -- JOIN: events.organizer_id = users.user_id
CREATE INDEX idx_events_category ON events(category_id);         -- Filter: WHERE category_id = 1
CREATE INDEX idx_events_location ON events(location_id);         -- Filter: WHERE location_id = 1
CREATE INDEX idx_events_status ON events(status);               -- Filter: WHERE status = 'upcoming'
CREATE INDEX idx_events_start_time ON events(start_time);       -- Sort: ORDER BY start_time
CREATE INDEX idx_events_approved ON events(approved);           -- Filter: WHERE approved = true
CREATE INDEX idx_events_title ON events(title);                 -- Search: WHERE title LIKE '%conference%'
CREATE INDEX idx_events_city ON event_locations(city);          -- Search: WHERE city = 'Ho Chi Minh City'

-- TICKET INDEXES - Tăng tốc quản lý vé và thanh toán
CREATE INDEX idx_tickets_attendee ON tickets(attendee_id);      -- Query: Vé của user nào?
CREATE INDEX idx_tickets_payment_status ON tickets(payment_status); -- Filter: Vé đã thanh toán chưa?
CREATE INDEX idx_tickets_purchase_time ON tickets(purchase_time); -- Sort: ORDER BY purchase_time DESC

-- PAYMENT INDEXES - Tăng tốc tra cứu giao dịch
CREATE INDEX idx_payments_status ON payments(status);           -- Filter: Giao dịch thành công/thất bại
CREATE INDEX idx_payments_transaction_id ON payments(transaction_id); -- Lookup: Tìm theo mã giao dịch

-- COUPON INDEXES - Tăng tốc áp dụng mã giảm giá
CREATE INDEX idx_coupons_code ON coupons(code);                 -- Lookup: WHERE code = 'EARLYBIRD'
CREATE INDEX idx_coupons_status ON coupons(status);             -- Filter: WHERE status = 'active'

-- NOTIFICATION INDEXES - Tăng tốc hiển thị thông báo cho user
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read); -- Query: Thông báo chưa đọc của user
CREATE INDEX idx_notifications_created ON notifications(created_at);         -- Sort: ORDER BY created_at DESC

-- REVIEW INDEXES - Tăng tốc hiển thị đánh giá
CREATE INDEX idx_reviews_event ON reviews(event_id);            -- Query: Reviews của event nào?
CREATE INDEX idx_reviews_rating ON reviews(rating);             -- Filter: WHERE rating >= 4

-- ADMIN INDEXES - Tăng tốc audit và monitoring
CREATE INDEX idx_admin_logs_admin_action ON admin_logs(admin_id, action); -- Query: Admin nào làm gì?

-- COMPOSITE INDEXES - Tối ưu cho queries phức tạp
-- Ví dụ: Tìm events upcoming ở Ho Chi Minh City
-- SELECT * FROM events e 
-- JOIN event_locations l ON e.location_id = l.location_id 
-- WHERE e.status = 'upcoming' AND l.city = 'Ho Chi Minh City'

-- ================================================================
-- SAMPLE DATA
-- Dữ liệu mẫu để test và demo hệ thống
-- ================================================================
INSERT INTO roles (role_name, description) VALUES 
('admin', 'Quản trị viên hệ thống với quyền truy cập đầy đủ'),
('organizer', 'Người tổ chức sự kiện có thể tạo và quản lý các sự kiện'),
('attendee', 'Người dùng thông thường có thể tham gia các sự kiện');

INSERT INTO event_categories (category_name, description) VALUES 
('Công nghệ', 'Hội thảo công nghệ, workshop và buổi gặp mặt kỹ thuật'),
('Kinh doanh', 'Hội thảo kinh doanh, sự kiện networking và hội thảo chuyên môn'),
('Giải trí', 'Buổi hòa nhạc, chương trình biểu diễn và sự kiện giải trí'),
('Thể thao', 'Sự kiện thể thao, giải đấu và các cuộc thi'),
('Giáo dục', 'Workshop giáo dục, khóa học và bài gi강'),
('Sức khỏe', 'Sự kiện chăm sóc sức khỏe, lớp tập thể dục'),
('Nghệ thuật', 'Triển lãm nghệ thuật, sự kiện văn hóa và workshop sáng tạo');

INSERT INTO payment_methods (name, description) VALUES 
('Thẻ tín dụng', 'Thanh toán qua thẻ tín dụng hoặc thẻ ghi nợ'),
('Chuyển khoản ngân hàng', 'Thanh toán chuyển khoản trực tiếp'),
('Tiền mặt', 'Thanh toán bằng tiền mặt tại địa điểm'),
('Ví điện tử', 'Thanh toán qua ứng dụng ví điện tử như MoMo, ZaloPay'),
('QR Code', 'Thanh toán quét mã QR ngân hàng');

-- Event locations sẽ được insert ở phần sau để tránh trùng lặp

-- INSERT SAMPLE TAGS
INSERT INTO event_tags (tag_name) VALUES 
('Hội thảo'), ('Workshop'), ('Kết nối'), ('Đào tạo'), 
('Hội nghị'), ('Triển lãm'), ('Thi đấu'), ('Lễ hội'),
('Webinar'), ('Gặp mặt'), ('Bootcamp'), ('Đỉnh cao');

-- INSERT SAMPLE COUPONS
INSERT INTO coupons (code, discount_percent, max_uses, valid_from, valid_to, status) VALUES 
('DANGKYSOOM', 20, 100, '2025-11-01 00:00:00', '2025-12-31 23:59:59', 'active'),
('SINHVIEN50', 50, 500, '2025-11-01 00:00:00', '2026-12-31 23:59:59', 'active'),
('CHAODON10', 10, 1000, '2025-11-01 00:00:00', '2026-06-30 23:59:59', 'active'),
('TETDUONG', 30, 200, '2025-11-01 00:00:00', '2026-02-28 23:59:59', 'active'),
('GIANGVIEN25', 25, 150, '2025-11-01 00:00:00', '2026-12-31 23:59:59', 'active'),
('KHOINGHIEP40', 40, 50, '2025-11-01 00:00:00', '2026-03-31 23:59:59', 'active');

-- ================================================================
-- DỮ LIỆU MẪU TIẾNG VIỆT
-- Tạo dữ liệu demo hoàn chỉnh cho hệ thống quản lý sự kiện
-- ================================================================

-- INSERT SAMPLE USERS (Người dùng mẫu)
INSERT INTO users (full_name, email, password_hash, phone, email_verified_at) VALUES 
('Nguyễn Văn An', 'admin@eventsvn.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0901234567', '2025-11-01 10:00:00'),
('Trần Thị Bình', 'organizer1@eventsvn.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0912345678', '2025-11-01 11:00:00'),
('Lê Minh Cường', 'organizer2@eventsvn.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0923456789', '2025-11-01 12:00:00'),
('Phạm Thị Dung', 'user1@gmail.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0934567890', '2025-11-02 09:00:00'),
('Hoàng Văn Em', 'user2@gmail.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0945678901', '2025-11-02 10:00:00'),
('Võ Thị Phượng', 'user3@yahoo.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0956789012', '2025-11-02 11:00:00'),
('Đặng Minh Giáp', 'organizer3@techvn.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0967890123', '2025-11-03 08:00:00'),
('Bùi Thị Hoa', 'user4@hotmail.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0978901234', '2025-11-03 09:00:00'),
('Ngô Văn Ích', 'organizer4@business.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0989012345', '2025-11-03 10:00:00'),
('Lý Thị Kim', 'user5@outlook.com', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0990123456', '2025-11-03 11:00:00');

-- INSERT USER ROLES (Phân quyền người dùng)
INSERT INTO user_roles (user_id, role_id) VALUES 
(1, 1), -- Nguyễn Văn An - Admin
(2, 2), -- Trần Thị Bình - Organizer
(3, 2), -- Lê Minh Cường - Organizer  
(4, 3), -- Phạm Thị Dung - Attendee
(5, 3), -- Hoàng Văn Em - Attendee
(6, 3), -- Võ Thị Phượng - Attendee
(7, 2), -- Đặng Minh Giáp - Organizer
(8, 3), -- Bùi Thị Hoa - Attendee
(9, 2), -- Ngô Văn Ích - Organizer
(10, 3); -- Lý Thị Kim - Attendee

-- INSERT ADDITIONAL LOCATIONS (Thêm địa điểm tại Việt Nam)
INSERT INTO event_locations (name, address, city, capacity) VALUES 
('Trung tâm Hội nghị Quốc gia', '123 Đường Lê Duẩn, Quận 1', 'Hồ Chí Minh', 3000),
('Nhà văn hóa Thanh niên', '456 Phố Huế, Hai Bà Trưng', 'Hà Nội', 800),
('Trung tâm Triển lãm Sài Gòn', '789 Nguyễn Văn Linh, Quận 7', 'Hồ Chí Minh', 2500),
('Đại học Bách khoa Hà Nội', '1 Đại Cồ Việt, Hai Bà Trưng', 'Hà Nội', 1200),
('Sân vận động Mỹ Đình', '2 Đường Phạm Hùng, Nam Từ Liêm', 'Hà Nội', 40000),
('Khách sạn Rex Sài Gòn', '141 Nguyễn Huệ, Quận 1', 'Hồ Chí Minh', 500),
('Công viên Tao Đàn', '1 Trương Định, Quận 1', 'Hồ Chí Minh', 1500),
('Trung tâm Hòa Bình', '27 Lý Thường Kiệt, Hoàn Kiếm', 'Hà Nội', 600),
('Resort FLC Quy Nhon', '123 Đường Nguyễn Tất Thành', 'Quy Nhon', 1000),
('Vinpearl Land Nha Trang', '456 Trần Phú, Lộc Thọ', 'Nha Trang', 2000);

-- INSERT SAMPLE EVENTS (Sự kiện mẫu tiếng Việt)
INSERT INTO events (organizer_id, category_id, location_id, title, description, start_time, end_time, max_attendees, approved, approved_at, approved_by) VALUES 
(2, 1, 1, 'Hội thảo Công nghệ AI Việt Nam 2025', 
'Hội thảo về xu hướng Trí tuệ nhân tạo tại Việt Nam với sự tham gia của các chuyên gia hàng đầu. Sự kiện sẽ bao gồm các phiên thảo luận về Machine Learning, Deep Learning và ứng dụng AI trong doanh nghiệp Việt Nam.', 
'2025-12-15 08:30:00', '2025-12-15 17:00:00', 500, true, '2025-11-10 14:00:00', 1),

(3, 2, 2, 'Diễn đàn Khởi nghiệp Việt Nam', 
'Sự kiện kết nối các startup Việt Nam với nhà đầu tư và mentor. Cơ hội tuyệt vời để học hỏi kinh nghiệm khởi nghiệp, gặp gỡ đối tác tiềm năng và tìm kiếm nguồn vốn đầu tư.', 
'2025-12-20 09:00:00', '2025-12-20 18:00:00', 800, true, '2025-11-08 10:00:00', 1),

(7, 1, 3, 'Workshop Lập trình Web với Laravel', 
'Khóa học thực hành 2 ngày về Laravel Framework. Từ cơ bản đến nâng cao, xây dựng ứng dụng web hoàn chỉnh. Phù hợp cho sinh viên và lập trình viên mới bắt đầu.', 
'2025-12-22 08:00:00', '2025-12-23 17:00:00', 100, true, '2025-11-09 16:00:00', 1),

(9, 3, 4, 'Đêm nhạc "Những Khúc Hát Xưa"', 
'Đêm nhạc tái hiện những ca khúc bất hủ của nhạc Việt với sự tham gia của các ca sĩ nổi tiếng. Không gian ấm cúng, đầy cảm xúc cho những ai yêu nhạc truyền thống.', 
'2025-12-25 19:30:00', '2025-12-25 22:00:00', 1500, true, '2025-11-11 09:00:00', 1),

(2, 5, 5, 'Khóa học "Kỹ năng Thuyết trình Hiệu quả"', 
'3 ngày học tập chuyên sâu về kỹ năng thuyết trình, giao tiếp công sở và thuyết phục khách hàng. Với nhiều bài tập thực hành và phản hồi từ chuyên gia.', 
'2025-12-28 08:30:00', '2025-12-30 17:30:00', 80, true, '2025-11-12 11:00:00', 1),

(3, 4, 6, 'Giải đấu Esports "VN Championship"', 
'Giải đấu game online lớn nhất Việt Nam với tổng giải thưởng 1 tỷ đồng. Thi đấu các game phổ biến như LMHT, PUBG Mobile, FIFA Online 4.', 
'2026-01-05 09:00:00', '2026-01-07 22:00:00', 15000, false, null, null),

(7, 6, 7, 'Festival Yoga & Meditation "Tìm về chính mình"', 
'3 ngày retreat yoga và thiền định tại resort sang trọng. Kết hợp giữa thực hành yoga, thiền, ăn chay và các hoạt động chăm sóc sức khỏe tinh thần.', 
'2026-01-12 06:00:00', '2026-01-14 18:00:00', 200, true, '2025-11-13 08:00:00', 1),

(9, 7, 8, 'Triển lãm Nghệ thuật Đương đại Việt Nam', 
'Triển lãm quy mô lớn giới thiệu tác phẩm của 50 nghệ sĩ Việt Nam. Bao gồm hội họa, điêu khắc, nghệ thuật số và các tác phẩm installation độc đáo.', 
'2026-01-18 10:00:00', '2026-01-25 20:00:00', 5000, true, '2025-11-13 15:00:00', 1);

-- INSERT TICKET TYPES (Các loại vé cho từng sự kiện)
INSERT INTO ticket_types (event_id, name, price, total_quantity, remaining_quantity, description, sale_start_time, sale_end_time) VALUES 
-- Hội thảo AI
(1, 'Vé Sinh viên', 150000.00, 200, 180, 'Dành cho sinh viên có thẻ học sinh, sinh viên. Bao gồm tài liệu và ăn trưa.', '2025-11-15 00:00:00', '2025-12-14 23:59:59'),
(1, 'Vé Thường', 300000.00, 250, 220, 'Vé tham dự tiêu chuẩn. Bao gồm tài liệu, ăn trưa và coffee break.', '2025-11-15 00:00:00', '2025-12-14 23:59:59'),
(1, 'Vé VIP', 500000.00, 50, 45, 'Chỗ ngồi hàng đầu, gặp gỡ diễn giả, quà tặng đặc biệt.', '2025-11-15 00:00:00', '2025-12-14 23:59:59'),

-- Diễn đàn Khởi nghiệp
(2, 'Vé Startup', 200000.00, 300, 250, 'Dành cho founder và nhân viên startup. Bao gồm networking lunch.', '2025-11-20 00:00:00', '2025-12-19 23:59:59'),
(2, 'Vé Investor', 800000.00, 100, 85, 'Dành cho nhà đầu tư và VC. Gặp gỡ riêng với các startup tiềm năng.', '2025-11-20 00:00:00', '2025-12-19 23:59:59'),
(2, 'Vé Thường', 400000.00, 400, 320, 'Vé tham dự tiêu chuẩn cho tất cả mọi người.', '2025-11-20 00:00:00', '2025-12-19 23:59:59'),

-- Workshop Laravel
(3, 'Vé Early Bird', 800000.00, 30, 15, 'Giá ưu đãi sớm. Bao gồm tài liệu, laptop thuê và certificate.', '2025-11-25 00:00:00', '2025-12-01 23:59:59'),
(3, 'Vé Thường', 1200000.00, 70, 60, 'Vé tiêu chuẩn 2 ngày workshop đầy đủ.', '2025-11-25 00:00:00', '2025-12-21 23:59:59'),

-- Đêm nhạc
(4, 'Vé Thường', 250000.00, 1000, 800, 'Chỗ ngồi khu vực thường. Bao gồm 1 thức uống.', '2025-12-01 00:00:00', '2025-12-25 18:00:00'),
(4, 'Vé VIP', 500000.00, 300, 250, 'Chỗ ngồi ưu tiên, buffet và meet & greet với ca sĩ.', '2025-12-01 00:00:00', '2025-12-25 18:00:00'),
(4, 'Vé VVIP', 1000000.00, 200, 180, 'Bàn riêng, champagne và photo với ca sĩ.', '2025-12-01 00:00:00', '2025-12-25 18:00:00'),

-- Khóa học Thuyết trình
(5, 'Vé Sinh viên', 1500000.00, 30, 25, 'Ưu đãi đặc biệt cho sinh viên. Bao gồm tài liệu và certificate.', '2025-12-05 00:00:00', '2025-12-27 23:59:59'),
(5, 'Vé Thường', 2500000.00, 50, 40, 'Khóa học đầy đủ 3 ngày với chuyên gia hàng đầu.', '2025-12-05 00:00:00', '2025-12-27 23:59:59');

-- INSERT SAMPLE TICKETS (Vé đã mua)
INSERT INTO tickets (ticket_type_id, attendee_id, payment_status, qr_code) VALUES 
(1, 4, 'paid', 'QR001AIVN2025STU004'), -- Phạm Thị Dung mua vé sinh viên AI
(2, 5, 'paid', 'QR002AIVN2025REG005'), -- Hoàng Văn Em mua vé thường AI
(3, 6, 'paid', 'QR003AIVN2025VIP006'), -- Võ Thị Phượng mua vé VIP AI
(4, 8, 'paid', 'QR004STVN2025STP008'), -- Bùi Thị Hoa mua vé startup
(5, 10, 'pending', 'QR005STVN2025INV010'), -- Lý Thị Kim mua vé investor (chưa thanh toán)
(7, 4, 'paid', 'QR007LRVN2025EAR004'), -- Phạm Thị Dung mua vé Early Bird Laravel
(9, 5, 'paid', 'QR009MUVN2025REG005'), -- Hoàng Văn Em mua vé nhạc thường
(10, 6, 'paid', 'QR010MUVN2025VIP006'), -- Võ Thị Phượng mua vé nhạc VIP
(12, 8, 'paid', 'QR012PTVN2025STU008'); -- Bùi Thị Hoa mua vé sinh viên thuyết trình

-- INSERT SAMPLE PAYMENTS (Thanh toán)
INSERT INTO payments (ticket_id, method_id, amount, status, transaction_id) VALUES 
(1, 1, 120000.00, 'success', 'VCB001VN20251108001'), -- Vé sinh viên AI (có discount) - Vietcombank
(2, 4, 300000.00, 'success', 'MOMO002VN20251108002'), -- Vé thường AI - MoMo
(3, 2, 500000.00, 'success', 'ACB003VN20251108003'), -- Vé VIP AI - ACB
(4, 1, 160000.00, 'success', 'VCB004VN20251108004'), -- Vé startup (có discount) - Vietcombank
(6, 3, 800000.00, 'success', 'CASH006VN20251108006'), -- Vé Early Bird Laravel - Tiền mặt
(7, 1, 250000.00, 'success', 'VCB007VN20251108007'), -- Vé nhạc thường - Vietcombank
(8, 4, 500000.00, 'success', 'ZALO008VN20251108008'), -- Vé nhạc VIP - ZaloPay
(9, 5, 750000.00, 'success', 'QR009VN20251108009'); -- Vé sinh viên thuyết trình - QR Code

-- INSERT SAMPLE REVIEWS (Đánh giá sự kiện)
INSERT INTO reviews (event_id, user_id, rating, comment) VALUES 
(1, 4, 5, 'Hội thảo rất bổ ích! Các diễn giả am hiểu sâu sắc về AI. Tài liệu chi tiết và thực tế. Sẽ giới thiệu cho bạn bè tham gia các sự kiện sau.'),
(1, 5, 4, 'Nội dung hay, tuy nhiên phòng hơi nhỏ so với số lượng người tham gia. Âm thanh có lúc không rõ. Nhìn chung vẫn rất đáng tham gia.'),
(1, 6, 5, 'Xuất sắc! Mình đã học được rất nhiều về xu hướng AI tại Việt Nam. Networking session rất hiệu quả, đã kết nối được với nhiều chuyên gia.'),
(2, 8, 4, 'Diễn đàn khởi nghiệp rất thú vị. Nhiều startup có ý tưởng sáng tạo. Tuy nhiên thời gian hơi gấp gáp, mong có thêm thời gian thảo luận.');

-- INSERT SAMPLE FAVORITES (Sự kiện yêu thích)
INSERT INTO favorites (user_id, event_id) VALUES 
(4, 3), -- Phạm Thị Dung quan tâm Workshop Laravel
(4, 5), -- Phạm Thị Dung quan tâm Khóa học Thuyết trình
(5, 4), -- Hoàng Văn Em quan tâm Đêm nhạc
(5, 6), -- Hoàng Văn Em quan tâm Giải Esports
(6, 7), -- Võ Thị Phượng quan tâm Festival Yoga
(6, 8), -- Võ Thị Phượng quan tâm Triển lãm Nghệ thuật
(8, 2), -- Bùi Thị Hoa quan tâm Diễn đàn Khởi nghiệp
(8, 3), -- Bùi Thị Hoa quan tâm Workshop Laravel
(10, 1), -- Lý Thị Kim quan tâm Hội thảo AI
(10, 5); -- Lý Thị Kim quan tâm Khóa học Thuyết trình

-- INSERT SAMPLE NOTIFICATIONS (Thông báo)
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES 
(4, 'Thanh toán thành công', 'Bạn đã thanh toán thành công vé tham dự "Hội thảo Công nghệ AI Việt Nam 2025". Mã QR: QR001AIVN2025STU004', 'success', true),
(5, 'Sự kiện sắp diễn ra', 'Sự kiện "Hội thảo Công nghệ AI Việt Nam 2025" sẽ bắt đầu trong 3 ngày. Đừng quên mang theo vé và CCCD.', 'info', true),
(6, 'Cập nhật sự kiện', 'Thời gian đăng ký "Đêm nhạc Những Khúc Hát Xưa" đã được gia hạn đến 18:00 ngày 25/12/2025.', 'info', false),
(8, 'Sự kiện mới', 'Có sự kiện mới phù hợp với sở thích của bạn: "Workshop Lập trình Web với Laravel". Đăng ký ngay!', 'info', false),
(10, 'Vé chờ thanh toán', 'Bạn có 1 vé đang chờ thanh toán. Vui lòng hoàn tất thanh toán trong 24h để giữ chỗ.', 'warning', false),
(4, 'Khuyến mãi đặc biệt', 'Sử dụng mã STUDENT50 để được giảm 50% cho tất cả sự kiện giáo dục. Có hiệu lực đến cuối năm!', 'success', false),
(5, 'Nhắc nhở check-in', 'Đừng quên check-in tại sự kiện "Hội thảo AI" bằng mã QR. Quầy đăng ký mở cửa từ 8:00 sáng.', 'info', true);

-- INSERT SAMPLE ADMIN LOGS (Log hoạt động admin)
INSERT INTO admin_logs (admin_id, action, target_table, target_id, old_values, new_values, ip_address) VALUES 
(1, 'approve_event', 'events', 1, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-10 14:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 2, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-08 10:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 3, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-09 16:00:00", "approved_by": 1}', '192.168.1.101'),
(1, 'create_coupon', 'coupons', 4, null, '{"code": "TETHOLIDAY", "discount_percent": 30, "status": "active"}', '192.168.1.100'),
(1, 'update_user_role', 'user_roles', null, null, '{"user_id": 1, "role_id": 1, "action": "assign_admin_role"}', '192.168.1.100');

-- INSERT EVENT TAG MAPPINGS (Gắn thẻ cho sự kiện)
INSERT INTO event_tag_map (event_id, tag_id) VALUES 
(1, 1), (1, 4), (1, 9),  -- Hội thảo AI: Hội thảo, Đào tạo, Webinar
(2, 1), (2, 3), (2, 12), -- Diễn đàn Khởi nghiệp: Hội thảo, Kết nối, Đỉnh cao
(3, 2), (3, 4), (3, 11), -- Workshop Laravel: Workshop, Đào tạo, Bootcamp
(4, 8), (4, 1),          -- Đêm nhạc: Lễ hội, Hội thảo
(5, 2), (5, 4), (5, 5),  -- Khóa học Thuyết trình: Workshop, Đào tạo, Hội nghị
(6, 7), (6, 8),          -- Giải Esports: Thi đấu, Lễ hội
(7, 2), (7, 8),          -- Festival Yoga: Workshop, Lễ hội
(8, 6), (8, 8);          -- Triển lãm Nghệ thuật: Triển lãm, Lễ hội

-- ================================================================
-- DỮ LIỆU MẪU BỔ SUNG CHO CÁC BẢNG KHÁC
-- ================================================================

-- INSERT SAMPLE INCIDENT REPORTS (Báo cáo sự cố)
INSERT INTO incident_reports (event_id, reporter_id, description, status) VALUES 
(1, 4, 'Hệ thống âm thanh bị nghịch âm trong 10 phút đầu buổi hội thảo. Đã được khắc phục kịp thời.', 'resolved'),
(2, 8, 'Có người tham gia không đúng dress code, gây ảnh hưởng đến không khí sự kiện.', 'closed'),
(4, 5, 'Một số ghế VIP bị hỏng, cần thay thế trước giờ diễn ra sự kiện.', 'in_progress');

-- INSERT SAMPLE REVIEW REPORTS (Báo cáo đánh giá)
INSERT INTO review_reports (review_id, reporter_id, reason, status) VALUES 
(2, 6, 'Bình luận có nội dung tiêu cực không đúng sự thật về chất lượng sự kiện.', 'pending'),
(4, 5, 'Đánh giá spam, không có nội dung thực chất về sự kiện.', 'reviewed');

-- INSERT SAMPLE REFUNDS (Hoàn tiền)
INSERT INTO refunds (payment_id, requester_id, reason, status) VALUES 
(5, 10, 'Không thể tham gia do lý do cá nhân đột xuất. Xin hoàn tiền vé investor.', 'pending'),
(2, 8, 'Sự kiện bị hoãn, yêu cầu hoàn tiền theo chính sách.', 'approved');

-- INSERT SAMPLE SYSTEM REPORTS (Báo cáo hệ thống)
INSERT INTO system_reports (generated_by, title, content, report_type) VALUES 
(1, 'Báo cáo doanh thu tháng 11/2025', '{"total_revenue": 15500000, "total_tickets": 45, "popular_category": "Công nghệ"}', 'monthly'),
(1, 'Thống kê người dùng tuần 46', '{"new_users": 25, "active_events": 8, "completion_rate": 89.5}', 'weekly');

-- INSERT SAMPLE EVENT MAPS (Bản đồ sự kiện)
INSERT INTO event_maps (event_id, map_image_url, note) VALUES 
(1, '/images/maps/ai-conference-floor-plan.jpg', 'Sơ đồ mặt bằng hội trường với khu vực VIP và networking area'),
(2, '/images/maps/startup-forum-layout.png', 'Layout không gian triển lãm startup và khu vực pitch'),
(3, '/images/maps/laravel-workshop-classroom.jpg', 'Sơ đồ phòng học workshop với bàn máy tính và projector'),
(4, '/images/maps/concert-seating-chart.jpg', 'Sơ đồ chỗ ngồi đêm nhạc với phân khu VIP, VVIP và thường'),
(5, '/images/maps/presentation-training-room.png', 'Layout phòng đào tạo với không gian thuyết trình và ghế khán giả'),
(7, '/images/maps/yoga-festival-outdoor.jpg', 'Sơ đồ khu vực yoga ngoài trời với các zone thiền và workshop'),
(8, '/images/maps/art-exhibition-gallery.png', 'Bố trí triển lãm nghệ thuật với các khu vực tác phẩm theo chủ đề');

-- ================================================================
-- THÊM DỮ LIỆU MẪU CHO CÁC BẢNG SYSTEM (Laravel)
-- ================================================================

-- INSERT SAMPLE CACHE DATA (Dữ liệu cache mẫu)
INSERT INTO cache (`key`, `value`, expiration) VALUES 
('events_popular', 's:1234:"a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}"', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 1 HOUR))),
('categories_list', 's:567:"a:7:{i:0;s:10:\"Công nghệ\";i:1;s:11:\"Kinh doanh\"...}"', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 2 HOUR))),
('user_4_profile', 's:890:"a:5:{s:7:\"user_id\";i:4;s:9:\"full_name\";s:15:\"Phạm Thị Dung\"...}"', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 30 MINUTE))),
('notifications_count_4', 's:1:"3"', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 15 MINUTE))),
('events_upcoming', 's:2345:"a:5:{i:0;a:3:{s:8:\"event_id\";i:1;s:5:\"title\";s:35:\"Hội thảo Công nghệ AI\"...}"', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 45 MINUTE)));

-- INSERT SAMPLE SESSIONS (Phiên đăng nhập mẫu)
INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES 
('9f2kL8nX3vB7mQ1dR5eT', 4, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUZyT3JjVDBQaXdxMnBjYXF4OXhINkMxcjREREpZdHJGWnNSeG5jSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0%3D', UNIX_TIMESTAMP()),
('7h5gK9mN2vX8jQ4dT6eW', 5, '192.168.1.106', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUkVyVG5jMFBpd3EycGNhcXo5eEg2QzFyNERESlljckZac1J4bmNJIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyNDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ%3D%3D', UNIX_TIMESTAMP()),
('2k8mL3nP9vR5jX7dQ4eB', 6, '192.168.1.107', 'Mozilla/5.0 (Android 11; Mobile; rv:92.0) Gecko/92.0 Firefox/92.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0VyWG5jMFBpd3EycGNhcXg5eEg2QzFyNERESlljckZac1J4bmNJIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozMDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2V2ZW50cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0%3D', UNIX_TIMESTAMP());

-- INSERT SAMPLE FAILED JOBS (Jobs thất bại mẫu)
INSERT INTO failed_jobs (uuid, connection, queue, payload, exception, failed_at) VALUES 
('550e8400-e29b-41d4-a716-446655440001', 'database', 'default', '{"commandName":"App\\\\Jobs\\\\SendEventNotification","command":"O:31:\"App\\\\Jobs\\\\SendEventNotification\":1:{s:7:\"eventId\";i:1;}"}', 'Swift_TransportException: Connection could not be established with host smtp.gmail.com', '2025-11-14 08:30:00'),
('550e8400-e29b-41d4-a716-446655440002', 'database', 'emails', '{"commandName":"App\\\\Jobs\\\\SendWelcomeEmail","command":"O:28:\"App\\\\Jobs\\\\SendWelcomeEmail\":1:{s:6:\"userId\";i:4;}"}', 'Exception: SMTP server timeout after 30 seconds', '2025-11-14 09:15:00'),
('550e8400-e29b-41d4-a716-446655440003', 'database', 'notifications', '{"commandName":"App\\\\Jobs\\\\ProcessRefundNotification","command":"O:35:\"App\\\\Jobs\\\\ProcessRefundNotification\":1:{s:8:\"refundId\";i:1;}"}', 'PDOException: SQLSTATE[HY000]: General error: 2006 MySQL server has gone away', '2025-11-14 10:45:00');

-- INSERT SAMPLE JOBS (Queue jobs mẫu)
INSERT INTO jobs (queue, payload, attempts, reserved_at, available_at, created_at) VALUES 
('default', '{"commandName":"App\\\\Jobs\\\\SendEventReminder","command":"O:29:\"App\\\\Jobs\\\\SendEventReminder\":2:{s:7:\"eventId\";i:1;s:9:\"reminderType\";s:7:\"24hours\";}"}', 0, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('emails', '{"commandName":"App\\\\Jobs\\\\SendTicketConfirmation","command":"O:32:\"App\\\\Jobs\\\\SendTicketConfirmation\":1:{s:8:\"ticketId\";i:9;}"}', 1, UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 5 MINUTE)), UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('notifications', '{"commandName":"App\\\\Jobs\\\\CleanupOldNotifications","command":"O:34:\"App\\\\Jobs\\\\CleanupOldNotifications\":1:{s:4:\"days\";i:30;}"}', 0, NULL, UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 1 DAY)), UNIX_TIMESTAMP());

-- INSERT SAMPLE JOB BATCHES (Batch jobs mẫu)
INSERT INTO job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) VALUES 
('9a035c3d-8b4f-4c2a-a6e7-1234567890ab', 'Daily Event Reminders', 25, 5, 2, '[15,23]', '{"timeout":300,"retry_until":1731744000}', NULL, UNIX_TIMESTAMP(), NULL),
('9a035c3d-8b4f-4c2a-a6e7-1234567890ac', 'Weekly Analytics Report', 12, 0, 0, '[]', '{"timeout":600,"retry_until":1731830400}', NULL, UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY)), UNIX_TIMESTAMP()),
('9a035c3d-8b4f-4c2a-a6e7-1234567890ad', 'Monthly Cleanup Tasks', 8, 3, 1, '[7]', '{"timeout":1800,"retry_until":1734422400}', NULL, UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 DAY)), NULL);

-- ================================================================
-- THÊM DỮ LIỆU MẪU CHO NGƯỜI DÙNG VÀ SỰ KIỆN
-- ================================================================

-- INSERT THÊM USERS (Mở rộng danh sách người dùng)
INSERT INTO users (full_name, email, password_hash, phone, email_verified_at, avatar_url) VALUES 
('Nguyễn Thị Lan', 'lan.nguyen@techcorp.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0901111111', '2025-11-04 10:00:00', '/avatars/lan_nguyen.jpg'),
('Trần Văn Minh', 'minh.tran@startup.io', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0912222222', '2025-11-04 11:00:00', '/avatars/minh_tran.jpg'),
('Lê Thị Hương', 'huong.le@design.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0923333333', '2025-11-04 12:00:00', '/avatars/huong_le.jpg'),
('Phạm Văn Đức', 'duc.pham@university.edu.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0934444444', '2025-11-04 13:00:00', '/avatars/duc_pham.jpg'),
('Võ Thị Mai', 'mai.vo@hospital.gov.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0945555555', '2025-11-04 14:00:00', '/avatars/mai_vo.jpg'),
('Hoàng Văn Nam', 'nam.hoang@bank.com.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0956666666', '2025-11-04 15:00:00', '/avatars/nam_hoang.jpg'),
('Đặng Thị Thu', 'thu.dang@media.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0967777777', '2025-11-04 16:00:00', '/avatars/thu_dang.jpg'),
('Bùi Văn Toàn', 'toan.bui@logistics.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0978888888', '2025-11-05 08:00:00', '/avatars/toan_bui.jpg'),
('Ngô Thị Yến', 'yen.ngo@restaurant.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0989999999', '2025-11-05 09:00:00', '/avatars/yen_ngo.jpg'),
('Lý Văn Hải', 'hai.ly@construction.vn', '$2y$12$LQv3c1yqBwFzW4kEQJcGqO8B8K4zxUaNpXJ/lPz8XQc8V9FyoHgW6', '0990000000', '2025-11-05 10:00:00', '/avatars/hai_ly.jpg');

-- INSERT USER ROLES CHO USERS MỚI
INSERT INTO user_roles (user_id, role_id) VALUES 
(11, 2), -- Nguyễn Thị Lan - Organizer
(12, 3), -- Trần Văn Minh - Attendee  
(13, 2), -- Lê Thị Hương - Organizer
(14, 3), -- Phạm Văn Đức - Attendee
(15, 3), -- Võ Thị Mai - Attendee
(16, 3), -- Hoàng Văn Nam - Attendee
(17, 3), -- Đặng Thị Thu - Attendee
(18, 3), -- Bùi Văn Toàn - Attendee
(19, 2), -- Ngô Thị Yến - Organizer
(20, 3); -- Lý Văn Hải - Attendee

-- INSERT THÊM EVENTS (Sự kiện mới)
INSERT INTO events (organizer_id, category_id, location_id, title, description, start_time, end_time, max_attendees, approved, approved_at, approved_by, banner_url) VALUES 
(11, 1, 9, 'Bootcamp Lập trình Full-Stack 2025', 
'Khóa học intensive 7 ngày về lập trình Full-Stack: React, Node.js, MongoDB. Từ zero đến hero với project thực tế và mentorship 1-on-1.', 
'2026-01-20 08:00:00', '2026-01-26 18:00:00', 50, true, '2025-11-14 10:00:00', 1, '/banners/fullstack_bootcamp.jpg'),

(13, 7, 10, 'Workshop "Thiết kế UI/UX cho Mobile App"', 
'2 ngày học thiết kế giao diện và trải nghiệm người dùng cho ứng dụng di động. Sử dụng Figma, Adobe XD và các tool design hiện đại.', 
'2026-02-01 09:00:00', '2026-02-02 17:00:00', 80, true, '2025-11-14 11:00:00', 1, '/banners/uiux_workshop.jpg'),

(19, 3, 1, 'Đêm Chung kết "Vietnam''s Got Talent Startup"', 
'Đêm chung kết cuộc thi tài năng dành cho startup Việt Nam. 20 startup hàng đầu sẽ tranh tài và tìm kiếm nhà đầu tư tiềm năng.', 
'2026-02-14 18:30:00', '2026-02-14 22:00:00', 2000, true, '2025-11-14 12:00:00', 1, '/banners/startup_talent.jpg'),

(11, 5, 2, 'Hội thảo "Kỹ năng Lãnh đạo trong Kỷ nguyên số"', 
'Học cách lãnh đạo team trong môi trường công nghệ số. Với sự tham gia của các CEO hàng đầu Việt Nam và chuyên gia quốc tế.', 
'2026-02-28 08:30:00', '2026-02-28 17:30:00', 300, false, null, null, '/banners/leadership_digital.jpg'),

(13, 6, 3, 'Retreat "Tìm lại cân bằng cuộc sống"', 
'3 ngày 2 đêm retreat tại resort 5 sao. Kết hợp yoga, meditation, healthy eating và các workshop về work-life balance.', 
'2026-03-15 14:00:00', '2026-03-17 12:00:00', 100, true, '2025-11-14 13:00:00', 1, '/banners/wellness_retreat.jpg'),

(19, 4, 4, 'Giải Marathon "Chạy vì Trái Đất Xanh"', 
'Giải chạy marathon từ thiện gây quỹ trồng cây xanh. 3 cự ly: 5km, 10km, 21km. Có giải thưởng và quà tặng ý nghĩa.', 
'2026-03-22 05:30:00', '2026-03-22 12:00:00', 5000, true, '2025-11-14 14:00:00', 1, '/banners/green_marathon.jpg');

-- INSERT THÊM TICKET TYPES CHO CÁC SỰ KIỆN MỚI
INSERT INTO ticket_types (event_id, name, price, total_quantity, remaining_quantity, description, sale_start_time, sale_end_time) VALUES 
-- Bootcamp Full-Stack
(9, 'Vé Early Bird', 5000000.00, 20, 15, 'Giá ưu đãi sớm. Bao gồm tài liệu, laptop thuê, 1-on-1 mentorship và certificate.', '2025-12-01 00:00:00', '2025-12-31 23:59:59'),
(9, 'Vé Thường', 7000000.00, 30, 25, 'Vé tiêu chuẩn bootcamp 7 ngày đầy đủ.', '2025-12-01 00:00:00', '2026-01-19 23:59:59'),

-- Workshop UI/UX
(10, 'Vé Sinh viên', 800000.00, 30, 25, 'Ưu đãi cho sinh viên design và IT. Bao gồm software license 1 tháng.', '2025-12-15 00:00:00', '2026-01-31 23:59:59'),
(10, 'Vé Designer', 1200000.00, 35, 30, 'Dành cho designer đang làm việc. Bao gồm template và asset pack.', '2025-12-15 00:00:00', '2026-01-31 23:59:59'),
(10, 'Vé Team (5 người)', 4500000.00, 15, 12, 'Ưu đãi cho team. Giá bao gồm 5 người, team building và group project.', '2025-12-15 00:00:00', '2026-01-31 23:59:59'),

-- Đêm Chung kết Startup
(11, 'Vé Khán giả', 200000.00, 1500, 1200, 'Chỗ ngồi khu vực thường, xem show và networking.', '2026-01-01 00:00:00', '2026-02-14 16:00:00'),
(11, 'Vé Investor', 1000000.00, 300, 250, 'Chỗ ngồi VIP, gặp riêng startup, buffet cao cấp.', '2026-01-01 00:00:00', '2026-02-14 16:00:00'),
(11, 'Vé Sponsor', 2000000.00, 200, 180, 'Chỗ ngồi VVIP, branding exposure, meet & greet với judges.', '2026-01-01 00:00:00', '2026-02-14 16:00:00'),

-- Retreat Wellness
(13, 'Vé Single Room', 3500000.00, 50, 40, 'Phòng đơn 3 ngày 2 đêm, full-board, tất cả activities.', '2026-01-15 00:00:00', '2026-03-14 23:59:59'),
(13, 'Vé Twin Share', 2800000.00, 50, 45, 'Phòng đôi chia sẻ, phù hợp cho bạn bè hoặc cặp đôi.', '2026-01-15 00:00:00', '2026-03-14 23:59:59'),

-- Marathon
(14, 'Vé 5km', 150000.00, 2000, 1800, 'Cự ly 5km, phù hợp mọi lứa tuổi. Bao gồm medal và BIB.', '2026-02-01 00:00:00', '2026-03-21 23:59:59'),
(14, 'Vé 10km', 250000.00, 1500, 1300, 'Cự ly 10km cho runner trung bình. Medal, BIB và áo kỷ niệm.', '2026-02-01 00:00:00', '2026-03-21 23:59:59'),
(14, 'Vé 21km', 400000.00, 1500, 1200, 'Half Marathon, thử thách cho runner giàu kinh nghiệm.', '2026-02-01 00:00:00', '2026-03-21 23:59:59');

-- INSERT THÊM TICKETS ĐÃ MUA
INSERT INTO tickets (ticket_type_id, attendee_id, payment_status, qr_code) VALUES 
(13, 12, 'paid', 'QR013FSVN2026EAR012'), -- Trần Văn Minh mua Early Bird Full-Stack
(14, 14, 'paid', 'QR014FSVN2026REG014'), -- Phạm Văn Đức mua vé thường Full-Stack
(15, 15, 'paid', 'QR015UIVN2026STU015'), -- Võ Thị Mai mua vé sinh viên UI/UX
(16, 16, 'paid', 'QR016UIVN2026DES016'), -- Hoàng Văn Nam mua vé designer UI/UX
(18, 17, 'paid', 'QR018STVN2026AUD017'), -- Đặng Thị Thu mua vé khán giả Startup
(19, 18, 'pending', 'QR019STVN2026INV018'), -- Bùi Văn Toàn mua vé investor (chưa thanh toán)
(21, 20, 'paid', 'QR021WLVN2026SIN020'), -- Lý Văn Hải mua vé single room Retreat
(23, 12, 'paid', 'QR023MRVN2026_5K012'), -- Trần Văn Minh đăng ký 5km Marathon
(24, 14, 'paid', 'QR024MRVN202610K014'), -- Phạm Văn Đức đăng ký 10km Marathon
(25, 16, 'paid', 'QR025MRVN202621K016'); -- Hoàng Văn Nam đăng ký 21km Marathon

-- INSERT THÊM PAYMENTS
INSERT INTO payments (ticket_id, method_id, amount, status, transaction_id) VALUES 
(10, 1, 4000000.00, 'success', 'VCB010VN20251114010'), -- Full-Stack Early Bird
(11, 4, 7000000.00, 'success', 'MOMO011VN20251114011'), -- Full-Stack Regular
(12, 2, 640000.00, 'success', 'ACB012VN20251114012'), -- UI/UX Student (có discount)
(13, 1, 1200000.00, 'success', 'VCB013VN20251114013'), -- UI/UX Designer
(14, 3, 200000.00, 'success', 'CASH014VN20251114014'), -- Startup Audience
(16, 5, 3500000.00, 'success', 'QR016VN20251114016'), -- Wellness Single Room
(17, 1, 150000.00, 'success', 'VCB017VN20251114017'), -- Marathon 5km
(18, 4, 250000.00, 'success', 'ZALO018VN20251114018'), -- Marathon 10km
(19, 1, 400000.00, 'success', 'VCB019VN20251114019'); -- Marathon 21km

-- INSERT THÊM FAVORITES
INSERT INTO favorites (user_id, event_id) VALUES 
(12, 9), -- Trần Văn Minh quan tâm Bootcamp Full-Stack
(14, 10), -- Phạm Văn Đức quan tâm Workshop UI/UX
(15, 13), -- Võ Thị Mai quan tâm Wellness Retreat
(16, 14), -- Hoàng Văn Nam quan tâm Marathon
(17, 11), -- Đặng Thị Thu quan tâm Startup Talent
(18, 9), -- Bùi Văn Toàn quan tâm Bootcamp
(20, 10), -- Lý Văn Hải quan tâm UI/UX Workshop
(12, 11), -- Trần Văn Minh quan tâm Startup Show
(15, 14), -- Võ Thị Mai quan tâm Marathon
(17, 13); -- Đặng Thị Thu quan tâm Wellness Retreat

-- INSERT THÊM NOTIFICATIONS
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES 
(12, 'Thanh toán thành công', 'Bạn đã thanh toán thành công vé "Bootcamp Full-Stack 2025". Chuẩn bị tinh thần cho 7 ngày intensive learning!', 'success', false),
(14, 'Sự kiện yêu thích mở bán vé', 'Sự kiện "Workshop UI/UX cho Mobile App" đã mở bán vé. Số lượng có hạn, đăng ký ngay!', 'info', false),
(15, 'Nhắc nhở thanh toán', 'Bạn có 1 vé chưa thanh toán cho "Wellness Retreat". Vui lòng hoàn tất trong 12h để giữ chỗ.', 'warning', false),
(16, 'Sự kiện sắp diễn ra', 'Marathon "Chạy vì Trái Đất Xanh" sẽ bắt đầu trong 1 tuần. Hãy chuẩn bị thể lực tốt nhất!', 'info', true),
(17, 'Mã giảm giá đặc biệt', 'Chúc mừng bạn được chọn nhận mã VIPEXCLUSIVE30 - giảm 30% cho tất cả sự kiện Premium!', 'success', false),
(18, 'Cập nhật sự kiện', 'Địa điểm "Đêm Chung kết Startup Talent" đã được thay đổi sang Nhà văn hóa Thanh niên. Check email để biết chi tiết.', 'warning', false),
(20, 'Kết quả cuộc thi', 'Chúc mừng! Bạn đã trúng giải phụ trong cuộc thi "Dự đoán Winner Startup Talent" - 1 suất miễn phí workshop UI/UX!', 'success', false);

-- INSERT THÊM REVIEWS
INSERT INTO reviews (event_id, user_id, rating, comment) VALUES 
(2, 12, 5, 'Diễn đàn khởi nghiệp tuyệt vời! Đã kết nối được với nhiều mentor và nhà đầu tư. Network rất chất lượng, tổ chức chuyên nghiệp.'),
(3, 14, 4, 'Workshop Laravel rất thực tế. Giảng viên kinh nghiệm, bài tập phong phú. Chỉ tiếc là thời gian hơi gấp, mong có thêm thời gian practice.'),
(4, 15, 5, 'Đêm nhạc cảm xúc tuyệt vời! Âm thanh chất lượng cao, không gian ấm cúng. Những ca khúc xưa được thể hiện rất hay.'),
(5, 16, 3, 'Khóa học thuyết trình có nội dung tốt nhưng phòng học hơi chật. Một số bài tập chưa thực tế lắm. Trainer thì rất nhiệt tình.'),
(7, 17, 5, 'Festival Yoga tuyệt vời nhất mình từng tham gia! Resort đẹp, instructor chuyên nghiệp, thức ăn healthy ngon. Sẽ quay lại năm sau!'),
(8, 18, 4, 'Triển lãm nghệ thuật rất đa dạng và phong phú. Tác phẩm chất lượng cao, không gian bố trí hợp lý. Giá vé hơi cao so với thời gian tham quan.');

-- INSERT THÊM ADMIN LOGS
INSERT INTO admin_logs (admin_id, action, target_table, target_id, old_values, new_values, ip_address) VALUES 
(1, 'approve_event', 'events', 9, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-14 10:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 10, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-14 11:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 11, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-14 12:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 13, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-14 13:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'approve_event', 'events', 14, '{"approved": false}', '{"approved": true, "approved_at": "2025-11-14 14:00:00", "approved_by": 1}', '192.168.1.100'),
(1, 'create_user', 'users', 11, null, '{"user_id": 11, "full_name": "Nguyễn Thị Lan", "email": "lan.nguyen@techcorp.vn", "role": "organizer"}', '192.168.1.100'),
(1, 'update_coupon', 'coupons', 1, '{"status": "active"}', '{"status": "expired", "used_count": 100}', '192.168.1.101'),
(1, 'process_refund', 'refunds', 2, '{"status": "pending"}', '{"status": "approved", "processed_at": "2025-11-14 15:00:00"}', '192.168.1.100');

-- INSERT THÊM EVENT TAG MAPPINGS
INSERT INTO event_tag_map (event_id, tag_id) VALUES 
(9, 2), (9, 4), (9, 11),   -- Bootcamp Full-Stack: Workshop, Đào tạo, Bootcamp
(10, 2), (10, 4),          -- UI/UX Workshop: Workshop, Đào tạo
(11, 7), (11, 8), (11, 12), -- Startup Talent: Thi đấu, Lễ hội, Đỉnh cao
(12, 1), (12, 4), (12, 5),  -- Leadership Digital: Hội thảo, Đào tạo, Hội nghị
(13, 2), (13, 8),          -- Wellness Retreat: Workshop, Lễ hội
(14, 7), (14, 8);          -- Green Marathon: Thi đấu, Lễ hội

-- INSERT THÊM SYSTEM REPORTS
INSERT INTO system_reports (generated_by, title, content, report_type) VALUES 
(1, 'Báo cáo hoạt động tuần 47/2025', 
'{"period": "2025-11-11 to 2025-11-17", "stats": {"new_users": 10, "new_events": 6, "total_tickets_sold": 25, "revenue": 28540000, "popular_category": "Công nghệ", "completion_rate": 94.2}}', 
'weekly'),
(1, 'Phân tích xu hướng người dùng tháng 11', 
'{"month": "2025-11", "user_behavior": {"most_active_time": "19:00-21:00", "popular_events": [1,2,4], "favorite_categories": ["Công nghệ", "Giải trí", "Kinh doanh"], "conversion_rate": 87.5, "return_rate": 65.2}}', 
'monthly'),
(1, 'Báo cáo sự kiện hot nhất quý 4/2025', 
'{"quarter": "Q4-2025", "top_events": [{"event_id": 1, "tickets_sold": 425, "revenue": 5450000}, {"event_id": 4, "tickets_sold": 1230, "revenue": 4850000}], "trending_categories": ["AI/Tech", "Entertainment"], "growth_rate": "+23.5%"}', 
'custom');

-- INSERT THÊM INCIDENT REPORTS
INSERT INTO incident_reports (event_id, reporter_id, description, status, resolved_at) VALUES 
(3, 14, 'Máy chiếu bị hỏng trong 30 phút đầu workshop Laravel. Đã thay máy dự phòng và tiếp tục bình thường.', 'resolved', '2025-12-22 09:30:00'),
(9, 12, 'WiFi tại venue không ổn định, ảnh hưởng đến việc code practice. Cần kiểm tra trước các event tech.', 'in_progress', NULL),
(11, 17, 'Một số startup không chuẩn bị đầy đủ demo, gây delay cho chương trình. Cần checklist rõ ràng hơn.', 'open', NULL),
(14, 16, 'Thời tiết mưa to vào sáng marathon, cần plan B cho các sự kiện outdoor.', 'open', NULL);

-- INSERT THÊM REVIEW REPORTS
INSERT INTO review_reports (review_id, reporter_id, reason, status) VALUES 
(5, 18, 'Review có nội dung thiếu tích cực và không khách quan về chất lượng event.', 'pending'),
(7, 15, 'Nghi ngờ đây là review fake, tài khoản mới tạo và không có bằng chứng tham gia sự kiện.', 'reviewed');

-- INSERT THÊM REFUNDS
INSERT INTO refunds (payment_id, requester_id, reason, status, processed_at) VALUES 
(11, 14, 'Có việc đột xuất không thể tham gia bootcamp 7 ngày. Xin hoàn tiền theo chính sách.', 'approved', '2025-11-14 16:00:00'),
(15, 18, 'Sự kiện bị hoãn từ tháng 2 sang tháng 3, không sắp xếp được lịch mới.', 'pending', NULL);

-- ================================================================
-- THỐNG KÊ CUỐI FILE
-- ================================================================

-- Thống kê tổng quan dữ liệu mẫu đã tạo:
-- - Users: 20 người (1 admin, 6 organizers, 13 attendees)  
-- - Events: 14 sự kiện (11 approved, 3 pending)
-- - Event Categories: 7 categories
-- - Event Locations: 17 địa điểm 
-- - Ticket Types: 25 loại vé
-- - Tickets Sold: 19 vé đã bán
-- - Payments: 17 thanh toán thành công
-- - Reviews: 10 đánh giá sự kiện
-- - Favorites: 20 lượt yêu thích
-- - Notifications: 12 thông báo
-- - Coupons: 6 mã giảm giá
-- - Tags: 12 tags
-- - Admin Logs: 13 log actions
-- - System Reports: 5 báo cáo
-- - Cache Entries: 5 entries
-- - Sessions: 3 active sessions
-- - Job Queue: 3 pending jobs
-- - Failed Jobs: 3 failed jobs

-- Total Records: 300+ records across all tables

-- Bật lại foreign key checks sau khi import xong
SET FOREIGN_KEY_CHECKS = 1;
