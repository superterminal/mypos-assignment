import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

const MerchantVehicles = ({ user }) => {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadVehicles();
    }, []);

    const loadVehicles = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/merchant/vehicles');
            setVehicles(response.data.vehicles || []);
        } catch (error) {
            console.error('Error loading vehicles:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (vehicleId) => {
        if (window.confirm('Are you sure you want to delete this vehicle?')) {
            try {
                await axios.delete(`/api/vehicles/${vehicleId}`);
                loadVehicles(); // Reload the list
            } catch (error) {
                console.error('Error deleting vehicle:', error);
            }
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
                <h2>My Vehicles</h2>
                <Link to="/merchant/vehicle/new" className="btn btn-success">
                    Add New Vehicle
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
                                    <Link
                                        to={`/vehicle/${vehicle.id}`}
                                        className="btn btn-primary btn-sm"
                                        style={{ minWidth: '90px' }}
                                    >
                                        View
                                    </Link>
                                    <Link
                                        to={`/merchant/vehicle/${vehicle.id}/edit`}
                                        className="btn btn-warning btn-sm"
                                        style={{ minWidth: '90px' }}
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="btn btn-danger btn-sm"
                                        style={{ minWidth: '90px' }}
                                        onClick={() => handleDelete(vehicle.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="alert alert-info">
                    <h4>No vehicles found</h4>
                    <p>You haven't added any vehicles yet. <Link to="/merchant/vehicle/new">Add your first vehicle</Link> to get started.</p>
                </div>
            )}
        </div>
    );
};

export default MerchantVehicles;
