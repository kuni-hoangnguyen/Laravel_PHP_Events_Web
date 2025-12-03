# TÀI LIỆU YÊU CẦU CHỨC NĂNG
## HỆ THỐNG QUẢN LÝ SỰ KIỆN (EVENTS MANAGEMENT SYSTEM)

**Phiên bản:** 2.1  
**Ngày cập nhật:** 2025-12-03  
**Framework:** Laravel 12.0 (PHP 8.2+)

---

## 1. TỔNG QUAN HỆ THỐNG

Hệ thống quản lý sự kiện là một nền tảng web cho phép:
- **Người tổ chức (Organizer)** tạo và quản lý sự kiện
- **Người tham gia (Attendee)** tìm kiếm, mua vé và tham gia sự kiện
- **Quản trị viên (Admin)** quản lý toàn bộ hệ thống, duyệt sự kiện và xử lý hoàn tiền

---

## 2. QUẢN LÝ NGƯỜI DÙNG VÀ PHÂN QUYỀN

### 2.1. Đăng ký tài khoản
- **FR-001:** Người dùng có thể đăng ký tài khoản mới với thông tin:
  - Họ và tên (bắt buộc, tối thiểu 2 ký tự)
  - Email (bắt buộc, định dạng hợp lệ, duy nhất)
  - Mật khẩu (bắt buộc, được hash bằng bcrypt)
  - Số điện thoại (tùy chọn)
- **FR-002:** Sau khi đăng ký, tài khoản tự động được gán vai trò "Attendee"
- **FR-003:** Hệ thống gửi thông báo chào mừng sau khi đăng ký thành công

### 2.2. Đăng nhập/Đăng xuất
- **FR-004:** Người dùng có thể đăng nhập bằng email và mật khẩu
- **FR-005:** Hệ thống xác thực thông tin đăng nhập và tạo session
- **FR-006:** Người dùng có thể đăng xuất, hệ thống sẽ hủy session
- **FR-007:** Hệ thống hỗ trợ "Remember Me" để duy trì đăng nhập

### 2.3. Quên mật khẩu
- **FR-008:** Người dùng có thể yêu cầu đặt lại mật khẩu qua email
- **FR-009:** Hệ thống kiểm tra email có tồn tại trong hệ thống
- **FR-010:** Hệ thống gửi email chứa link đặt lại mật khẩu với signed URL
- **FR-010a:** Token reset password có thời hạn 1 giờ
- **FR-010b:** Token được hash và lưu trong bảng `password_reset_tokens`
- **FR-010c:** Sau khi đặt lại mật khẩu thành công, token sẽ bị xóa

### 2.4. Xác thực Email
- **FR-011:** Người dùng có thể xác thực email của mình
- **FR-012:** Hệ thống gửi email xác thực sau khi đăng ký
- **FR-013:** Email xác thực chứa signed URL để bảo mật
- **FR-014:** Người dùng có thể gửi lại email xác thực từ trang hồ sơ
- **FR-015:** Rate limiting cho việc gửi lại email: 3 requests/phút
- **FR-016:** Một số chức năng yêu cầu email đã được xác thực (mua vé, đánh giá)
- **FR-017:** Hệ thống hiển thị banner cảnh báo nếu email chưa được xác thực

### 2.5. Phân quyền người dùng
- **FR-013:** Hệ thống có 3 vai trò chính:
  - **Admin:** Toàn quyền quản lý hệ thống
  - **Organizer:** Tạo và quản lý sự kiện
  - **Attendee:** Mua vé và tham gia sự kiện
- **FR-014:** Một người dùng có thể có nhiều vai trò
- **FR-015:** Admin có thể cập nhật vai trò của người dùng khác

### 2.6. Quản lý hồ sơ
- **FR-016:** Người dùng có thể xem thông tin tài khoản của mình
- **FR-017:** Người dùng có thể cập nhật thông tin cá nhân (tên, số điện thoại, avatar)
- **FR-018:** Người dùng có thể đổi mật khẩu:
  - Phải nhập mật khẩu hiện tại
  - Mật khẩu mới phải có ít nhất 8 ký tự
  - Mật khẩu mới phải khác với mật khẩu cũ
  - Phải xác nhận mật khẩu mới

---

## 3. QUẢN LÝ SỰ KIỆN

