<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'category_id' => 'required|exists:event_categories,category_id',
            'location_id' => 'required|exists:event_locations,location_id',
            'max_attendees' => 'required|integer|min:1',
            'banner_url' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tên sự kiện là bắt buộc.',
            'title.max' => 'Tên sự kiện không được vượt quá 200 ký tự.',
            'description.required' => 'Mô tả sự kiện là bắt buộc.',
            'start_time.required' => 'Thời gian bắt đầu là bắt buộc.',
            'start_time.after' => 'Thời gian bắt đầu phải sau thời điểm hiện tại.',
            'end_time.required' => 'Thời gian kết thúc là bắt buộc.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'category_id.required' => 'Danh mục sự kiện là bắt buộc.',
            'category_id.exists' => 'Danh mục sự kiện không tồn tại.',
            'location_id.required' => 'Địa điểm là bắt buộc.',
            'location_id.exists' => 'Địa điểm không tồn tại.',
            'max_attendees.required' => 'Số lượng tham gia tối đa là bắt buộc.',
            'max_attendees.min' => 'Số lượng tham gia tối đa phải ít nhất 1.',
            'banner_url.url' => 'URL banner không hợp lệ.',
        ];
    }
}
