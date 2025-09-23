import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useVehicleStore } from '../stores/RootStore';
import { formatPrice } from '../utils/formatUtils';

const FollowedVehicles = observer(() => {
    const vehicleStore = useVehicleStore();

    useEffect(() => {
        vehicleStore.fetchFollowedVehicles();
    }, [vehicleStore]);

    const handleUnfollow = async (vehicleId) => {
        const result = await vehicleStore.unfollowVehicle(vehicleId);
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

    return (
        <div className="container">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2>Followed Vehicles</h2>
                <Link to="/vehicles" className="btn btn-primary">
                    Browse All Vehicles
                </Link>
            </div>

            {vehicleStore.followedVehicles.length > 0 ? (
                <div className="row">
                    {vehicleStore.followedVehicles.map(vehicle => (
                        <div key={vehicle.id} className="col-md-6 col-lg-4 mb-4">
                            <div className="card h-100 vehicle-card">
                                <div className="card-body">
                                    <h5 className="card-title">{vehicle.displayName}</h5>
                                    <p className="card-text">
                                        <strong>Type:</strong> {vehicle.type}<br />
                                        <strong>Brand:</strong> {vehicle.brand}<br />
                                        <strong>Model:</strong> {vehicle.model}<br />
                                        <strong>Colour:</strong> {vehicle.colour}<br />
                                        <strong>Price:</strong> ${formatPrice(vehicle.price)}<br />
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
});

export default FollowedVehicles;