### 3.1. Tạo sự kiện (Organizer)
- **FR-019:** Organizer có thể tạo sự kiện mới với thông tin:
  - Tiêu đề sự kiện (bắt buộc, tối đa 200 ký tự)
  - Mô tả chi tiết
  - Thời gian bắt đầu (bắt buộc, phải sau thời điểm hiện tại)
  - Thời gian kết thúc (bắt buộc, phải sau thời gian bắt đầu)
  - Danh mục sự kiện (bắt buộc, chọn từ danh sách có sẵn)
  - Địa điểm (bắt buộc, chọn từ danh sách có sẵn)
  - Số lượng người tham gia tối đa
  - Banner/ảnh đại diện (URL)
- **FR-020:** Sự kiện mới tạo có trạng thái "pending" và cần được admin duyệt
- **FR-021:** Organizer có thể tạo nhiều loại vé (ticket types) khi tạo sự kiện:
  - Tên loại vé, giá, số lượng, mô tả
  - Thời gian bắt đầu/kết thúc bán vé
  - Trạng thái active/inactive
- **FR-022:** Organizer có thể xem danh sách sự kiện do mình tạo
- **FR-023:** Hệ thống tự động gửi thông báo cho tất cả admin khi có sự kiện mới cần duyệt

### 3.2. Cập nhật sự kiện
- **FR-024:** Chủ sở hữu sự kiện có thể cập nhật thông tin sự kiện
- **FR-025:** Chỉ có thể cập nhật khi sự kiện ở trạng thái cho phép chỉnh sửa
- **FR-026:** Không thể cập nhật sự kiện đã bắt đầu hoặc đã kết thúc
- **FR-027:** Organizer có thể cập nhật ticket types:
  - Thêm ticket type mới
  - Sửa ticket type đã tồn tại (chỉ khi chưa có vé nào được bán)
  - Xóa ticket type (chỉ khi chưa có vé nào được bán)
  - Khi cập nhật total_quantity, hệ thống tự động tính lại remaining_quantity

### 3.3. Xóa sự kiện
- **FR-028:** Chủ sở hữu sự kiện có thể xóa sự kiện (soft delete)
- **FR-029:** Chỉ có thể xóa khi sự kiện ở trạng thái cho phép
- **FR-030:** Admin có thể xóa sự kiện (soft delete) và ghi log hành động

### 3.4. Yêu cầu hủy sự kiện
- **FR-031:** Organizer có thể yêu cầu hủy sự kiện với lý do (tối thiểu 10 ký tự, tối đa 1000 ký tự)
- **FR-032:** Mỗi sự kiện chỉ có thể có một yêu cầu hủy đang pending
- **FR-033:** Hệ thống gửi thông báo cho tất cả admin khi có yêu cầu hủy
- **FR-034:** Admin có thể duyệt hoặc từ chối yêu cầu hủy:
  - Duyệt: Cập nhật status = 'cancelled', gửi thông báo cho organizer và tất cả attendees
  - Từ chối: Xóa yêu cầu hủy, gửi thông báo cho organizer
- **FR-035:** Tất cả hành động liên quan đến hủy sự kiện đều được ghi log

### 3.5. Duyệt sự kiện (Admin)
- **FR-036:** Admin có thể xem danh sách tất cả sự kiện (đã duyệt/chưa duyệt/từ chối/yêu cầu hủy)
- **FR-037:** Admin có thể lọc sự kiện theo trạng thái (pending, approved, rejected, cancellation)
- **FR-038:** Admin có thể duyệt sự kiện, khi đó:
  - Trạng thái `approved` = 1
  - Lưu thời gian duyệt và người duyệt
  - Gửi thông báo cho organizer
  - Ghi log hành động
- **FR-039:** Admin có thể từ chối sự kiện với lý do:
  - Trạng thái `approved` = -1
  - Lưu lý do từ chối
  - Gửi thông báo cho organizer kèm lý do
  - Ghi log hành động

### 3.6. Xem danh sách sự kiện (Public)
- **FR-040:** Người dùng (kể cả chưa đăng nhập) có thể xem danh sách sự kiện đã được duyệt
- **FR-041:** Hệ thống hỗ trợ tìm kiếm và lọc sự kiện theo:
  - Danh mục (category)
  - Địa điểm (location)
  - Từ khóa (tìm trong tiêu đề)
  - Khoảng thời gian (start_date, end_date)
  - Trạng thái (upcoming, ongoing, ended)
- **FR-042:** Danh sách sự kiện được phân trang (12 sự kiện/trang)
- **FR-043:** Sự kiện được sắp xếp theo thời gian bắt đầu (sắp diễn ra trước)

