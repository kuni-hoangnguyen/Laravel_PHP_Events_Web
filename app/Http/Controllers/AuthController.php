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

            return response()->json(
                [
                    'message' => 'User registered successfully',
                    'user' => $user->load('roles'),
                ],
                201,
            );
        } catch (\Exception $e) {
            Log::error('User registration failed: '.$e->getMessage());

            return response()->json(
                [
                    'message' => 'Registration failed. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                ],
                500,
            );
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
            return response()->json(
                [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        // Laravel sẽ tự động check password_hash field
        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('roles');

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('roles');

        return response()->json([
            'user' => $user,
        ]);
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
            return response()->json(
                [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        // TODO: Implement password reset logic
        return response()->json([
            'message' => 'Password reset email sent',
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified',
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }
}
