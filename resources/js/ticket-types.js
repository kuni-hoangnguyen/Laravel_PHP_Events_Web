let ticketTypeIndex = 0;

export function initTicketTypes(initialIndex = 0) {
    ticketTypeIndex = initialIndex;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (ticketTypeIndex === 0) {
            addTicketType();
        }
    });
}

export function addTicketType(ticketType = null) {
    const container = document.getElementById('ticket-types-container');
    if (!container) return;
    
    const index = ticketTypeIndex++;
    const ticketTypeHtml = `
        <div class="border border-gray-200 rounded-lg p-4 ticket-type-item" data-index="${index}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-gray-900">Loại vé #${index + 1}</h4>
                <button type="button" onclick="window.removeTicketType(${index})" class="text-red-600 hover:text-red-800 text-sm">
                    Xóa
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên loại vé <span class="text-red-500">*</span></label>
                    <input type="text" name="ticket_types[${index}][name]" value="${ticketType?.name || ''}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ví dụ: Vé VIP, Vé Thường">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá (VND) <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][price]" value="${ticketType?.price || ''}" required min="0" step="1000"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng số vé <span class="text-red-500">*</span></label>
                    <input type="number" name="ticket_types[${index}][total_quantity]" value="${ticketType?.total_quantity || ''}" required min="1"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <input type="text" name="ticket_types[${index}][description]" value="${ticketType?.description || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Mô tả quyền lợi của loại vé">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_start_time]" value="${ticketType?.sale_start_time || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc bán</label>
                    <input type="datetime-local" name="ticket_types[${index}][sale_end_time]" value="${ticketType?.sale_end_time || ''}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-3">
                <label class="flex items-center">
                    <input type="checkbox" name="ticket_types[${index}][is_active]" value="1" ${ticketType?.is_active !== false ? 'checked' : ''}
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Kích hoạt</span>
                </label>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', ticketTypeHtml);
}

export function removeTicketType(index) {
    const item = document.querySelector(`.ticket-type-item[data-index="${index}"]`);
    if (item) {
        const ticketTypeId = item.getAttribute('data-ticket-type-id');
        if (ticketTypeId) {
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = `ticket_types[${index}][_delete]`;
            deleteInput.value = '1';
            item.appendChild(deleteInput);
            item.style.display = 'none';
        } else {
            item.remove();
        }
    }
}

window.addTicketType = addTicketType;
window.removeTicketType = removeTicketType;

