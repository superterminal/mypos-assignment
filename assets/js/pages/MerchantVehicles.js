import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useVehicleStore } from '../stores/RootStore';
import { formatPrice } from '../utils/formatUtils';

const MerchantVehicles = observer(() => {
    const vehicleStore = useVehicleStore();

    useEffect(() => {
        vehicleStore.fetchMerchantVehicles();
    }, [vehicleStore]);

    const handleDelete = async (vehicleId) => {
        if (window.confirm('Are you sure you want to delete this vehicle?')) {
            const result = await vehicleStore.deleteVehicle(vehicleId);
            if (result.success) {
                // Optionally show success message
            }
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
                <h2>My Vehicles</h2>
                <Link to="/merchant/vehicle/new" className="btn btn-success">
                    Add New Vehicle
                </Link>
            </div>

            {vehicleStore.vehicles.length > 0 ? (
                <div className="row">
                    {vehicleStore.vehicles.map(vehicle => (
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
});

export default MerchantVehicles;
