import React, { useState, useEffect, useMemo } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';

const VehicleList = ({ user }) => {
    const [allVehicles, setAllVehicles] = useState([]);
    const [filterOptions, setFilterOptions] = useState({
        types: [],
        brands: [],
        colours: []
    });
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
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadVehicles();
        loadFilterOptions();
    }, []);

    const loadVehicles = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/vehicles');
            setAllVehicles(response.data.vehicles);
        } catch (error) {
            console.error('Error loading vehicles:', error);
        } finally {
            setLoading(false);
        }
    };

    const loadFilterOptions = async () => {
        try {
            const response = await axios.get('/api/vehicles/filter-options');
            setFilterOptions(response.data);
        } catch (error) {
            console.error('Error loading filter options:', error);
        }
    };

    // Client-side filtering logic
    const filteredVehicles = useMemo(() => {
        return allVehicles.filter(vehicle => {
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
            if (filters.priceMin && price < parseFloat(filters.priceMin)) {
                return false;
            }
            if (filters.priceMax && price > parseFloat(filters.priceMax)) {
                return false;
            }

            return true;
        });
    }, [allVehicles, filters]);

    // Client-side pagination
    const vehiclesPerPage = 12;
    const totalPages = Math.ceil(filteredVehicles.length / vehiclesPerPage);
    const currentPage = pagination?.page || 1;
    const startIndex = (currentPage - 1) * vehiclesPerPage;
    const endIndex = startIndex + vehiclesPerPage;
    const paginatedVehicles = filteredVehicles.slice(startIndex, endIndex);

    // Update pagination when filters change
    useEffect(() => {
        if (allVehicles.length > 0) {
            setPagination(prev => ({
                ...prev,
                page: 1,
                totalPages: Math.ceil(filteredVehicles.length / vehiclesPerPage),
                total: filteredVehicles.length
            }));
        }
    }, [filteredVehicles.length, allVehicles.length]);

    const handleFilterChange = (e) => {
        setFilters({
            ...filters,
            [e.target.name]: e.target.value
        });
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
        try {
            await axios.post(`/api/vehicles/${vehicleId}/follow`);
            loadVehicles(); // Reload to update follow status
        } catch (error) {
            console.error('Error following vehicle:', error);
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
                                        {filterOptions.types.map(type => (
                                            <option key={type} value={type}>
                                                {type.charAt(0).toUpperCase() + type.slice(1)}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="brand" className="form-label">Brand</label>
                                    <select
                                        name="brand"
                                        id="brand"
                                        className="form-select"
                                        value={filters.brand}
                                        onChange={handleFilterChange}
                                    >
                                        <option value="">All Brands</option>
                                        {filterOptions.brands.map(brand => (
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
                                    <label htmlFor="colour" className="form-label">Colour</label>
                                    <select
                                        name="colour"
                                        id="colour"
                                        className="form-select"
                                        value={filters.colour}
                                        onChange={handleFilterChange}
                                    >
                                        <option value="">All Colours</option>
                                        {filterOptions.colours.map(colour => (
                                            <option key={colour} value={colour}>{colour}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="priceMin" className="form-label">Min Price</label>
                                    <input
                                        type="number"
                                        name="priceMin"
                                        id="priceMin"
                                        className="form-control"
                                        value={filters.priceMin}
                                        onChange={handleFilterChange}
                                        step="0.01"
                                    />
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="priceMax" className="form-label">Max Price</label>
                                    <input
                                        type="number"
                                        name="priceMax"
                                        id="priceMax"
                                        className="form-control"
                                        value={filters.priceMax}
                                        onChange={handleFilterChange}
                                        step="0.01"
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
                        {user?.isMerchant && (
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
                                                <Link to={`/vehicle/${vehicle.id}`} className="btn btn-primary">
                                                    View Details
                                                </Link>
                                                {user?.isBuyer && (
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
};

export default VehicleList;
