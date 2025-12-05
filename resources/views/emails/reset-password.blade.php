<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Seniks Events</h1>
    </div>
    
    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
        <h2 style="color: #333; margin-top: 0;">Đặt lại mật khẩu</h2>
        
        <p>Xin chào <strong>{{ $user->full_name }}</strong>,</p>
        
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" 
               style="display: inline-block; background: #667eea; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                Đặt lại mật khẩu
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">Hoặc copy và dán link sau vào trình duyệt:</p>
        <p style="word-break: break-all; color: #667eea; font-size: 12px; background: #f5f5f5; padding: 10px; border-radius: 5px;">{{ $resetUrl }}</p>
        
        <p style="color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <strong>Lưu ý:</strong> Link này sẽ hết hạn sau 1 giờ. Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.
        </p>
        
        <p style="color: #999; font-size: 12px; margin-top: 10px;">
            Nếu bạn gặp vấn đề, vui lòng liên hệ với chúng tôi.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>&copy; {{ date('Y') }} Seniks Events. Tất cả quyền được bảo lưu.</p>
    </div>
</body>
</html>
