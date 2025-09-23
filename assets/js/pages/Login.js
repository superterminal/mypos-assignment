import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuthStore, useUIStore } from '../stores/RootStore';

const Login = observer(() => {
    const location = useLocation();
    const authStore = useAuthStore();
    const uiStore = useUIStore();
    const navigate = useNavigate();
    
    const [formData, setFormData] = useState({
        email: location.state?.email || '',
        password: ''
    });
    const [successMessage, setSuccessMessage] = useState(location.state?.message || '');

    // Clear success message after 5 seconds
    useEffect(() => {
        if (successMessage) {
            const timer = setTimeout(() => {
                setSuccessMessage('');
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [successMessage]);

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        authStore.clearError();

        const result = await authStore.login(formData.email, formData.password);
        
        if (result.success) {
            navigate('/');
        }
    };

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-6">
                    <div className="card">
                        <div className="card-header">
                            <h3 className="text-center">Login</h3>
                        </div>
                        <div className="card-body">
                            {successMessage && (
                                <div className="alert alert-success">{successMessage}</div>
                            )}
                            {authStore.error && (
                                <div className="alert alert-danger">{authStore.error}</div>
                            )}

                            <form onSubmit={handleSubmit}>
                                <div className="mb-3">
                                    <label htmlFor="email" className="form-label">Email</label>
                                    <input
                                        type="email"
                                        className="form-control"
                                        id="email"
                                        name="email"
                                        value={formData.email}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                                <div className="mb-3">
                                    <label htmlFor="password" className="form-label">Password</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        id="password"
                                        name="password"
                                        value={formData.password}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>

                                <div className="d-grid">
                                    <button 
                                        type="submit" 
                                        className="btn btn-primary"
                                        disabled={authStore.isLoading}
                                    >
                                        {authStore.isLoading ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            </>
                                        ) : (
                                            'Sign in'
                                        )}
                                    </button>
                                </div>
                            </form>

                            <div className="text-center mt-3">
                                <Link to="/register">Don't have an account? Register here</Link><br />
                                <Link to="/forgot-password">Forgot your password?</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
});

export default Login;