### 3.7. Xem chi tiết sự kiện
- **FR-044:** Người dùng có thể xem chi tiết sự kiện bao gồm:
  - Thông tin cơ bản (tiêu đề, mô tả, thời gian, địa điểm)
  - Thông tin người tổ chức
  - Danh sách loại vé và giá (chỉ hiển thị vé đang bán, còn số lượng)
  - Đánh giá và bình luận từ người tham gia
  - Tags liên quan
  - Số lượng người đã tham gia
  - Trạng thái sự kiện (upcoming, ongoing, ended, cancelled)

---

## 4. QUẢN LÝ VÉ (TICKETS)

### 4.1. Loại vé (Ticket Types)
- **FR-033:** Mỗi sự kiện có thể có nhiều loại vé với:
  - Tên loại vé
  - Giá vé
  - Tổng số lượng vé
  - Số lượng vé còn lại
  - Thời gian bắt đầu/kết thúc bán vé
  - Mô tả
  - Trạng thái active/inactive
- **FR-034:** Hệ thống tự động cập nhật số lượng vé còn lại khi có người mua

### 4.2. Mua vé
- **FR-045:** Người dùng đã xác thực email có thể mua vé cho sự kiện
- **FR-046:** Quy trình mua vé:
  - Chọn loại vé (chỉ hiển thị vé đang bán, còn số lượng)
  - Chọn số lượng (tối đa 10 vé/lần)
  - Chọn phương thức thanh toán (tiền mặt, thẻ tín dụng, chuyển khoản, ví điện tử, QR code)
  - Xác nhận mua
- **FR-047:** Hệ thống kiểm tra:
  - Sự kiện có đang trong thời gian bán vé không
  - Loại vé còn đủ số lượng không
  - Người dùng đã xác thực email chưa
  - Loại vé có đang active không
- **FR-048:** Khi mua vé thành công:
  - Tạo ticket với quantity (số lượng vé) và QR code duy nhất
  - Tạo payment record với trạng thái phù hợp:
    - Tiền mặt: status = "failed" (chờ xác nhận), ticket payment_status = "pending"
    - Các phương thức khác: status = "failed" (chờ xử lý)
  - Giảm số lượng vé còn lại (với xử lý lost update)
  - Gửi thông báo cho organizer (nếu thanh toán tiền mặt)
  - Ghi log hành động
- **FR-049:** Hệ thống áp dụng rate limiting (10 requests/phút) để tránh spam
- **FR-050:** Xử lý Lost Update khi mua vé:
  - Sử dụng pessimistic locking (`lockForUpdate()`) để khóa ticket type
  - Atomic update với điều kiện WHERE để đảm bảo số lượng đủ
  - Rollback transaction nếu số lượng không đủ
  - Thông báo lỗi rõ ràng khi có người khác mua trước đó
- **FR-051:** Mỗi ticket có thể chứa nhiều vé (quantity > 1), tất cả cùng một QR code

### 4.3. Xem vé đã mua
- **FR-052:** Người dùng có thể xem danh sách vé đã mua của mình
- **FR-053:** Danh sách vé được sắp xếp theo thời gian mua (mới nhất trước)
- **FR-054:** Vé chờ thanh toán được làm mờ và không thể xem chi tiết
- **FR-055:** Người dùng có thể xem chi tiết từng vé (chỉ khi đã thanh toán) bao gồm:
  - Thông tin sự kiện
  - Loại vé
  - Số lượng vé (quantity)
  - Thời gian mua
  - Trạng thái thanh toán (pending, paid, used, cancelled)
  - QR code (chỉ hiển thị khi đã thanh toán)
  - Thông tin thanh toán
  - Thời gian check-in (nếu đã check-in)

### 4.4. QR Code cho vé
- **FR-045:** Mỗi vé có một QR code duy nhất được tạo tự động khi mua
- **FR-046:** Người dùng có thể xem và tải QR code của vé mình (chỉ khi đã thanh toán)
- **FR-047:** QR code được tạo bằng format: `TICKET_{ticket_id}_{unique_id}`
- **FR-048:** QR code được hiển thị dưới dạng hình ảnh (PNG) qua API bên thứ ba
- **FR-049:** QR code được tạo trong TicketController và QRCodeService (không tạo trong view)

---

## 5. THANH TOÁN (PAYMENTS)

### 5.1. Tạo thanh toán
- **FR-050:** Khi mua vé, hệ thống tự động tạo payment record với:
  - Số tiền (từ giá vé)
  - Phương thức thanh toán
  - Trạng thái phù hợp:
    - Tiền mặt: status = "failed" (chờ xác nhận)
    - Các phương thức khác: (sẽ thêm sau)
  - Transaction ID duy nhất
- **FR-051:** Payment được liên kết với ticket tương ứng
- **FR-052:** Thanh toán tiền mặt yêu cầu organizer xác nhận trước khi ticket được kích hoạt

