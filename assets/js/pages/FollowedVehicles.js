import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

const FollowedVehicles = ({ user }) => {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadVehicles();
    }, []);

    const loadVehicles = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/buyer/followed-vehicles');
            setVehicles(response.data.vehicles || []);
        } catch (error) {
            console.error('Error loading followed vehicles:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleUnfollow = async (vehicleId) => {
        try {
            await axios.delete(`/api/vehicles/${vehicleId}/follow`);
            loadVehicles(); // Reload the list
        } catch (error) {
            console.error('Error unfollowing vehicle:', error);
        }
    };

    if (loading) {
        return (
            <div className="d-flex justify-content-center">
                <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    return (
        <div className="container">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2>Followed Vehicles</h2>
                <Link to="/vehicles" className="btn btn-primary">
                    Browse All Vehicles
                </Link>
            </div>

            {vehicles.length > 0 ? (
                <div className="row">
                    {vehicles.map(vehicle => (
                        <div key={vehicle.id} className="col-md-6 col-lg-4 mb-4">
                            <div className="card h-100 vehicle-card">
                                <div className="card-body">
                                    <h5 className="card-title">{vehicle.displayName}</h5>
                                    <p className="card-text">
                                        <strong>Type:</strong> {vehicle.type}<br />
                                        <strong>Brand:</strong> {vehicle.brand}<br />
                                        <strong>Model:</strong> {vehicle.model}<br />
                                        <strong>Colour:</strong> {vehicle.colour}<br />
                                        <strong>Price:</strong> ${parseFloat(vehicle.price).toFixed(2)}<br />
                                        <strong>Quantity:</strong> {vehicle.quantity}
                                    </p>
                                </div>
                                <div className="card-footer d-flex justify-content-between align-items-center">
                                    <Link to={`/vehicle/${vehicle.id}`} className="btn btn-primary btn-sm">
                                        View Details
                                    </Link>
                                    <button
                                        type="button"
                                        className="btn btn-outline-danger btn-sm"
                                        onClick={() => handleUnfollow(vehicle.id)}
                                    >
                                        Unfollow
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="alert alert-info">
                    <h4>No followed vehicles</h4>
                    <p>You haven't followed any vehicles yet. <Link to="/vehicles">Browse vehicles</Link> and follow the ones you're interested in.</p>
                </div>
            )}
        </div>
    );
};

export default FollowedVehicles;
