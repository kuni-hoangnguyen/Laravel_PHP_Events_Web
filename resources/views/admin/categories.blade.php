@extends('layouts.admin')

@section('title', 'Quản lý danh mục - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý danh mục</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form tạo/sửa category -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4" id="form-title">Tạo danh mục mới</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" id="category-form">
                @csrf
                <input type="hidden" name="_method" value="POST" id="form-method">
                <input type="hidden" name="category_id" id="category-id">

                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Tên danh mục <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="category_name" 
                        name="category_name" 
                        required
                        maxlength="100"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category_name') border-red-500 @enderror"
                    >
                    @error('category_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Mô tả
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                    ></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md">
                        <span id="submit-text">Tạo danh mục</span>
                    </button>
                    <button type="button" id="cancel-btn" class="hidden bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-6 rounded-md" onclick="resetForm()">
                        Hủy
                    </button>
                </div>
            </form>
        </div>

        <!-- Danh sách categories -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Danh sách danh mục</h2>
            @if($categories->count() > 0)
                <div class="space-y-3">
                    @foreach($categories as $category)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($category->description, 50) }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $category->events_count }} sự kiện</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button 
                                    onclick="editCategory({{ $category->category_id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')"
                                    class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Sửa
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->category_id) }}" class="contents" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Chưa có danh mục nào</p>
            @endif
        </div>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    document.getElementById('form-title').textContent = 'Sửa danh mục';
    document.getElementById('submit-text').textContent = 'Cập nhật danh mục';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('category-id').value = id;
    document.getElementById('category_name').value = name;
    document.getElementById('description').value = description;
    document.getElementById('category-form').action = '{{ route("admin.categories.update", ":id") }}'.replace(':id', id);
    document.getElementById('cancel-btn').classList.remove('hidden');
}

function resetForm() {
    document.getElementById('form-title').textContent = 'Tạo danh mục mới';
    document.getElementById('submit-text').textContent = 'Tạo danh mục';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('category-id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('category-form').action = '{{ route("admin.categories.store") }}';
    document.getElementById('cancel-btn').classList.add('hidden');
}
</script>
@endsection
