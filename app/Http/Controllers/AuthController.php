<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\ImageUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AuthController extends WelcomeController
{
    protected NotificationService $notificationService;

    protected ImageUploadService $imageUploadService;

    public function __construct(NotificationService $notificationService, ImageUploadService $imageUploadService)
    {
        $this->notificationService = $notificationService;
        $this->imageUploadService = $imageUploadService;
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
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            $user->roles()->attach(3);

            try {
                $user->notify(new VerifyEmailNotification);
            } catch (\Exception $e) {
                Log::error('Failed to send verification email: '.$e->getMessage());
            }

            $this->notificationService->createNotification($user->user_id, 'Chào mừng bạn đến với Events Management!', 'Tài khoản của bạn đã được tạo thành công. Vui lòng kiểm tra email để xác thực tài khoản.', 'success');

            return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');
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

        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
            return redirect()->back()->withInput()->with('error', 'Email hoặc mật khẩu không đúng.');
        }

        $user = Auth::user();

        if (! $user->email_verified_at) {
            return redirect()->route('home')
                ->with('warning', 'Vui lòng xác thực email để sử dụng đầy đủ các tính năng. Email xác thực đã được gửi đến địa chỉ email của bạn.');
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
    public function me(Request $request)
    {
        $user = Auth::user();
        $user->load('roles');

        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $validator = Validator::make($request->all(), [
                'full_name' => 'sometimes|string|min:2|max:100',
                'phone' => 'nullable|string|max:20',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->with('error', 'Dữ liệu cập nhật không hợp lệ.');
            }

            $updateData = $request->only(['full_name', 'phone']);

            if ($request->hasFile('avatar')) {
                try {
                    if ($user->avatar_url) {
                        $storageUrl = config('filesystems.disks.public.url') ?: url('/storage');
                        if (strpos($user->avatar_url, $storageUrl) === 0) {
                            $oldPath = str_replace($storageUrl, '', $user->avatar_url);
                            $oldPath = ltrim($oldPath, '/');
                            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                                $this->imageUploadService->delete($oldPath);
                            }
                        }
                    }

                    $avatarPath = $this->imageUploadService->uploadAvatar($request->file('avatar'));
                    $updateData['avatar_url'] = $this->imageUploadService->getUrl($avatarPath);
                } catch (\Exception $e) {
                    Log::error('Failed to upload avatar: '.$e->getMessage());

                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Lỗi khi upload ảnh đại diện: '.$e->getMessage());
                }
            }

            $user->update($updateData);

            return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
        }

        return view('profile.index', compact('user'));
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

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            $token = \Illuminate\Support\Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($user, $token));

            Log::info('Password reset email sent', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            return redirect()->back()->with('success', 'Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư của bạn.');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Không thể gửi email đặt lại mật khẩu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Hiển thị form reset password
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        try {
            Log::info('Password reset form requested', [
                'token' => $token,
                'query_params' => $request->query(),
                'all_params' => $request->all(),
                'url' => $request->fullUrl(),
            ]);

            if (! URL::hasValidSignature($request)) {
                Log::warning('Invalid signature for password reset', [
                    'url' => $request->fullUrl(),
                ]);

                return redirect()->route('password.forgot')
                    ->with('error', 'Link đặt lại mật khẩu đã hết hạn hoặc không hợp lệ. Vui lòng yêu cầu lại.');
            }

            $email = $request->query('email');

            if (! $email) {
                Log::warning('Missing email in password reset request', [
                    'token' => $token,
                    'query_params' => $request->query(),
                ]);

                return redirect()->route('password.forgot')
                    ->with('error', 'Link đặt lại mật khẩu không hợp lệ. Thiếu thông tin email.');
            }

            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (! $resetRecord) {
                return redirect()->route('password.forgot')
                    ->with('error', 'Token đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng.');
            }

            if (! Hash::check($token, $resetRecord->token)) {
                return redirect()->route('password.forgot')
                    ->with('error', 'Token đặt lại mật khẩu không hợp lệ.');
            }

            if (now()->diffInMinutes($resetRecord->created_at) > 60) {
                DB::table('password_reset_tokens')
                    ->where('email', $email)
                    ->delete();

                return redirect()->route('password.forgot')
                    ->with('error', 'Link đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu lại.');
            }

            return view('auth.reset-password', [
                'token' => $token,
                'email' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing reset password form', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('password.forgot')
                ->with('error', 'Đã xảy ra lỗi khi xử lý yêu cầu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Xử lý reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'Token không hợp lệ.',
            'email.required' => 'Email là bắt buộc.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu mới là bắt buộc.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (! $resetRecord) {
                return redirect()->back()
                    ->with('error', 'Token đặt lại mật khẩu không hợp lệ.')
                    ->withInput();
            }

            if (! Hash::check($request->token, $resetRecord->token)) {
                return redirect()->back()
                    ->with('error', 'Token đặt lại mật khẩu không hợp lệ.')
                    ->withInput();
            }

            if (now()->diffInMinutes($resetRecord->created_at) > 60) {
                return redirect()->route('password.forgot')
                    ->with('error', 'Link đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu lại.');
            }

            $user = User::where('email', $request->email)->firstOrFail();

            $user->update([
                'password_hash' => Hash::make($request->password),
            ]);

            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            Log::info('Password reset successful', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            return redirect()->route('login')
                ->with('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Không thể đặt lại mật khẩu. Vui lòng thử lại sau.')
                ->withInput();
        }
    }

    /**
     * Verify email via signed URL
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        // Verify the hash matches the user's email
        if (! hash_equals((string) $hash, sha1($user->email))) {
            return redirect()->route('home')->with('error', 'Liên kết xác thực không hợp lệ.');
        }

        if ($user->email_verified_at) {
            return redirect()->route('home')->with('info', 'Email đã được xác thực trước đó.');
        }

        // Verify the signed URL
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('home')->with('error', 'Liên kết xác thực đã hết hạn. Vui lòng yêu cầu gửi lại email xác thực.');
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
        ]);

        // Auto login if not already logged in
        if (! Auth::check()) {
            Auth::login($user);
        }

        return redirect()->route('home')->with('success', 'Xác thực email thành công! Bạn có thể sử dụng đầy đủ các tính năng của hệ thống.');
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để gửi lại email xác thực.');
        }

        if ($user->email_verified_at) {
            return redirect()->back()->with('warning', 'Email đã được xác thực trước đó.');
        }

        try {
            $user->notify(new VerifyEmailNotification);

            Log::info('Verification email resent', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            return redirect()->back()->with('success', 'Email xác thực đã được gửi lại. Vui lòng kiểm tra hộp thư của bạn.');
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Không thể gửi email xác thực. Vui lòng thử lại sau.');
        }
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password_hash)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput();
        }

        if (Hash::check($request->new_password, $user->password_hash)) {
            return redirect()->back()
                ->withErrors(['new_password' => 'Mật khẩu mới phải khác với mật khẩu hiện tại.'])
                ->withInput();
        }

        try {
            $user->update([
                'password_hash' => Hash::make($request->new_password),
            ]);

            Log::info('Password changed', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            return redirect()->back()->with('success', 'Đổi mật khẩu thành công!');
        } catch (\Exception $e) {
            Log::error('Failed to change password', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Không thể đổi mật khẩu. Vui lòng thử lại sau.');
        }
    }
}
