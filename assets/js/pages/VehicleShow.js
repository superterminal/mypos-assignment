import React, { useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuthStore, useVehicleStore } from '../stores/RootStore';
import { formatPrice } from '../utils/formatUtils';

const VehicleShow = observer(() => {
    const { id } = useParams();
    const authStore = useAuthStore();
    const vehicleStore = useVehicleStore();

    useEffect(() => {
        vehicleStore.fetchVehicle(id);
    }, [id, vehicleStore]);

    const handleFollow = async () => {
        const result = await vehicleStore.followVehicle(id);
        if (result.success) {
            // Optionally show success message
        }
    };

    const handleUnfollow = async () => {
        const result = await vehicleStore.unfollowVehicle(id);
        if (result.success) {
            // Optionally show success message
        }
    };

    if (vehicleStore.isLoading) {
        return (
            <div className="d-flex justify-content-center">
                <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (vehicleStore.error || !vehicleStore.currentVehicle) {
        return (
            <div className="alert alert-danger">
                <h4>Vehicle not found</h4>
                <p>The vehicle you're looking for doesn't exist or has been removed.</p>
                <Link to="/vehicles" className="btn btn-primary">Back to Vehicles</Link>
            </div>
        );
    }

    const vehicle = vehicleStore.currentVehicle;

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
                                <strong>Price:</strong> ${formatPrice(vehicle.price)}<br />
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
                            
                            {authStore.isBuyer && (
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
                            
                            {authStore.isMerchant && authStore.user?.id === vehicle.merchant.id && (
                                <div className="d-grid gap-2">
                                    <Link to={`/merchant/vehicle/${vehicle.id}/edit`} className="btn btn-warning">
                                        <i className="bi bi-pencil"></i> Edit Vehicle
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
});

export default VehicleShow;
