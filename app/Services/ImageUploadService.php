<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload ảnh
     *
     * @param UploadedFile $file
     * @param string $folder Thư mục lưu trữ (ví dụ: 'events', 'avatars')
     * @return string Đường dẫn file đã lưu
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('File không hợp lệ. Chỉ chấp nhận ảnh (JPEG, PNG, GIF, WebP).');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('File quá lớn. Kích thước tối đa là 5MB.');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . date('Y/m') . '/' . $filename;

        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        return $path;
    }

    /**
     * Upload avatar
     *
     * @param UploadedFile $file
     * @return string Đường dẫn file đã lưu
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        return $this->upload($file, 'avatars');
    }

    /**
     * Upload banner event
     *
     * @param UploadedFile $file
     * @return string Đường dẫn file đã lưu
     */
    public function uploadEventBanner(UploadedFile $file): string
    {
        return $this->upload($file, 'events/banners');
    }

    /**
     * Xóa file ảnh
     *
     * @param string $path Đường dẫn file cần xóa
     * @return bool
     */
    public function delete(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Lấy URL đầy đủ của ảnh (chỉ dùng khi hiển thị, không lưu vào DB)
     *
     * @param string|null $path Đường dẫn file
     * @return string|null
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Lấy relative path để lưu vào database
     * Chỉ trả về path, không bao gồm domain
     *
     * @param string|null $path Đường dẫn file
     * @return string|null
     */
    public function getPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $path;
    }
}