### 5.2. Xác nhận thanh toán
- **FR-056:** Xác nhận thanh toán tiền mặt (Organizer):
  - Organizer có thể xem danh sách thanh toán tiền mặt chờ xác nhận
  - Organizer xác nhận nhận tiền → cập nhật:
    - Payment status = "success"
    - Payment paid_at = thời gian hiện tại
    - Ticket payment_status = "paid"
  - Gửi thông báo cho người mua: "Mua vé thành công"
  - Gửi email với thông tin vé và QR code cho người mua
  - Ghi log hành động
- **FR-057:** Từ chối thanh toán tiền mặt (Organizer):
  - Organizer có thể từ chối thanh toán tiền mặt
  - Cập nhật ticket payment_status = "cancelled"
  - Tăng lại remaining_quantity cho ticket type
  - Gửi thông báo cho người mua về việc từ chối
  - Ghi log hành động
- **FR-058:** Sau khi thanh toán thành công qua payment gateway (tương lai):
  - Cập nhật trạng thái payment = "success"
  - Cập nhật thời gian thanh toán (paid_at)
  - Cập nhật trạng thái ticket = "paid"
  - Lưu transaction reference từ gateway
- **FR-059:** Hệ thống hỗ trợ webhook từ payment gateway (đã định nghĩa route nhưng chưa implement)

### 5.3. Xem lịch sử thanh toán
- **FR-056:** Người dùng có thể xem danh sách các giao dịch thanh toán của mình
- **FR-057:** Danh sách được sắp xếp theo thời gian (mới nhất trước)
- **FR-058:** Người dùng có thể xem chi tiết từng giao dịch:
  - Thông tin vé
  - Số tiền
  - Phương thức thanh toán
  - Trạng thái (Thành công, Thất bại, Đã hoàn tiền, Chờ xác nhận cho tiền mặt)
  - Thời gian thanh toán (paid_at hoặc purchase_time)
  - Transaction ID

### 5.4. Hoàn tiền (Refunds)
- **FR-059:** Người dùng có thể yêu cầu hoàn tiền cho payment đã thành công
- **FR-060:** Yêu cầu hoàn tiền bao gồm:
  - Payment ID
  - Lý do hoàn tiền (bắt buộc, tối đa 255 ký tự)
  - Trạng thái mặc định: "pending"
- **FR-061:** Mỗi payment chỉ có thể có một yêu cầu hoàn tiền đang pending
- **FR-062:** Admin có thể xử lý yêu cầu hoàn tiền:
  - Duyệt (approved): Cập nhật trạng thái refund = "approved"
  - Từ chối (rejected): Cập nhật trạng thái refund = "rejected"
  - Lưu ghi chú của admin
  - Ghi log hành động
- **FR-063:** Hệ thống gửi thông báo cho người dùng khi trạng thái refund thay đổi

---

## 6. ĐÁNH GIÁ VÀ BÌNH LUẬN (REVIEWS)

### 6.1. Tạo đánh giá
- **FR-057:** Người dùng đã tham gia sự kiện có thể đánh giá sau khi sự kiện kết thúc
- **FR-058:** Đánh giá bao gồm:
  - Điểm số (1-5 sao, bắt buộc)
  - Bình luận (tùy chọn, tối đa 500 ký tự)
- **FR-059:** Mỗi người dùng chỉ có thể đánh giá một lần cho mỗi sự kiện
- **FR-060:** Hệ thống kiểm tra sự kiện đã kết thúc trước khi cho phép đánh giá

### 6.2. Cập nhật/Xóa đánh giá
- **FR-061:** Người dùng có thể cập nhật đánh giá của mình
- **FR-062:** Người dùng có thể xóa đánh giá của mình

### 6.3. Xem đánh giá
- **FR-063:** Người dùng có thể xem danh sách đánh giá của sự kiện
- **FR-064:** Hệ thống tính điểm trung bình từ tất cả đánh giá
- **FR-065:** Đánh giá được sắp xếp theo thời gian (mới nhất trước)

---

## 7. YÊU THÍCH (FAVORITES)

### 7.1. Thêm/Xóa yêu thích
- **FR-066:** Người dùng đã đăng nhập có thể thêm sự kiện vào danh sách yêu thích
- **FR-067:** Người dùng có thể xóa sự kiện khỏi danh sách yêu thích
- **FR-068:** Người dùng có thể toggle (thêm/xóa) yêu thích một cách nhanh chóng
- **FR-069:** Mỗi sự kiện chỉ có thể được thêm vào yêu thích một lần

