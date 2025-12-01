<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends WelcomeController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Đăng ký tài khoản mới
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            // Validation đã được thực hiện tự động bởi RegisterUserRequest
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            // Gán role attendee mặc định
            $user->roles()->attach(3); // role_id = 3 (attendee)

            // Gửi thông báo chào mừng
            $this->notificationService->createNotification($user->user_id, 'Chào mừng bạn đến với Events Management!', 'Tài khoản của bạn đã được tạo thành công. Hãy khám phá các sự kiện thú vị và tham gia ngay!', 'success');

            return redirect()->route('auth.show-login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.');
        } catch (\Exception $e) {
            Log::error('User registration failed: '.$e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đăng ký thất bại. Vui lòng thử lại.');
        }
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', 'Thông tin đăng nhập không hợp lệ.');
        }

        // Laravel sẽ tự động check password_hash field
        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->back()->withInput()->with('error', 'Email hoặc mật khẩu không đúng.');
        }

        return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Đăng xuất thành công!');
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me()
    {
        $user = Auth::user();
        $user->load('roles');
        return view('auth.me', compact('user'))
            ->with('success', 'Lấy thông tin user thành công!');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi email reset password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', 'Email không hợp lệ hoặc chưa được đăng ký.');
        }

        // TODO: Implement password reset logic
        return redirect()->back()->with('success', 'Email đặt lại mật khẩu đã được gửi.');
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return redirect()->back()->with('warning', 'Email đã được xác thực trước đó.');
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Xác thực email thành công!');
    }
}
