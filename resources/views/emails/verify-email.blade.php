<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email - Seniks Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
            text-align: center;
            border: none;
        }
        .button:hover {
            background-color: #4338ca;
            color: #ffffff !important;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Seniks Events</div>
            <h1 style="color: #1f2937; margin: 0;">Xác thực email của bạn</h1>
        </div>

        <p>Xin chào <strong>{{ $user->full_name }}</strong>,</p>

        <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Seniks Events</strong>!</p>

        <p>Để hoàn tất quá trình đăng ký và bắt đầu sử dụng các tính năng của chúng tôi, vui lòng xác thực địa chỉ email của bạn bằng cách nhấp vào nút bên dưới:</p>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button" style="background-color: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; display: inline-block;">Xác thực email</a>
        </div>

        <p>Hoặc sao chép và dán liên kết sau vào trình duyệt của bạn:</p>
        <p style="word-break: break-all; color: #4f46e5; background-color: #f3f4f6; padding: 10px; border-radius: 4px;">
            {{ $verificationUrl }}
        </p>

        <div class="warning">
            <strong>Lưu ý:</strong> Liên kết này sẽ hết hạn sau 7 ngày. Nếu bạn không yêu cầu tạo tài khoản này, vui lòng bỏ qua email này.
        </div>

        <p>Nếu bạn gặp bất kỳ vấn đề nào, vui lòng liên hệ với chúng tôi qua email hỗ trợ.</p>

        <p>Trân trọng,<br>
        <strong>Đội ngũ Seniks Events</strong></p>

        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời email này.</p>
            <p>&copy; {{ date('Y') }} Seniks Events. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>

