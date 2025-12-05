export function toastFunction() {
    return {
        toasts: [],
        
        init() {
            window.toastInstance = this;
        },
        
        show(message, type = 'info', duration = 5000) {
            const toastConfig = {
                success: {
                    bgClass: 'bg-green-50 border border-green-200',
                    textClass: 'text-green-800',
                    iconClass: 'text-green-600'
                },
                error: {
                    bgClass: 'bg-red-50 border border-red-200',
                    textClass: 'text-red-800',
                    iconClass: 'text-red-600'
                },
                warning: {
                    bgClass: 'bg-yellow-50 border border-yellow-200',
                    textClass: 'text-yellow-800',
                    iconClass: 'text-yellow-600'
                },
                info: {
                    bgClass: 'bg-blue-50 border border-blue-200',
                    textClass: 'text-blue-800',
                    iconClass: 'text-blue-600'
                }
            };
            
            const config = toastConfig[type] || toastConfig.info;
            
            const toast = {
                message: message,
                type: type,
                show: true,
                ...config
            };
            
            this.toasts.push(toast);
            
            setTimeout(() => {
                this.remove(this.toasts.indexOf(toast));
            }, duration);
        },
        
        remove(index) {
            if (index >= 0 && index < this.toasts.length) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 300);
            }
        }
    };
}

