import React, { useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, useNavigate } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import axios from 'axios';

// Import MobX configuration
import { configureMobX, configureMobXDev } from './stores/MobXConfig';

// Import stores
import rootStore, { useAuthStore, useUIStore } from './stores/RootStore';
import StoreProvider from './components/StoreProvider';

// Configure MobX
configureMobX();
configureMobXDev();

// Import components
import Navbar from './components/Navbar';
import MobXErrorBoundary from './components/MobXErrorBoundary';
import ProtectedRoute from './components/ProtectedRoute';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import VehicleList from './pages/VehicleList';
import VehicleShow from './pages/VehicleShow';
import VehicleNew from './pages/VehicleNew';
import VehicleEdit from './pages/VehicleEdit';
import MerchantVehicles from './pages/MerchantVehicles';
import FollowedVehicles from './pages/FollowedVehicles';
import ForgotPassword from './pages/ForgotPassword';
import ResetPassword from './pages/ResetPassword';
import NotFound from './pages/NotFound';

// Configure axios defaults
axios.defaults.baseURL = window.location.origin;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true; // Enable cookies for session-based auth

// Set CSRF token for API calls
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-Token'] = csrfToken;
}

const AppContent = observer(() => {
    const authStore = useAuthStore();
    const uiStore = useUIStore();
    const navigate = useNavigate();

    useEffect(() => {
        // Check authentication if not already authenticated
        if (!authStore.isAuthenticated) {
            authStore.checkAuth();
        }
    }, [authStore]);

    const handleLogout = async () => {
        await authStore.logout();
        navigate('/');
    };

    if (authStore.isLoading) {
        return (
            <div className="d-flex justify-content-center align-items-center" style={{ height: '100vh' }}>
                <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    return (
        <div className="App">
            <Navbar onLogout={handleLogout} />
            
            {/* Flash Messages */}
            {uiStore.hasFlashMessages && (
                <div className="container mt-4">
                    {uiStore.flashMessages.map((msg) => (
                        <div key={msg.id} className={`alert alert-${msg.type === 'error' ? 'danger' : msg.type} alert-dismissible fade show`} role="alert">
                            {msg.message}
                            <button 
                                type="button" 
                                className="btn-close" 
                                onClick={() => uiStore.removeFlashMessage(msg.id)}
                            ></button>
                        </div>
                    ))}
                </div>
            )}
            
            <main className="container mt-4">
                <Routes>
                    <Route path="/" element={<Home />} />
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />
                    <Route path="/vehicles" element={<VehicleList />} />
                    <Route path="/vehicle/:id" element={<VehicleShow />} />
                    <Route 
                        path="/merchant/vehicle/new" 
                        element={
                            <ProtectedRoute requiredRole="merchant">
                                <VehicleNew />
                            </ProtectedRoute>
                        } 
                    />
                    <Route 
                        path="/merchant/vehicle/:id/edit" 
                        element={
                            <ProtectedRoute requiredRole="merchant">
                                <VehicleEdit />
                            </ProtectedRoute>
                        } 
                    />
                    <Route 
                        path="/merchant/vehicles" 
                        element={
                            <ProtectedRoute requiredRole="merchant">
                                <MerchantVehicles />
                            </ProtectedRoute>
                        } 
                    />
                    <Route 
                        path="/buyer/followed" 
                        element={
                            <ProtectedRoute requiredRole="buyer">
                                <FollowedVehicles />
                            </ProtectedRoute>
                        } 
                    />
                    <Route path="/forgot-password" element={<ForgotPassword />} />
                    <Route path="/reset-password/:token" element={<ResetPassword />} />
                    {/* Catch-all route for 404 pages */}
                    <Route path="*" element={<NotFound />} />
                </Routes>
            </main>
        </div>
    );
});

function App() {
    // Make rootStore available globally for debugging
    window.rootStore = rootStore;
    
    return (
        <MobXErrorBoundary>
            <StoreProvider>
                <Router>
                    <AppContent />
                </Router>
            </StoreProvider>
        </MobXErrorBoundary>
    );
}

export default App;
