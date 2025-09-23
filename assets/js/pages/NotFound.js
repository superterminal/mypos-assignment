import React from 'react';
import { Link } from 'react-router-dom';

const NotFound = () => {
    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8 text-center">
                    <div className="card">
                        <div className="card-body py-5">
                            <div className="mb-4">
                                <h1 className="display-1 text-muted">404</h1>
                                <h2 className="h4 mb-3">Page Not Found</h2>
                                <p className="text-muted mb-4">
                                    Sorry, the page you are looking for doesn't exist or you don't have permission to access it.
                                </p>
                            </div>
                            
                            <div className="d-grid gap-2 d-md-flex justify-content-md-center">
                                <Link to="/" className="btn btn-primary">
                                    <i className="bi bi-house me-2"></i>
                                    Go Home
                                </Link>
                                <Link to="/vehicles" className="btn btn-outline-primary">
                                    <i className="bi bi-car-front me-2"></i>
                                    Browse Vehicles
                                </Link>
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

export default NotFound;
