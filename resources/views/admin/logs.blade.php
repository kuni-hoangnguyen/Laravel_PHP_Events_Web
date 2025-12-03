@extends('layouts.admin')

@section('title', 'Nhật ký quản trị - Seniks Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Nhật ký quản trị</h1>

    @php
        // $logs được truyền từ controller
    @endphp

    @if($logs->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người thực hiện</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bảng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Log ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                        {{ $log->action_description ?? $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($log->admin_id)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Admin: {{ $log->admin->full_name ?? $log->admin->name ?? 'N/A' }}
                                        </span>
                                    @elseif($log->user_id)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            User: {{ $log->user->full_name ?? $log->user->name ?? 'N/A' }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->target_table ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->target_id ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->log_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button onclick="openLogDetailModal({{ $log->log_id }})" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500 text-lg">Chưa có log nào.</p>
        </div>
    @endif
</div>

<!-- Modal Chi tiết Log -->
<div id="logDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Chi tiết Log</h3>
                <button onclick="closeLogDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="logDetailContent" class="space-y-4">
                <!-- Nội dung sẽ được load bằng AJAX -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                    <p class="mt-2 text-gray-500">Đang tải...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openLogDetailModal(logId) {
    document.getElementById('logDetailModal').classList.remove('hidden');
    
    // Load chi tiết log bằng AJAX
    fetch(`{{ url('admin/logs') }}/${logId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('logDetailContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Log ID</label>
                        <p class="mt-1 text-sm text-gray-900">${data.log_id}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hành động</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                ${data.action_description || data.action}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Người thực hiện</label>
                        <p class="mt-1 text-sm text-gray-900">
                            ${data.admin_id ? 
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin: ${data.admin_name || 'N/A'}
                                </span>` : 
                                data.user_id ?
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    User: ${data.user_name || 'N/A'}
                                </span>` : 
                                'N/A'
                            }
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bảng</label>
                        <p class="mt-1 text-sm text-gray-900">${data.target_table || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target ID</label>
                        <p class="mt-1 text-sm text-gray-900">${data.target_id || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">IP Address</label>
                        <p class="mt-1 text-sm text-gray-900">${data.ip_address || 'N/A'}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Thời gian</label>
                        <p class="mt-1 text-sm text-gray-900">${data.created_at}</p>
                    </div>
                    ${data.old_values ? `
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Giá trị cũ</label>
                        <pre class="mt-1 p-3 bg-gray-50 rounded-md text-xs text-gray-900 overflow-auto max-h-40">${JSON.stringify(data.old_values, null, 2)}</pre>
                    </div>
                    ` : ''}
                    ${data.new_values ? `
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Giá trị mới</label>
                        <pre class="mt-1 p-3 bg-gray-50 rounded-md text-xs text-gray-900 overflow-auto max-h-40">${JSON.stringify(data.new_values, null, 2)}</pre>
                    </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('logDetailContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-500">Lỗi khi tải chi tiết log: ${error.message}</p>
                </div>
            `;
        });
}

function closeLogDetailModal() {
    document.getElementById('logDetailModal').classList.add('hidden');
}

// Đóng modal khi click bên ngoài
document.getElementById('logDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogDetailModal();
    }
});
</script>
@endsection

