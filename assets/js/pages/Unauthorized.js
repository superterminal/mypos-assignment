import React from 'react';
import { Link } from 'react-router-dom';
import { useAuthStore } from '../stores/RootStore';

const Unauthorized = () => {
    const authStore = useAuthStore();

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8 text-center">
                    <div className="card">
                        <div className="card-body py-5">
                            <div className="mb-4">
                                <h1 className="display-1 text-muted">403</h1>
                                <h2 className="h4 mb-3">Access Denied</h2>
                                <p className="text-muted mb-4">
                                    You don't have permission to access this page.
                                </p>
                                {authStore.isAuthenticated ? (
                                    <p className="text-muted mb-4">
                                        You are logged in as a <strong>{authStore.isMerchant ? 'Merchant' : 'Buyer'}</strong>, 
                                        but this page requires different permissions.
                                    </p>
                                ) : (
                                    <p className="text-muted mb-4">
                                        Please log in to access this page.
                                    </p>
                                )}
                            </div>
                            
                            <div className="d-grid gap-2 d-md-flex justify-content-md-center">
                                {authStore.isAuthenticated ? (
                                    <>
                                        <Link to="/" className="btn btn-primary">
                                            <i className="bi bi-house me-2"></i>
                                            Go Home
                                        </Link>
                                        <Link to="/vehicles" className="btn btn-outline-primary">
                                            <i className="bi bi-car-front me-2"></i>
                                            Browse Vehicles
                                        </Link>
                                        {authStore.isMerchant && (
                                            <Link to="/merchant/vehicles" className="btn btn-outline-success">
                                                <i className="bi bi-list-ul me-2"></i>
                                                My Vehicles
                                            </Link>
                                        )}
                                        {authStore.isBuyer && (
                                            <Link to="/buyer/followed" className="btn btn-outline-success">
                                                <i className="bi bi-heart me-2"></i>
                                                Followed Vehicles
                                            </Link>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        <Link to="/login" className="btn btn-primary">
                                            <i className="bi bi-box-arrow-in-right me-2"></i>
                                            Login
                                        </Link>
                                        <Link to="/register" className="btn btn-outline-primary">
                                            <i className="bi bi-person-plus me-2"></i>
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                            
                            <div className="mt-4">
                                <small className="text-muted">
                                    If you believe this is an error, please contact support.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Unauthorized;
