import { makeAutoObservable, action, computed } from 'mobx';

class UIStore {
    flashMessages = [];
    isLoading = false;
    sidebarOpen = false;

    constructor() {
        makeAutoObservable(this);
        this.initializeFlashMessages();
    }

    initializeFlashMessages = action(() => {
        // Initialize with server-side flash messages if available
        const flashContainer = document.getElementById('flash-messages');
        if (flashContainer && flashContainer.children.length > 0) {
            const messages = Array.from(flashContainer.children).map(el => ({
                id: Date.now() + Math.random(),
                type: el.classList.contains('alert-danger') ? 'error' : 
                      el.classList.contains('alert-success') ? 'success' : 
                      el.classList.contains('alert-warning') ? 'warning' : 'info',
                message: el.textContent.trim()
            }));
            
            this.flashMessages = messages;
            
            // Auto-hide messages after 5 seconds
            setTimeout(() => {
                this.clearAllFlashMessages();
            }, 5000);
        }
    })

    addFlashMessage = action((type, message, autoHide = true) => {
        const flashMessage = {
            id: Date.now() + Math.random(),
            type,
            message
        };

        this.flashMessages.push(flashMessage);

        if (autoHide) {
            setTimeout(() => {
                this.removeFlashMessage(flashMessage.id);
            }, 5000);
        }
    })

    removeFlashMessage = action((id) => {
        this.flashMessages = this.flashMessages.filter(msg => msg.id !== id);
    })

    clearAllFlashMessages = action(() => {
        this.flashMessages = [];
    })

    setLoading = action((loading) => {
        this.isLoading = loading;
    })

    toggleSidebar = action(() => {
        this.sidebarOpen = !this.sidebarOpen;
    })

    closeSidebar = action(() => {
        this.sidebarOpen = false;
    })

    // Helper methods for common flash message types
    showSuccess = action((message) => {
        this.addFlashMessage('success', message);
    })

    showError = action((message) => {
        this.addFlashMessage('error', message);
    })

    showWarning = action((message) => {
        this.addFlashMessage('warning', message);
    })

    showInfo = action((message) => {
        this.addFlashMessage('info', message);
    })

    // Computed values
    get hasFlashMessages() {
        return this.flashMessages.length > 0;
    }

    get successMessages() {
        return this.flashMessages.filter(msg => msg.type === 'success');
    }

    get errorMessages() {
        return this.flashMessages.filter(msg => msg.type === 'error');
    }

    get warningMessages() {
        return this.flashMessages.filter(msg => msg.type === 'warning');
    }

    get infoMessages() {
        return this.flashMessages.filter(msg => msg.type === 'info');
    }
}

export default UIStore;