### 7.2. Xem danh sách yêu thích
- **FR-070:** Người dùng có thể xem danh sách sự kiện đã yêu thích
- **FR-071:** Danh sách được sắp xếp theo thời gian thêm (mới nhất trước)

### 7.3. Kiểm tra trạng thái yêu thích
- **FR-072:** Người dùng có thể kiểm tra một sự kiện có trong danh sách yêu thích không

### 7.4. Gợi ý sự kiện
- **FR-073:** Hệ thống gợi ý sự kiện dựa trên:
  - Danh mục của các sự kiện đã yêu thích
  - Sự kiện chưa được yêu thích
  - Sự kiện sắp diễn ra
  - Sắp xếp theo số lượng yêu thích
- **FR-074:** Nếu người dùng chưa có yêu thích, hệ thống gợi ý các sự kiện phổ biến nhất

---

## 8. THÔNG BÁO (NOTIFICATIONS)

### 8.1. Tạo thông báo
- **FR-075:** Hệ thống tự động tạo thông báo cho các sự kiện:
  - Đăng ký tài khoản thành công
  - Sự kiện được duyệt/từ chối (cho organizer)
  - Sự kiện mới cần duyệt (cho admin)
  - Yêu cầu hủy sự kiện (cho admin)
  - Yêu cầu hủy được duyệt/từ chối (cho organizer)
  - Sự kiện bị hủy (cho tất cả attendees)
  - Thanh toán tiền mặt chờ xác nhận (cho organizer)
  - Thanh toán được xác nhận/từ chối (cho người mua)
  - Mua vé thành công (sau khi thanh toán được xác nhận)
  - Yêu cầu hoàn tiền (cho admin và organizer)
  - Trạng thái hoàn tiền thay đổi (cho người yêu cầu)
  - Nhắc nhở sự kiện sắp diễn ra (TODO: chưa implement scheduler)
- **FR-076:** Mỗi thông báo có:
  - Tiêu đề
  - Nội dung
  - Loại (success, warning, info, error)
  - Trạng thái đã đọc/chưa đọc (read_at)
  - Action URL (link đến trang liên quan)
- **FR-077:** Thông báo có thể được click để điều hướng đến trang liên quan

### 8.2. Xem thông báo
- **FR-078:** Người dùng có thể xem danh sách thông báo của mình
- **FR-079:** Danh sách được sắp xếp theo notification_id (mới nhất trước)
- **FR-080:** Hệ thống hiển thị số lượng thông báo chưa đọc:
  - Badge đỏ trên icon thông báo
  - Badge trên link "Thông báo" trong dropdown
- **FR-081:** Hệ thống hiển thị toast notification khi có thông báo mới
- **FR-082:** Toast notifications xuất hiện ở góc dưới bên phải
- **FR-083:** Người dùng có thể click vào thông báo để điều hướng đến trang liên quan (action_url)

### 8.3. Đánh dấu đã đọc
- **FR-084:** Người dùng có thể đánh dấu một thông báo là đã đọc
- **FR-085:** Người dùng có thể đánh dấu tất cả thông báo là đã đọc
- **FR-086:** Trạng thái đã đọc được lưu trong field `is_read` (boolean)
- **FR-087:** Khi click vào thông báo, hệ thống tự động đánh dấu đã đọc trước khi redirect

### 8.4. Dọn dẹp thông báo
- **FR-088:** Hệ thống có thể tự động xóa thông báo cũ hơn 30 ngày (cleanup function)

---

## 9. ORGANIZER DASHBOARD

### 9.1. Dashboard Organizer
- **FR-122:** Organizer có thể xem dashboard với thống kê:
  - Tổng số sự kiện
  - Số sự kiện đã duyệt/chờ duyệt
  - Tổng số thanh toán tiền mặt chờ xác nhận
  - Tổng số vé đã bán
  - Tổng doanh thu
  - Sự kiện sắp diễn ra (7 ngày tới)
  - Sự kiện có thanh toán chờ xác nhận (với badge số lượng)
  - Sự kiện gần đây
- **FR-123:** Dashboard có quick links đến các trang quản lý

### 9.2. Quản lý thanh toán tiền mặt
- **FR-124:** Organizer có thể xem danh sách thanh toán tiền mặt chờ xác nhận cho từng sự kiện
- **FR-125:** Organizer có thể xác nhận nhận tiền:
  - Cập nhật payment status = "success"
  - Cập nhật payment paid_at = now()
  - Cập nhật ticket payment_status = "paid"
  - Gửi thông báo và email cho người mua (với QR code)
  - Ghi log hành động
