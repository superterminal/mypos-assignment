import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';

const VehicleShow = ({ user }) => {
    const { id } = useParams();
    const [vehicle, setVehicle] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        loadVehicle();
    }, [id]);

    const loadVehicle = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/vehicles/${id}`);
            setVehicle(response.data);
        } catch (error) {
            setError('Vehicle not found');
        } finally {
            setLoading(false);
        }
    };

    const handleFollow = async () => {
        try {
            await axios.post(`/api/vehicles/${id}/follow`);
            loadVehicle(); // Reload to update follow status
        } catch (error) {
            console.error('Error following vehicle:', error);
        }
    };

    const handleUnfollow = async () => {
        try {
            await axios.delete(`/api/vehicles/${id}/follow`);
            loadVehicle(); // Reload to update follow status
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

    if (error || !vehicle) {
        return (
            <div className="alert alert-danger">
                <h4>Vehicle not found</h4>
                <p>The vehicle you're looking for doesn't exist or has been removed.</p>
                <Link to="/vehicles" className="btn btn-primary">Back to Vehicles</Link>
            </div>
        );
    }

    return (
        <div className="container">
            <div className="mb-3">
                <Link to="/vehicles" className="btn btn-outline-secondary">
                    <i className="bi bi-arrow-left"></i> Back to Vehicles
                </Link>
            </div>
            <div className="row">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-body">
                             
                            <h1 className="card-title">{vehicle.displayName}</h1>
                            <p className="card-text">
                                <strong>Type:</strong> {vehicle.type}<br />
                                <strong>Brand:</strong> {vehicle.brand}<br />
                                <strong>Model:</strong> {vehicle.model}<br />
                                <strong>Colour:</strong> {vehicle.colour}<br />
                                <strong>Price:</strong> ${parseFloat(vehicle.price).toFixed(2)}<br />
                                <strong>Quantity:</strong> {vehicle.quantity}
                            </p>
                            
                            {/* Type-specific attributes */}
                            {vehicle.type === 'car' && (
                                <div>
                                    <strong>Doors:</strong> {vehicle.doors}<br />
                                    <strong>Category:</strong> {vehicle.category}
                                </div>
                            )}
                            {vehicle.type === 'truck' && (
                                <div>
                                    <strong>Beds:</strong> {vehicle.beds}
                                </div>
                            )}
                            {vehicle.type === 'trailer' && (
                                <div>
                                    <strong>Load Capacity:</strong> {vehicle.loadCapacityKg} kg<br />
                                    <strong>Axles:</strong> {vehicle.axles}
                                </div>
                            )}
                            {vehicle.engineCapacity && (
                                <div>
                                    <strong>Engine Capacity:</strong> {vehicle.engineCapacity}L
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body">
                            <h5>Merchant Information</h5>
                            <p>
                                <strong>Name:</strong> {vehicle.merchant.fullName}<br />
                                <strong>Email:</strong> {vehicle.merchant.email}
                            </p>
                            
                            {user?.isBuyer && (
                                <div className="d-grid">
                                    {vehicle.isFollowed ? (
                                        <button className="btn btn-outline-danger" onClick={handleUnfollow}>
                                            <i className="bi bi-x-circle"></i> Unfollow
                                        </button>
                                    ) : (
                                        <button className="btn btn-primary" onClick={handleFollow}>
                                            <i className="bi bi-plus-circle"></i> Follow Vehicle
                                        </button>
                                    )}
                                </div>
                            )}
                            
                            {user?.isMerchant && user.id === vehicle.merchant.id && (
                                <div className="d-grid gap-2">
                                    <Link to={`/merchant/vehicle/${vehicle.id}/edit`} className="btn btn-warning">
                                        Edit Vehicle
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default VehicleShow;
