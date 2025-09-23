import React from 'react';
import { useAuthStore } from '../stores/RootStore';
import Unauthorized from '../pages/Unauthorized';

const ProtectedRoute = ({ children, requiredRole, fallback = null }) => {
    const authStore = useAuthStore();

    // If user is not authenticated
    if (!authStore.isAuthenticated) {
        return fallback || <Unauthorized />;
    }

    // If no specific role is required, allow access
    if (!requiredRole) {
        return children;
    }

    // Check if user has the required role
    const hasPermission = (() => {
        switch (requiredRole) {
            case 'merchant':
                return authStore.isMerchant;
            case 'buyer':
                return authStore.isBuyer;
            case 'authenticated':
                return authStore.isAuthenticated;
            default:
                return false;
        }
    })();

    // If user doesn't have permission, show unauthorized page
    if (!hasPermission) {
        return <Unauthorized />;
    }

    // User has permission, render the protected content
    return children;
};

export default ProtectedRoute;
