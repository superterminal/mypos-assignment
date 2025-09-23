import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuthStore } from '../stores/RootStore';

const Register = observer(() => {
    const authStore = useAuthStore();
    const navigate = useNavigate();
    
    const [formData, setFormData] = useState({
        email: '',
        password: '',
        confirmPassword: '',
        firstName: '',
        lastName: '',
        userType: 'buyer'
    });

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        authStore.clearError();

        const result = await authStore.register(formData);
        
        if (result.success) {
            // Registration successful - redirect to login page
            navigate('/login', { 
                state: { 
                    message: 'Registration successful! Please log in to continue.',
                    email: result.data.email 
                } 
            });
        }
    };

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-6">
                    <div className="card">
                        <div className="card-header">
                            <h3 className="text-center">Register</h3>
                        </div>
                        <div className="card-body">
                            {authStore.error && (
                                <div className="alert alert-danger">
                                    <ul className="mb-0">
                                        {Array.isArray(authStore.error) ? 
                                            authStore.error.map((error, index) => (
                                                <li key={index}>{error}</li>
                                            )) : 
                                            <li>{authStore.error}</li>
                                        }
                                    </ul>
                                </div>
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
                                    <div className="form-text">
                                        Password must contain at least one lowercase letter, one uppercase letter, and one number
                                    </div>
                                </div>
                                <div className="mb-3">
                                    <label htmlFor="confirmPassword" className="form-label">Confirm Password</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        id="confirmPassword"
                                        name="confirmPassword"
                                        value={formData.confirmPassword}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                                <div className="mb-3">
                                    <label htmlFor="firstName" className="form-label">First Name</label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="firstName"
                                        name="firstName"
                                        value={formData.firstName}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                                <div className="mb-3">
                                    <label htmlFor="lastName" className="form-label">Last Name</label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="lastName"
                                        name="lastName"
                                        value={formData.lastName}
                                        onChange={handleChange}
                                        required
                                    />
                                </div>
                                <div className="mb-3">
                                    <label htmlFor="userType" className="form-label">User Type</label>
                                    <select
                                        className="form-select"
                                        id="userType"
                                        name="userType"
                                        value={formData.userType}
                                        onChange={handleChange}
                                    >
                                        <option value="buyer">Buyer</option>
                                        <option value="merchant">Merchant</option>
                                    </select>
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
                                            'Register'
                                        )}
                                    </button>
                                </div>
                            </form>

                            <div className="text-center mt-3">
                                <Link to="/login">Already have an account? Login here</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
});

export default Register;
