@extends('layouts.admin')

@section('title', 'Quản lý người dùng - Seniks Events')

@section('content')
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Quản lý người dùng</h1>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Tìm kiếm theo tên hoặc email..."
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <select 
                name="role_id" 
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Tất cả vai trò</option>
                @foreach($roles ?? [] as $role)
                    <option value="{{ $role->role_id }}" {{ request('role_id') == $role->role_id ? 'selected' : '' }}>
                        {{ $role->role_name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md">
                Tìm kiếm
            </button>
        </form>
    </div>


    @if($users->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người dùng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đăng ký</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->full_name ?? $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                                {{ $role->role_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.users.show', $user->user_id) }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors" title="Xem chi tiết">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.role.update', $user->user_id) }}" class="flex-1">
                                            @csrf
                                            @method('PUT')
                                            <select name="role_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-md px-2 py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">Vai trò</option>
                                                @foreach(\App\Models\Role::all() as $role)
                                                    <option value="{{ $role->role_id }}" {{ $user->roles->contains('role_id', $role->role_id) ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        @if(!$user->isAdmin())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này? Tất cả dữ liệu liên quan sẽ bị xóa.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition-colors" title="Xóa người dùng">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Không tìm thấy người dùng nào.</p>
        </div>
    @endif
</div>
@endsection
