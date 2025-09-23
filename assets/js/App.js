import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useNavigate } from 'react-router-dom';
import axios from 'axios';

// Import components
import Navbar from './components/Navbar';
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

// Configure axios defaults
axios.defaults.baseURL = window.location.origin;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true; // Enable cookies for session-based auth

// Set CSRF token for API calls
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-Token'] = csrfToken;
}

function AppContent() {
    const [user, setUser] = useState(window.userData || null);
    const [loading, setLoading] = useState(false);
    const [flashMessages, setFlashMessages] = useState([]);
    const navigate = useNavigate();

    useEffect(() => {
        // Display flash messages from server
        displayFlashMessages();
        
        // If no user data from server, check API
        if (!window.userData) {
            checkAuth();
        }
    }, []);

    const displayFlashMessages = () => {
        const flashContainer = document.getElementById('flash-messages');
        if (flashContainer && flashContainer.children.length > 0) {
            const messages = Array.from(flashContainer.children).map(el => ({
                type: el.classList.contains('alert-danger') ? 'error' : 
                      el.classList.contains('alert-success') ? 'success' : 
                      el.classList.contains('alert-warning') ? 'warning' : 'info',
                message: el.textContent.trim()
            }));
            setFlashMessages(messages);
            
            // Auto-hide messages after 5 seconds
            setTimeout(() => {
                setFlashMessages([]);
            }, 5000);
        }
    };

    const checkAuth = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/user/me');
            setUser(response.data);
        } catch (error) {
            console.log('Auth check failed:', error.response?.status, error.response?.data);
            setUser(null);
        } finally {
            setLoading(false);
        }
    };

    const handleLogin = (userData) => {
        setUser(userData);
    };

    const handleLogout = () => {
        setUser(null);
        // Use React Router navigation instead of full page refresh
        navigate('/');
    };

    if (loading) {
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
            <Navbar user={user} onLogout={handleLogout} />
            
            {/* Flash Messages */}
            {flashMessages.length > 0 && (
                <div className="container mt-4">
                    {flashMessages.map((msg, index) => (
                        <div key={index} className={`alert alert-${msg.type === 'error' ? 'danger' : msg.type} alert-dismissible fade show`} role="alert">
                            {msg.message}
                            <button 
                                type="button" 
                                className="btn-close" 
                                onClick={() => setFlashMessages(flashMessages.filter((_, i) => i !== index))}
                            ></button>
                        </div>
                    ))}
                </div>
            )}
            
            <main className="container mt-4">
                <Routes>
                    <Route path="/" element={<Home user={user} />} />
                    <Route path="/login" element={<Login onLogin={handleLogin} />} />
                    <Route path="/register" element={<Register onLogin={handleLogin} />} />
                    <Route path="/vehicles" element={<VehicleList user={user} />} />
                    <Route path="/vehicle/:id" element={<VehicleShow user={user} />} />
                    <Route 
                        path="/merchant/vehicle/new" 
                        element={user?.isMerchant ? <VehicleNew user={user} /> : <Navigate to="/login" />} 
                    />
                    <Route 
                        path="/merchant/vehicle/:id/edit" 
                        element={user?.isMerchant ? <VehicleEdit user={user} /> : <Navigate to="/login" />} 
                    />
                    <Route 
                        path="/merchant/vehicles" 
                        element={user?.isMerchant ? <MerchantVehicles user={user} /> : <Navigate to="/login" />} 
                    />
                    <Route 
                        path="/buyer/followed" 
                        element={user?.isBuyer ? <FollowedVehicles user={user} /> : <Navigate to="/login" />} 
                    />
                    <Route path="/forgot-password" element={<ForgotPassword />} />
                    <Route path="/reset-password/:token" element={<ResetPassword />} />
                </Routes>
            </main>
        </div>
    );
}

function App() {
    return (
        <Router>
            <AppContent />
        </Router>
    );
}

export default App;