- **FR-126:** Organizer có thể từ chối thanh toán tiền mặt:
  - Cập nhật ticket payment_status = "cancelled"
  - Tăng lại remaining_quantity cho ticket type
  - Gửi thông báo cho người mua
  - Ghi log hành động
- **FR-127:** Badge hiển thị số lượng thanh toán chờ xác nhận:
  - Trên link "Sự kiện của tôi" trong dropdown
  - Trên nút "Thanh toán tiền mặt chờ xác nhận" của mỗi sự kiện
- **FR-128:** Dashboard hiển thị danh sách sự kiện có thanh toán chờ xác nhận với số lượng

## 10. CHECK-IN BẰNG QR CODE

### 10.1. Check-in cho người tham gia
- **FR-127:** Organizer có thể check-in người tham gia tại sự kiện bằng cách quét QR code
- **FR-128:** Hệ thống kiểm tra:
  - QR code có hợp lệ không
  - QR code có thuộc sự kiện này không
  - Vé đã thanh toán chưa (payment_status = "paid")
  - Vé đã được check-in chưa (checked_in_at = null)
  - Sự kiện đã đến thời gian check-in chưa (cho phép check-in từ 2 giờ trước khi bắt đầu)
- **FR-129:** Sau khi check-in thành công:
  - Cập nhật trạng thái ticket payment_status = "used"
  - Lưu thời gian check-in (checked_in_at = now())
  - Hiển thị thông tin người tham gia và số lượng vé
  - Ghi log hành động
- **FR-130:** Hệ thống hiển thị thông báo nếu:
  - QR code không hợp lệ hoặc không thuộc sự kiện
  - Vé chưa thanh toán
  - Vé đã được check-in trước đó (hiển thị thời gian check-in)
  - Chưa đến thời gian check-in (hiển thị thời gian bắt đầu check-in)

### 10.2. Quản lý check-in cho Organizer
- **FR-131:** Organizer có thể xem trang scanner QR code cho sự kiện của mình
- **FR-132:** Organizer có thể quét và check-in vé cho người tham gia qua giao diện scanner
- **FR-133:** Organizer có thể xem thống kê check-in:
  - Tổng số vé đã bán (tính theo quantity)
  - Số vé đã check-in (ticket payment_status = "used")
  - Số vé chưa check-in (ticket payment_status = "paid" và checked_in_at = null)
  - Tỷ lệ check-in (%)
- **FR-134:** Organizer có thể xem danh sách người tham gia đã mua vé (không chỉ đã check-in):
  - Thông tin người mua (tên, email)
  - Loại vé, số lượng, giá
  - Thời gian mua
  - Trạng thái check-in
  - Tổng doanh thu của sự kiện

---

## 11. QUẢN TRỊ HỆ THỐNG (ADMIN)

### 10.1. Dashboard
- **FR-090:** Admin có thể xem dashboard với layout riêng (sidebar navigation):
  - Top navigation bar với gradient indigo
  - Sidebar với menu quản lý
  - Dropdown chỉ hiển thị "Dashboard" trong phần Admin
- **FR-091:** Dashboard hiển thị thống kê:
  - Tổng số người dùng
  - Tổng số sự kiện
  - Số sự kiện đang chờ duyệt
  - Tổng số vé đã bán
  - Tổng doanh thu
  - 5 sự kiện gần đây nhất
  - 5 người dùng mới nhất

### 10.2. Quản lý sự kiện
- **FR-092:** Admin có thể xem danh sách tất cả sự kiện
- **FR-093:** Admin có thể lọc sự kiện theo trạng thái (pending, approved, rejected)
- **FR-094:** Admin có thể duyệt/từ chối sự kiện (xem FR-027, FR-028)

### 10.3. Quản lý thanh toán
- **FR-095:** Admin có thể xem danh sách tất cả thanh toán
- **FR-096:** Admin có thể lọc thanh toán theo trạng thái (success, failed, refunded)
- **FR-097:** Admin có thể tìm kiếm theo Transaction ID, tên, email
- **FR-098:** Admin có thể xem chi tiết từng thanh toán

### 10.4. Quản lý vé
- **FR-099:** Admin có thể xem danh sách tất cả vé
- **FR-100:** Admin có thể lọc vé theo trạng thái thanh toán (pending, paid, used, cancelled)
- **FR-101:** Admin có thể tìm kiếm theo QR code, tên, email, sự kiện
- **FR-102:** Admin có thể xem chi tiết từng vé

### 10.5. Quản lý danh mục
- **FR-103:** Admin có thể xem danh sách tất cả danh mục
- **FR-104:** Admin có thể tạo danh mục mới (tên, mô tả)
- **FR-105:** Admin có thể sửa danh mục
- **FR-106:** Admin có thể xóa danh mục (nếu không có sự kiện sử dụng)
- **FR-107:** Hiển thị số lượng sự kiện của mỗi danh mục

