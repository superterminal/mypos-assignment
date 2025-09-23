import React, { useState, useEffect, useMemo } from 'react';
import { Link } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuthStore, useVehicleStore } from '../stores/RootStore';
import { formatPrice, formatNumberWithCommas, parseFormattedNumber } from '../utils/formatUtils';

const VehicleList = observer(() => {
    const authStore = useAuthStore();
    const vehicleStore = useVehicleStore();
    
    const [filters, setFilters] = useState({
        type: '',
        brand: '',
        model: '',
        colour: '',
        priceMin: '',
        priceMax: ''
    });
    const [pagination, setPagination] = useState({
        page: 1,
        totalPages: 1,
        total: 0
    });

    useEffect(() => {
        vehicleStore.fetchVehicles();
        vehicleStore.fetchFilterOptions();
    }, [vehicleStore]);

    // Client-side filtering logic
    const filteredVehicles = useMemo(() => {
        return vehicleStore.vehicles.filter(vehicle => {
            // Type filter
            if (filters.type && vehicle.type !== filters.type) {
                return false;
            }

            // Brand filter
            if (filters.brand && vehicle.brand !== filters.brand) {
                return false;
            }

            // Model filter (case-insensitive partial match)
            if (filters.model && !vehicle.model.toLowerCase().includes(filters.model.toLowerCase())) {
                return false;
            }

            // Colour filter
            if (filters.colour && vehicle.colour !== filters.colour) {
                return false;
            }

            // Price range filter
            const price = parseFloat(vehicle.price);
            if (filters.priceMin && price < parseFloat(parseFormattedNumber(filters.priceMin))) {
                return false;
            }
            if (filters.priceMax && price > parseFloat(parseFormattedNumber(filters.priceMax))) {
                return false;
            }

            return true;
        });
    }, [vehicleStore.vehicles, filters]);

    // Get brands available for the selected type
    const availableBrands = useMemo(() => {
        if (!filters.type) {
            // If no type is selected, show all brands
            return vehicleStore.filterOptions?.brands || [];
        }
        
        // Filter vehicles by selected type and get unique brands
        const vehiclesOfType = vehicleStore.vehicles.filter(vehicle => vehicle.type === filters.type);
        const brands = [...new Set(vehiclesOfType.map(vehicle => vehicle.brand))];
        return brands.sort();
    }, [filters.type, vehicleStore.vehicles, vehicleStore.filterOptions?.brands]);

    // Get colours available for the selected type and brand
    const availableColours = useMemo(() => {
        if (!filters.type) {
            // If no type is selected, show all colours
            return vehicleStore.filterOptions?.colours || [];
        }
        
        let filteredVehicles = vehicleStore.vehicles.filter(vehicle => vehicle.type === filters.type);
        
        if (filters.brand) {
            filteredVehicles = filteredVehicles.filter(vehicle => vehicle.brand === filters.brand);
        }
        
        const colours = [...new Set(filteredVehicles.map(vehicle => vehicle.colour))];
        return colours.sort();
    }, [filters.type, filters.brand, vehicleStore.vehicles, vehicleStore.filterOptions?.colours]);

    // Client-side pagination
    const vehiclesPerPage = 12;
    const totalPages = Math.ceil(filteredVehicles.length / vehiclesPerPage);
    const currentPage = pagination?.page || 1;
    const startIndex = (currentPage - 1) * vehiclesPerPage;
    const endIndex = startIndex + vehiclesPerPage;
    const paginatedVehicles = filteredVehicles.slice(startIndex, endIndex);

    // Update pagination when filters change
    useEffect(() => {
        if (vehicleStore.vehicles.length > 0) {
            setPagination(prev => ({
                ...prev,
                page: 1,
                totalPages: Math.ceil(filteredVehicles.length / vehiclesPerPage),
                total: filteredVehicles.length
            }));
        }
    }, [filteredVehicles.length, vehicleStore.vehicles.length]);

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        
        if (name === 'type') {
            // When type changes, clear brand and colour filters
            setFilters({
                ...filters,
                type: value,
                brand: '',
                colour: ''
            });
        } else if (name === 'brand') {
            // When brand changes, clear colour filter
            setFilters({
                ...filters,
                brand: value,
                colour: ''
            });
        } else if (name === 'priceMin' || name === 'priceMax') {
            // Special handling for price fields
            const formattedValue = formatNumberWithCommas(value);
            setFilters({
                ...filters,
                [name]: formattedValue
            });
        } else {
            // For other filters, just update the specific field
            setFilters({
                ...filters,
                [name]: value
            });
        }
    };

    const clearFilters = () => {
        setFilters({
            type: '',
            brand: '',
            model: '',
            colour: '',
            priceMin: '',
            priceMax: ''
        });
    };

    const handleFollow = async (vehicleId) => {
        const result = await vehicleStore.followVehicle(vehicleId);
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
            <div className="row">
                <div className="col-md-3">
                    <div className="card filter-sidebar">
                        <div className="card-header">
                            <h5>Filters</h5>
                        </div>
                        <div className="card-body">
                            <form>
                                <div className="mb-3">
                                    <label htmlFor="type" className="form-label">Type</label>
                                    <select
                                        name="type"
                                        id="type"
                                        className="form-select"
                                        value={filters.type}
                                        onChange={handleFilterChange}
                                    >
                                        <option value="">All Types</option>
                                        {vehicleStore.filterOptions?.types?.map(type => (
                                            <option key={type} value={type}>
                                                {type.charAt(0).toUpperCase() + type.slice(1)}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="brand" className="form-label">
                                        Brand {filters.type && `(${availableBrands.length} available)`}
                                    </label>
                                    <select
                                        name="brand"
                                        id="brand"
                                        className="form-select"
                                        value={filters.brand}
                                        onChange={handleFilterChange}
                                        disabled={!filters.type}
                                    >
                                        <option value="">
                                            {filters.type ? `All ${filters.type} Brands` : 'Select Type First'}
                                        </option>
                                        {availableBrands.map(brand => (
                                            <option key={brand} value={brand}>{brand}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="model" className="form-label">Model</label>
                                    <input
                                        type="text"
                                        name="model"
                                        id="model"
                                        className="form-control"
                                        value={filters.model}
                                        onChange={handleFilterChange}
                                    />
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="colour" className="form-label">
                                        Colour {filters.type && `(${availableColours.length} available)`}
                                    </label>
                                    <select
                                        name="colour"
                                        id="colour"
                                        className="form-select"
                                        value={filters.colour}
                                        onChange={handleFilterChange}
                                        disabled={!filters.type}
                                    >
                                        <option value="">
                                            {filters.type ? `All ${filters.type} Colours` : 'Select Type First'}
                                        </option>
                                        {availableColours.map(colour => (
                                            <option key={colour} value={colour}>{colour}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="priceMin" className="form-label">Min Price</label>
                                    <input
                                        type="text"
                                        name="priceMin"
                                        id="priceMin"
                                        className="form-control"
                                        value={filters.priceMin}
                                        onChange={handleFilterChange}
                                        placeholder="0.00"
                                    />
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="priceMax" className="form-label">Max Price</label>
                                    <input
                                        type="text"
                                        name="priceMax"
                                        id="priceMax"
                                        className="form-control"
                                        value={filters.priceMax}
                                        onChange={handleFilterChange}
                                        placeholder="0.00"
                                    />
                                </div>

                                <div className="d-grid gap-2">
                                    <button type="button" className="btn btn-outline-secondary" onClick={clearFilters}>
                                        Clear Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div className="col-md-9">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h2>Vehicles ({pagination?.total || filteredVehicles.length} found)</h2>
                        {authStore.isMerchant && (
                            <Link to="/merchant/vehicle/new" className="btn btn-success">
                                Add Vehicle
                            </Link>
                        )}
                    </div>

                    {paginatedVehicles.length > 0 ? (
                        <>
                            <div className="row">
                                {paginatedVehicles.map(vehicle => (
                                    <div key={vehicle.id} className="col-md-6 col-lg-4 mb-4">
                                        <div className="card h-100 vehicle-card">
                                            <div className="card-body">
                                                <h5 className="card-title d-flex justify-content-between align-items-center">
                                                    {vehicle.displayName}
                                                    {authStore.isMerchant && vehicle.merchant?.id === authStore.userId && (
                                                        <span className="badge bg-success">
                                                            <i className="bi bi-person-check me-1"></i>
                                                            My Listing
                                                        </span>
                                                    )}
                                                </h5>
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
                                                <Link to={`/vehicle/${vehicle.id}`} className="btn btn-primary">
                                                    View Details
                                                </Link>
                                                {authStore.isBuyer && (
                                                    <button
                                                        type="button"
                                                        className={`btn ${vehicle.isFollowed ? 'btn-success' : 'btn-secondary'}`}
                                                        onClick={() => handleFollow(vehicle.id)}
                                                        disabled={vehicle.isFollowed}
                                                    >
                                                        <i className={`bi ${vehicle.isFollowed ? 'bi-check-circle' : 'bi-plus-circle'}`}></i>
                                                        {vehicle.isFollowed ? 'Followed' : 'Follow'}
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Pagination */}
                            {totalPages > 1 && (
                                <nav aria-label="Vehicle pagination">
                                    <ul className="pagination justify-content-center">
                                        {currentPage > 1 && (
                                            <li className="page-item">
                                                <button
                                                    className="page-link"
                                                    onClick={() => setPagination({ ...pagination, page: currentPage - 1 })}
                                                >
                                                    Previous
                                                </button>
                                            </li>
                                        )}

                                        {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                                            <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                                                <button
                                                    className="page-link"
                                                    onClick={() => setPagination({ ...pagination, page })}
                                                >
                                                    {page}
                                                </button>
                                            </li>
                                        ))}

                                        {currentPage < totalPages && (
                                            <li className="page-item">
                                                <button
                                                    className="page-link"
                                                    onClick={() => setPagination({ ...pagination, page: currentPage + 1 })}
                                                >
                                                    Next
                                                </button>
                                            </li>
                                        )}
                                    </ul>
                                </nav>
                            )}
                        </>
                    ) : (
                        <div className="alert alert-info">
                            <h4>No vehicles found</h4>
                            <p>Try adjusting your filters or check back later for new listings.</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
});

export default VehicleList;
