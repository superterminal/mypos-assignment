import { makeAutoObservable, runInAction, action, computed } from 'mobx';
import axios from 'axios';

class AuthStore {
    user = null;
    isAuthenticated = false;
    isLoading = false;
    error = null;

    constructor() {
        makeAutoObservable(this);
        this.initializeAuth();
    }

    initializeAuth = action(() => {
        // Initialize with server-side user data if available
        if (window.userData) {
            this.user = window.userData;
            this.isAuthenticated = true;
        }
    })

    checkAuth = action(async () => {
        if (this.isAuthenticated) return;

        try {
            this.isLoading = true;
            this.error = null;
            
            const response = await axios.get('/api/user/me');
            
            runInAction(() => {
                this.user = response.data;
                this.isAuthenticated = true;
                this.isLoading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.user = null;
                this.isAuthenticated = false;
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Authentication failed';
            });
        }
    })

    login = action(async (email, password) => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.post('/api/login', { email, password });
            
            // Get full user data after successful login
            const userResponse = await axios.get('/api/user/me');
            
            runInAction(() => {
                this.user = userResponse.data;
                this.isAuthenticated = true;
                this.isLoading = false;
            });

            return { success: true, user: this.user };
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Login failed';
            });
            return { success: false, error: this.error };
        }
    })

    register = action(async (userData) => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.post('/api/register', userData);
            
            runInAction(() => {
                this.isLoading = false;
            });

            return { success: true, data: response.data };
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.errors || [error.response?.data?.error || 'Registration failed'];
            });
            return { success: false, errors: this.error };
        }
    })

    logout = action(async () => {
        try {
            await axios.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            runInAction(() => {
                this.user = null;
                this.isAuthenticated = false;
                this.error = null;
            });
        }
    })

    clearError = action(() => {
        this.error = null;
    })

    // Computed values
    get isMerchant() {
        return this.user?.isMerchant || false;
    }

    get isBuyer() {
        return this.user?.isBuyer || false;
    }

    get userFullName() {
        return this.user?.fullName || '';
    }

    get userEmail() {
        return this.user?.email || '';
    }

    get userId() {
        return this.user?.id || null;
    }
}

export default AuthStore;
