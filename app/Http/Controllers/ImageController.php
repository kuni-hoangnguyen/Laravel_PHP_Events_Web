<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Upload ảnh (API endpoint)
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
            'type' => 'sometimes|in:avatar,banner', // Loại ảnh
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('image');
            $type = $request->input('type', 'banner');

            if ($type === 'avatar') {
                $path = $this->imageUploadService->uploadAvatar($file);
            } else {
                $path = $this->imageUploadService->uploadEventBanner($file);
            }

            $url = $this->imageUploadService->getUrl($path);

            return response()->json([
                'success' => true,
                'message' => 'Upload ảnh thành công',
                'path' => $path,
                'url' => $url,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi upload ảnh: ' . $e->getMessage(),
            ], 500);
        }
    }
}