### 10.6. Quản lý địa điểm
- **FR-108:** Admin có thể xem danh sách tất cả địa điểm
- **FR-109:** Admin có thể tạo địa điểm mới (tên, địa chỉ, thành phố, sức chứa)
- **FR-110:** Admin có thể sửa địa điểm
- **FR-111:** Admin có thể xóa địa điểm (nếu không có sự kiện sử dụng)
- **FR-112:** Hiển thị số lượng sự kiện của mỗi địa điểm

### 10.7. Quản lý người dùng
- **FR-113:** Admin có thể xem danh sách tất cả người dùng
- **FR-114:** Admin có thể tìm kiếm người dùng theo tên hoặc email
- **FR-115:** Admin có thể cập nhật vai trò của người dùng
- **FR-116:** Admin có thể xóa người dùng (trừ admin)
- **FR-117:** Mọi thao tác quản lý người dùng đều được ghi log

### 10.8. Quản lý hoàn tiền
- **FR-118:** Admin có thể xem danh sách tất cả yêu cầu hoàn tiền
- **FR-119:** Admin có thể lọc theo trạng thái (pending, approved, rejected)
- **FR-120:** Admin có thể xử lý yêu cầu hoàn tiền (xem FR-062)

### 10.9. Xem log hành động
- **FR-121:** Admin có thể xem log tất cả hành động quản trị:
  - Hành động (approve_event, reject_event, update_user_role, delete_user, process_refund, create_category, update_category, delete_category, create_location, update_location, delete_location)
  - Bảng bị ảnh hưởng
  - ID bản ghi
  - Dữ liệu trước và sau thay đổi
  - Người thực hiện
  - Thời gian

---

## 12. BẢO MẬT VÀ BẢO VỆ

### 11.1. Xác thực và phân quyền
- **FR-102:** Tất cả routes yêu cầu đăng nhập đều được bảo vệ bởi middleware `auth`
- **FR-103:** Routes chỉ dành cho admin được bảo vệ bởi middleware `admin`
- **FR-104:** Routes chỉ dành cho organizer được bảo vệ bởi middleware `organizer`
- **FR-105:** Routes chỉ dành cho chủ sở hữu được bảo vệ bởi middleware tương ứng:
  - `event.owner`: Chỉ chủ sở hữu sự kiện
  - `ticket.owner`: Chỉ chủ sở hữu vé
  - `payment.verify`: Chỉ chủ sở hữu payment

### 11.2. Rate Limiting
- **FR-106:** Hệ thống áp dụng rate limiting cho:
  - Quên mật khẩu: 3 requests/5 phút
  - Mua vé: 10 requests/phút
- **FR-107:** Rate limiting được áp dụng qua middleware `custom.throttle`

### 11.3. Validation
- **FR-108:** Tất cả dữ liệu đầu vào đều được validate:
  - Form requests (RegisterUserRequest, StoreEventRequest)
  - Validator trong controllers
  - Database constraints (email format, name length)

### 11.4. Event Status Middleware
- **FR-109:** Hệ thống kiểm tra trạng thái sự kiện trước khi cho phép thao tác:
  - `event.status:buy_ticket`: Chỉ cho phép mua vé khi sự kiện ở trạng thái phù hợp
  - `event.status:review`: Chỉ cho phép đánh giá sau khi sự kiện kết thúc
  - `event.status:edit`: Chỉ cho phép chỉnh sửa khi sự kiện ở trạng thái phù hợp

---

## 13. DỮ LIỆU VÀ BÁO CÁO

### 12.1. Danh mục và địa điểm
- **FR-110:** Hệ thống có danh sách danh mục sự kiện cố định
- **FR-111:** Hệ thống có danh sách địa điểm cố định
- **FR-112:** Người dùng có thể xem danh sách danh mục và địa điểm

### 12.2. Tags sự kiện
- **FR-113:** Sự kiện có thể có nhiều tags để phân loại và tìm kiếm
- **FR-114:** Tags được quản lý qua bảng `event_tags` và `event_tag_map`

### 12.3. Bản đồ sự kiện
- **FR-115:** Sự kiện có thể có nhiều bản đồ (maps) để hiển thị layout địa điểm

---

## 14. GIAO DIỆN NGƯỜI DÙNG

