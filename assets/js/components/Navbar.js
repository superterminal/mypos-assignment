import React from 'react';
import { Link } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuthStore } from '../stores/RootStore';

const Navbar = observer(({ onLogout }) => {
    const authStore = useAuthStore();

    const handleLogout = async () => {
        await authStore.logout();
        if (onLogout) {
            onLogout();
        }
    };

    return (
        <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
            <div className="container">
                <Link className="navbar-brand" to="/">
                    MyPOS Car Market
                </Link>
                <button 
                    className="navbar-toggler" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav"
                >
                    <span className="navbar-toggler-icon"></span>
                </button>
                <div className="collapse navbar-collapse" id="navbarNav">
                    <ul className="navbar-nav me-auto">
                        <li className="nav-item">
                            <Link className="nav-link" to="/vehicles">
                                Vehicles
                            </Link>
                        </li>
                        {authStore.isAuthenticated && (
                            <>
                                {authStore.isMerchant && (
                                    <li className="nav-item">
                                        <Link className="nav-link" to="/merchant/vehicles">
                                            My Vehicles
                                        </Link>
                                    </li>
                                )}
                                {authStore.isBuyer && (
                                    <li className="nav-item">
                                        <Link className="nav-link" to="/buyer/followed">
                                            Followed
                                        </Link>
                                    </li>
                                )}
                            </>
                        )}
                    </ul>
                    <ul className="navbar-nav">
                        {authStore.isAuthenticated ? (
                            <li className="nav-item dropdown">
                                <a 
                                    className="nav-link dropdown-toggle" 
                                    href="#" 
                                    role="button" 
                                    data-bs-toggle="dropdown"
                                >
                                    {authStore.userFullName}
                                    <span className="badge bg-secondary ms-2">
                                        {authStore.isMerchant ? 'Merchant' : 'Buyer'}
                                    </span>
                                </a>
                                <ul className="dropdown-menu">
                                    <li>
                                        <div className="dropdown-item-text">
                                            <small className="text-muted">
                                                Logged in as: <strong>{authStore.isMerchant ? 'Merchant' : 'Buyer'}</strong>
                                            </small>
                                        </div>
                                    </li>
                                    <li><hr className="dropdown-divider" /></li>
                                    <li>
                                        <button 
                                            className="dropdown-item" 
                                            onClick={handleLogout}
                                        >
                                            Logout
                                        </button>
                                    </li>
                                </ul>
                            </li>
                        ) : (
                            <>
                                <li className="nav-item">
                                    <Link className="nav-link" to="/login">
                                        Login
                                    </Link>
                                </li>
                                <li className="nav-item">
                                    <Link className="nav-link" to="/register">
                                        Register
                                    </Link>
                                </li>
                            </>
                        )}
                    </ul>
                </div>
            </div>
        </nav>
    );
});

export default Navbar;
