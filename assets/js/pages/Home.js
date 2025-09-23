import React from 'react';
import { Link } from 'react-router-dom';

const Home = ({ user }) => {
    return (
        <div>
            {/* Hero Section */}
            <div className="hero-section">
                <div className="container">
                    <div className="row align-items-center">
                        <div className="col-lg-6">
                            <h1 className="display-4 fw-bold mb-4">myPOS Car Market!</h1>
                            <p className="lead mb-4">
                                The ultimate vehicle marketplace connecting buyers and sellers. 
                                Find your dream vehicle or sell your inventory with ease.
                            </p>
                            <div className="d-flex gap-3">
                                {user ? (
                                    <>
                                        <Link to="/vehicles" className="btn btn-light btn-lg">
                                            <i className="bi bi-search"></i> Browse Vehicles
                                        </Link>
                                        {user.isMerchant && (
                                            <Link to="/merchant/vehicles" className="btn btn-outline-light btn-lg">
                                                <i className="bi bi-truck"></i> My Vehicles
                                            </Link>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        <Link to="/register" className="btn btn-light btn-lg">
                                            <i className="bi bi-person-plus"></i> Get Started
                                        </Link>
                                        <Link to="/vehicles" className="btn btn-outline-light btn-lg">
                                            <i className="bi bi-eye"></i> Browse Vehicles
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                        <div className="col-lg-6 text-center">
                            <i className="bi bi-car-front feature-icon"></i>
                            <h3>Your Vehicle Journey Starts Here</h3>
                        </div>
                    </div>
                </div>
            </div>

            {/* Features Section */}
            <div className="container mb-5">
                <div className="row text-center mb-5">
                    <div className="col-12">
                        <h2 className="display-5 fw-bold">Why Choose myPOS Car Market!</h2>
                        <p className="lead text-muted">Built with modern technology and user-focused design</p>
                    </div>
                </div>
                
                <div className="row g-4">
                    <div className="col-md-4">
                        <div className="card h-100 border-0 shadow-sm">
                            <div className="card-body text-center p-4">
                                <i className="bi bi-shield-check feature-icon text-primary"></i>
                                <h5 className="card-title">Secure & Reliable</h5>
                                <p className="card-text">
                                    Built with Symfony framework, ensuring enterprise-grade security 
                                    and performance for all your vehicle transactions.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card h-100 border-0 shadow-sm">
                            <div className="card-body text-center p-4">
                                <i className="bi bi-search feature-icon text-success"></i>
                                <h5 className="card-title">Advanced Search</h5>
                                <p className="card-text">
                                    Filter vehicles by type, brand, model, color, and price range. 
                                    Find exactly what you're looking for in seconds.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card h-100 border-0 shadow-sm">
                            <div className="card-body text-center p-4">
                                <i className="bi bi-heart feature-icon text-danger"></i>
                                <h5 className="card-title">Follow & Save</h5>
                                <p className="card-text">
                                    Save vehicles you're interested in and get updates. 
                                    Never miss out on your dream vehicle again.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* How It Works Section */}
            <div className="how-it-works">
                <div className="container">
                    <div className="row text-center mb-5">
                        <div className="col-12">
                            <h2 className="display-5 fw-bold">How It Works</h2>
                            <p className="lead text-muted">Simple steps to buy or sell vehicles</p>
                        </div>
                    </div>
                    
                    <div className="row g-4">
                        <div className="col-md-4">
                            <div className="text-center">
                                <div className="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '80px', height: '80px' }}>
                                    <span className="fs-2 fw-bold">1</span>
                                </div>
                                <h5>Create Account</h5>
                                <p className="text-muted">
                                    Sign up as a Buyer to browse and follow vehicles, 
                                    or as a Merchant to list your inventory.
                                </p>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="text-center">
                                <div className="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '80px', height: '80px' }}>
                                    <span className="fs-2 fw-bold">2</span>
                                </div>
                                <h5>Browse or List</h5>
                                <p className="text-muted">
                                    Buyers can search and filter vehicles, while Merchants 
                                    can add, edit, and manage their vehicle listings.
                                </p>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="text-center">
                                <div className="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '80px', height: '80px' }}>
                                    <span className="fs-2 fw-bold">3</span>
                                </div>
                                <h5>Connect & Transact</h5>
                                <p className="text-muted">
                                    Follow vehicles you're interested in, track your favorites, 
                                    and connect with sellers for transactions.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Vehicle Types Section */}
            <div className="container my-5">
                <div className="row text-center mb-5">
                    <div className="col-12">
                        <h2 className="display-5 fw-bold">Vehicle Types We Support</h2>
                        <p className="lead text-muted">From motorcycles to trailers, we've got you covered</p>
                    </div>
                </div>
                
                <div className="row g-4">
                    <div className="col-md-3">
                        <div className="card h-100 border-0 shadow-sm text-center">
                            <div className="card-body p-4">
                                <i className="bi bi-bicycle feature-icon text-primary"></i>
                                <h6 className="card-title">Motorcycles</h6>
                                <p className="card-text small">
                                    Brand, model, engine capacity, color, price, quantity
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card h-100 border-0 shadow-sm text-center">
                            <div className="card-body p-4">
                                <i className="bi bi-car-front feature-icon text-success"></i>
                                <h6 className="card-title">Cars</h6>
                                <p className="card-text small">
                                    Brand, model, engine capacity, color, doors, category, price, quantity
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card h-100 border-0 shadow-sm text-center">
                            <div className="card-body p-4">
                                <i className="bi bi-truck feature-icon text-warning"></i>
                                <h6 className="card-title">Trucks</h6>
                                <p className="card-text small">
                                    Brand, model, engine capacity, color, beds, price, quantity
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card h-100 border-0 shadow-sm text-center">
                            <div className="card-body p-4">
                                <i className="bi bi-box feature-icon text-info"></i>
                                <h6 className="card-title">Trailers</h6>
                                <p className="card-text small">
                                    Brand, model, load capacity, axles, price, quantity
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Stats Section */}
            <div className="stats-section">
                <div className="container">
                    <div className="row text-center">
                        <div className="col-md-4">
                            <h3 className="display-4 fw-bold">100+</h3>
                            <p className="lead">Vehicles Listed</p>
                        </div>
                        <div className="col-md-4">
                            <h3 className="display-4 fw-bold">50+</h3>
                            <p className="lead">Active Merchants</p>
                        </div>
                        <div className="col-md-4">
                            <h3 className="display-4 fw-bold">200+</h3>
                            <p className="lead">Happy Buyers</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Call to Action Section */}
            <div className="cta-section">
                <div className="container text-center">
                    <h2 className="display-5 fw-bold mb-4">Ready to Get Started?</h2>
                    <p className="lead mb-4">
                        Join thousands of users who trust myPOS Car Market! for their vehicle needs.
                    </p>
                    {!user ? (
                        <>
                            <Link to="/register" className="btn btn-primary btn-lg me-3">
                                <i className="bi bi-person-plus"></i> Create Account
                            </Link>
                            <Link to="/login" className="btn btn-outline-light btn-lg">
                                <i className="bi bi-box-arrow-in-right"></i> Login
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link to="/vehicles" className="btn btn-primary btn-lg me-3">
                                <i className="bi bi-search"></i> Browse Vehicles
                            </Link>
                            {user.isMerchant && (
                                <Link to="/merchant/vehicles" className="btn btn-outline-light btn-lg">
                                    <i className="bi bi-plus-circle"></i> Add Vehicle
                                </Link>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Home;
