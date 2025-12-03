<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin vé - Seniks Events</title>
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
        .ticket-info {
            background-color: #f9fafb;
            border: 2px solid #4f46e5;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 200px;
            height: auto;
            border: 4px solid #e5e7eb;
            border-radius: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #1f2937;
            font-weight: 500;
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
        }
        .button:hover {
            background-color: #4338ca;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Seniks Events</div>
            <h1 style="color: #1f2937; margin: 0;">Thông tin vé của bạn</h1>
        </div>

        <p>Xin chào <strong>{{ $ticket->attendee->full_name }}</strong>,</p>

        <p>Cảm ơn bạn đã tham gia sự kiện <strong>{{ $ticket->ticketType->event->title ?? 'N/A' }}</strong>!</p>

        <div class="ticket-info">
            <h2 style="color: #4f46e5; margin-top: 0;">Chi tiết vé</h2>
            
            <div class="info-row">
                <span class="info-label">Sự kiện:</span>
                <span class="info-value">{{ $ticket->ticketType->event->title ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Loại vé:</span>
                <span class="info-value">{{ $ticket->ticketType->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số lượng:</span>
                <span class="info-value">{{ $ticket->quantity ?? 1 }} vé</span>
            </div>
            <div class="info-row">
                <span class="info-label">Giá vé (đơn vị):</span>
                <span class="info-value">{{ number_format($ticket->ticketType->price ?? 0, 0, ',', '.') }} đ</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tổng tiền:</span>
                <span class="info-value" style="font-weight: 700; color: #4f46e5;">{{ number_format(($ticket->ticketType->price ?? 0) * ($ticket->quantity ?? 1), 0, ',', '.') }} đ</span>
            </div>
            @if($ticket->payment && $ticket->payment->paymentMethod)
            <div class="info-row">
                <span class="info-label">Phương thức thanh toán:</span>
                <span class="info-value">{{ $ticket->payment->paymentMethod->name ?? 'N/A' }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Thời gian:</span>
                <span class="info-value">
                    {{ $ticket->ticketType->event->start_time->format('d/m/Y H:i') ?? 'N/A' }} - 
                    {{ $ticket->ticketType->event->end_time->format('d/m/Y H:i') ?? 'N/A' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa điểm:</span>
                <span class="info-value">{{ $ticket->ticketType->event->location->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mã QR:</span>
                <span class="info-value" style="font-family: monospace;">{{ $qrCode }}</span>
            </div>
            @if(($ticket->quantity ?? 1) > 1)
            <div class="info-row" style="background-color: #fef3c7; padding: 10px; border-radius: 4px; margin-top: 10px;">
                <span class="info-label" style="color: #92400e; font-weight: 700;">Lưu ý:</span>
                <span class="info-value" style="color: #92400e;">Bạn đã mua {{ $ticket->quantity }} vé. Mã QR này dùng chung cho tất cả {{ $ticket->quantity }} vé.</span>
            </div>
            @endif
        </div>

        <div class="qr-code">
            <h3 style="color: #1f2937; margin-bottom: 15px;">QR Code vé</h3>
            <img src="{{ $qrImageUrl }}" alt="QR Code" />
            <p style="color: #6b7280; font-size: 14px; margin-top: 10px;">
                @if(($ticket->quantity ?? 1) > 1)
                    Hiển thị QR code này tại sự kiện để check-in cho {{ $ticket->quantity }} vé
                @else
                    Hiển thị QR code này tại sự kiện để check-in
                @endif
            </p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('tickets.show', $ticket->ticket_id) }}" class="button" style="background-color: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; display: inline-block;">Xem chi tiết vé</a>
        </div>

        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; border-radius: 4px;">
            <strong>Lưu ý quan trọng:</strong>
            <ul style="margin: 8px 0; padding-left: 20px;">
                <li>Vui lòng mang theo QR code này khi tham gia sự kiện. Bạn có thể hiển thị trên điện thoại hoặc in ra giấy.</li>
                @if(($ticket->quantity ?? 1) > 1)
                <li>Bạn đã mua {{ $ticket->quantity }} vé. Mã QR này dùng chung cho tất cả {{ $ticket->quantity }} vé trong đơn hàng này.</li>
                <li>Khi check-in, nhân viên sẽ quét QR code một lần và xác nhận số lượng vé bạn đã mua.</li>
                @endif
                <li>Vé đã được thanh toán và xác nhận thành công.</li>
            </ul>
        </div>

        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>

        <p>Trân trọng,<br>
        <strong>Đội ngũ Seniks Events</strong></p>

        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời email này.</p>
            <p>&copy; {{ date('Y') }} Seniks Events. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
