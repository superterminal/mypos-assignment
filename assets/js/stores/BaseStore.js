import { action, makeObservable, observable } from 'mobx';

/**
 * Base store class that provides common functionality for all stores
 * Following MobX best practices for error handling and state management
 */
export class BaseStore {
    isLoading = false;
    error = null;
    lastUpdated = null;

    constructor() {
        makeObservable(this, {
            isLoading: observable,
            error: observable,
            lastUpdated: observable,
            setLoading: action,
            setError: action,
            clearError: action,
            setLastUpdated: action,
        });
    }

    setLoading = action((loading) => {
        this.isLoading = loading;
    });

    setError = action((error) => {
        this.error = error;
        this.isLoading = false;
    });

    clearError = action(() => {
        this.error = null;
    });

    setLastUpdated = action(() => {
        this.lastUpdated = new Date();
    });

    // Helper method for async operations with proper error handling
    async executeAsync(operation, errorMessage = 'Operation failed') {
        try {
            this.setLoading(true);
            this.clearError();
            
            const result = await operation();
            this.setLastUpdated();
            
            return { success: true, data: result };
        } catch (error) {
            const errorMsg = error.response?.data?.error || 
                           error.response?.data?.message || 
                           error.message || 
                           errorMessage;
            this.setError(errorMsg);
            return { success: false, error: errorMsg };
        } finally {
            this.setLoading(false);
        }
    }

    // Computed value to check if data is stale
    get isDataStale() {
        if (!this.lastUpdated) return true;
        const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
        return this.lastUpdated < fiveMinutesAgo;
    }
}
