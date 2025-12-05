# Seniks Events Web - Hệ Thống Quản Lý Sự Kiện

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Hệ thống quản lý sự kiện toàn diện được xây dựng bằng Laravel 12.0, cho phép tổ chức sự kiện, bán vé trực tuyến, thanh toán và quản lý người tham gia.

## Tính Năng Chính

### Quản Lý Người Dùng
- Đăng ký/Đăng nhập với xác thực email
- Phân quyền: Admin, Organizer, Attendee
- Quản lý hồ sơ và đổi mật khẩu

### Quản Lý Sự Kiện
- Tạo và quản lý sự kiện với nhiều loại vé
- Duyệt sự kiện (Admin)
- Tìm kiếm và lọc sự kiện
- Yêu cầu hủy sự kiện

### Quản Lý Vé
- Mua vé với xử lý lost update
- QR Code duy nhất cho mỗi vé
- Check-in bằng QR code (Organizer)
- Tự động deactivate ticket types khi sự kiện kết thúc

### Thanh Toán
- **Tiền mặt:** Chờ organizer xác nhận
- **PayOS:** Thanh toán trực tuyến tự động
- **Tự động expire:** Hủy vé nếu thanh toán khác tiền mặt quá 10 phút chưa thanh toán
- Xem lịch sử thanh toán

### Đánh Giá & Yêu Thích
- Đánh giá sự kiện (1-5 sao + bình luận)
- Thêm/xóa sự kiện vào yêu thích
- Gợi ý sự kiện dựa trên yêu thích

### Thông Báo
- Thông báo tự động cho các sự kiện quan trọng
- Toast notifications
- Email notifications

### Admin Dashboard
- Thống kê tổng quan
- Quản lý sự kiện, người dùng, thanh toán, vé
- Quản lý danh mục và địa điểm
- Xem log hành động

### Organizer Dashboard
- Thống kê sự kiện
- Quản lý thanh toán tiền mặt
- QR Scanner để check-in
- Thống kê check-in

## Yêu Cầu Hệ Thống

- **PHP:** 8.2 hoặc cao hơn
- **Composer:** 2.x
- **Node.js:** 18.x hoặc cao hơn
- **npm:** 9.x hoặc cao hơn
- **Database:** MySQL 5.7+ 
- **Web Server:** Apache / Nginx

## Cài Đặt

### 1. Clone Repository

```bash
git clone <repository-url>
cd Laravel_PHP__Events_Web
```

### 2. Cài Đặt Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Cấu Hình Môi Trường

```bash
# Copy file .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

Chỉnh sửa file `.env`:

```env
APP_NAME="SeniksEvents"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=events_db
DB_USERNAME=root
DB_PASSWORD=

# PayOS Configuration
PAYOS_CLIENT_ID=your_client_id
PAYOS_API_KEY=your_api_key
PAYOS_CHECKSUM_KEY=your_checksum_key

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Tạo Database và Import Schema

```bash
# Tạo database
mysql -u root -p
CREATE DATABASE events_db;
exit

# Import schema và sample data
mysql -u root -p events_db < EventsDB.sql
```

### 5. Build Assets

```bash
npm run build
```

### 6. Chạy Server

```bash
php artisan serve
```

Truy cập: `http://localhost:8000`

## Cấu Hình Laravel Scheduler

Laravel Scheduler **KHÔNG tự động chạy**. Bạn cần cấu hình cron job:

### Development (Windows)

Chạy scheduler liên tục:
```bash
php artisan schedule:work
```

### Production (Linux/Unix)

Thêm vào crontab:
```bash
crontab -e
```

Thêm dòng sau (thay `/path-to-project` bằng đường dẫn thực tế):
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

- **`ticket-types:deactivate-expired`** - Chạy mỗi giờ
- **`payments:expire-pending-non-cash`** - Chạy mỗi 5 phút

## Sử Dụng

### Development

Chạy server, queue, logs và vite cùng lúc:
```bash
composer dev
```

### Production Build

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Chạy Queue Worker

```bash
php artisan queue:work
```

## Testing

```bash
php artisan test
```

## Cấu Trúc Dự Án

```
Laravel_PHP_Seniks_Events_Web/
├── app/
│   ├── Console/Commands/      # Artisan commands
│   ├── Http/Controllers/      # Controllers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   └── ...
├── database/
│   ├── migrations/            # Database migrations
│   └── EventsDB.sql          # Database schema & sample data
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # CSS files
│   └── js/                   # JavaScript files
├── routes/
│   ├── web.php               # Web routes
│   └── console.php           # Scheduled tasks
└── ...
```

## Đăng Nhập Mặc Định

Sau khi import `EventsDB.sql`, bạn có thể đăng nhập với:

- **Admin:** `admin@example.com` / `password`
- **Organizer:** `organizer@example.com` / `password`
- **Attendee:** `user@example.com` / `password`

## Bảo Mật

- Xác thực email bắt buộc cho một số chức năng
- Rate limiting cho các endpoint quan trọng
- Middleware phân quyền chặt chẽ
- Validation đầy đủ cho tất cả input

## Troubleshooting

### Lỗi PayOS
- Kiểm tra cấu hình `.env` (PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY)
- Xem logs: `storage/logs/laravel.log`

### Lỗi Database
- Kiểm tra kết nối database trong `.env`
- Chạy migrations: `php artisan migrate`
- Import schema: `mysql -u root -p events_db < EventsDB.sql`

### Scheduler không chạy
- Kiểm tra cron job đã được cấu hình chưa
- Test thủ công: `php artisan schedule:run`
- Development: Chạy `php artisan schedule:work` để chạy scheduler liên tục
- Production: Cấu hình cron job `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`

## License

MIT License

## Đóng Góp

Nếu bạn muốn đóng góp cho dự án:
1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push và tạo Pull Request

## Liên Hệ

Nếu có câu hỏi hoặc vấn đề, vui lòng tạo issue trên repository.

---

**Phiên bản:** 2.3  
**Cập nhật:** 2025-12-05