### 14.1. Toast Notifications
- **FR-134:** Hệ thống sử dụng toast notifications thay cho alert truyền thống
- **FR-135:** Toast notifications xuất hiện ở góc dưới bên phải
- **FR-136:** Toast có các loại: success, error, warning, info
- **FR-137:** Toast tự động ẩn sau 5 giây
- **FR-138:** Người dùng có thể đóng toast thủ công
- **FR-139:** Hệ thống sử dụng sessionStorage để tránh hiển thị lại flash message khi quay lại trang

### 14.2. Trang chủ
- **FR-140:** Trang chủ hiển thị thông tin giới thiệu và danh sách sự kiện nổi bật

### 14.3. Responsive Design
- **FR-141:** Giao diện hỗ trợ hiển thị trên nhiều thiết bị (desktop, tablet, mobile)

### 14.4. Views
- **FR-142:** Hệ thống sử dụng Blade templates để render views
- **FR-143:** Các views chính:
  - Authentication (login, register, forgot-password)
  - Events (index, show, my-events, create, edit, dashboard, pending-cash-payments)
  - Tickets (my-tickets, show, purchase)
  - Payments (index, show)
  - Reviews (index, create, edit)
  - Favorites (index, recommendations)
  - Notifications (index)
  - QR Code (ticket QR, scanner, stats, attendees)
  - Admin (dashboard, events, users, payments, tickets, categories, locations, refunds, logs)
- **FR-144:** Admin có layout riêng với sidebar navigation
- **FR-145:** Dropdown được chia theo role:
  - Attendee section: Hồ sơ, Vé của tôi, Thanh toán, Yêu thích, Thông báo
  - Organizer section: Dashboard, Sự kiện của tôi (hiển thị cho organizer và admin)
  - Admin section: Dashboard (chỉ admin, chỉ có Dashboard)

---

## 15. TÍNH NĂNG BỔ SUNG

### 14.1. Coupons
- **FR-120:** Hệ thống có model Coupon nhưng chưa được implement đầy đủ
- **FR-121:** Ticket có thể liên kết với coupon (đã có trong database schema)

### 14.2. Báo cáo sự cố
- **FR-122:** Hệ thống có model IncidentReport và ReviewReport nhưng chưa được implement

### 14.3. Báo cáo hệ thống
- **FR-123:** Hệ thống có model SystemReport nhưng chưa được implement

---

## 16. HẠN CHẾ VÀ TODO

### 15.1. Chức năng chưa hoàn thiện
- **TODO-001:** Webhook từ payment gateway chưa được implement đầy đủ
- **TODO-002:** Scheduler để gửi thông báo nhắc nhở sự kiện sắp diễn ra chưa được implement
- **TODO-003:** Tích hợp payment gateway thực tế (hiện tại chỉ hỗ trợ tiền mặt)
- **TODO-004:** Tính năng coupon chưa được implement đầy đủ (có model nhưng chưa tích hợp vào flow mua vé)

### 15.2. Cải tiến đề xuất
- **ENH-001:** Thêm tính năng tìm kiếm nâng cao (filter theo giá vé, rating, khoảng cách)
- **ENH-002:** Thêm tính năng chia sẻ sự kiện lên mạng xã hội
- **ENH-003:** Thêm tính năng đặt chỗ trước (waitlist) khi hết vé
- **ENH-004:** Thêm tính năng chat/comment trực tiếp trong sự kiện
- **ENH-005:** Thêm tính năng live streaming cho sự kiện
- **ENH-006:** Thêm tính năng báo cáo sự cố và khiếu nại
- **ENH-007:** Thêm tính năng thống kê chi tiết cho organizer
- **ENH-008:** Thêm tính năng export dữ liệu (Excel, PDF)

---

## 17. CÔNG NGHỆ SỬ DỤNG

- **Framework:** Laravel 12.0
- **PHP:** 8.2+
- **Database:** MySQL/MariaDB (có thể dùng SQLite cho development)
- **Authentication:** Laravel Sanctum
- **Frontend:** Blade Templates, JavaScript, CSS
- **QR Code:** API bên thứ ba (qrserver.com)
- **Queue:** Laravel Queue (database driver)

---

## 18. KẾT LUẬN

Hệ thống quản lý sự kiện cung cấp đầy đủ các chức năng cơ bản cho việc:
- Tổ chức và quản lý sự kiện
- Mua bán vé trực tuyến
- Thanh toán và hoàn tiền
- Đánh giá và phản hồi
- Quản trị hệ thống

Hệ thống được thiết kế với kiến trúc rõ ràng, phân quyền chặt chẽ và có khả năng mở rộng trong tương lai.

---

**Tài liệu này được tạo tự động dựa trên phân tích mã nguồn của dự án.**


