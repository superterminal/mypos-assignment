import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';

const VehicleEdit = ({ user }) => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [vehicle, setVehicle] = useState(null);
    const [formData, setFormData] = useState({
        brand: '',
        model: '',
        colour: '',
        price: '',
        quantity: 0,
        engineCapacity: '',
        doors: '',
        category: '',
        beds: '',
        loadCapacityKg: '',
        axles: ''
    });
    const [errors, setErrors] = useState([]);
    const [loading, setLoading] = useState(false);
    const [carData, setCarData] = useState([]);
    const [showBrandSuggestions, setShowBrandSuggestions] = useState(false);
    const [showModelSuggestions, setShowModelSuggestions] = useState(false);

    useEffect(() => {
        loadVehicle();
    }, [id]);

    // Fetch car data on component mount
    useEffect(() => {
        const fetchCarData = async () => {
            try {
                const response = await axios.get('/api/car-data');
                setCarData(response.data);
            } catch (error) {
                console.error('Failed to fetch car data:', error);
            }
        };
        fetchCarData();
    }, []);

    const loadVehicle = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/vehicles/${id}`);
            const vehicleData = response.data;
            setVehicle(vehicleData);
            
            // Populate form with vehicle data
            setFormData({
                brand: vehicleData.brand || '',
                model: vehicleData.model || '',
                colour: vehicleData.colour || '',
                price: vehicleData.price || '',
                quantity: vehicleData.quantity || 0,
                engineCapacity: vehicleData.engineCapacity || '',
                doors: vehicleData.doors || '',
                category: vehicleData.category || '',
                beds: vehicleData.beds || '',
                loadCapacityKg: vehicleData.loadCapacityKg || '',
                axles: vehicleData.axles || ''
            });
        } catch (error) {
            setErrors(['Vehicle not found']);
        } finally {
            setLoading(false);
        }
    };

    // Memoized brand suggestions
    const filteredBrands = useMemo(() => {
        if (vehicle?.type !== 'car' || !formData.brand || formData.brand.length < 1) {
            return [];
        }
        
        const query = formData.brand.toLowerCase();
        return carData
            .map(car => car.brand)
            .filter(brand => brand.toLowerCase().includes(query))
            .slice(0, 10);
    }, [carData, formData.brand, vehicle?.type]);

    // Memoized model suggestions
    const filteredModels = useMemo(() => {
        if (vehicle?.type !== 'car' || !formData.model || formData.model.length < 1 || !formData.brand) {
            return [];
        }
        
        const brandData = carData.find(car => car.brand === formData.brand);
        if (!brandData) return [];
        
        const query = formData.model.toLowerCase();
        return brandData.models
            .filter(model => model.toLowerCase().includes(query))
            .slice(0, 10);
    }, [carData, formData.brand, formData.model, vehicle?.type]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value
        });

        // Clear model when brand changes
        if (name === 'brand') {
            setFormData(prev => ({
                ...prev,
                [name]: value,
                model: ''
            }));
        }
    };

    const handleBrandSelect = (brand) => {
        setFormData(prev => ({
            ...prev,
            brand: brand,
            model: ''
        }));
        setShowBrandSuggestions(false);
    };

    const handleModelSelect = (model) => {
        setFormData(prev => ({
            ...prev,
            model: model
        }));
        setShowModelSuggestions(false);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors([]);

        try {
            await axios.put(`/api/vehicles/${id}`, formData);
            navigate('/merchant/vehicles');
        } catch (error) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            } else {
                setErrors(['Failed to update vehicle. Please try again.']);
            }
        } finally {
            setLoading(false);
        }
    };

    if (loading && !vehicle) {
        return (
            <div className="d-flex justify-content-center">
                <div className="spinner-border" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (errors.length > 0 && !vehicle) {
        return (
            <div className="alert alert-danger">
                <h4>Vehicle not found</h4>
                <p>The vehicle you're trying to edit doesn't exist or you don't have permission to edit it.</p>
            </div>
        );
    }

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">
                            <h3>Edit Vehicle - {vehicle?.displayName}</h3>
                        </div>
                        <div className="card-body">
                            {errors.length > 0 && (
                                <div className="alert alert-danger">
                                    <h6>Please fix the following errors:</h6>
                                    <ul className="mb-0">
                                        {errors.map((error, index) => (
                                            <li key={index}>{error}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <form onSubmit={handleSubmit}>
                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="brand" className="form-label">Brand</label>
                                            <div className="position-relative">
                                                <input
                                                    type="text"
                                                    name="brand"
                                                    id="brand"
                                                    className="form-control"
                                                    value={formData.brand}
                                                    onChange={handleChange}
                                                    onFocus={() => vehicle?.type === 'car' && setShowBrandSuggestions(true)}
                                                    onBlur={() => setTimeout(() => setShowBrandSuggestions(false), 200)}
                                                    required
                                                />
                                                {vehicle?.type === 'car' && showBrandSuggestions && filteredBrands.length > 0 && (
                                                    <div className="list-group position-absolute w-100" style={{ zIndex: 1000, top: '100%' }}>
                                                        {filteredBrands.map((brand, index) => (
                                                            <button
                                                                key={index}
                                                                type="button"
                                                                className="list-group-item list-group-item-action"
                                                                onClick={() => handleBrandSelect(brand)}
                                                            >
                                                                {brand}
                                                            </button>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="model" className="form-label">Model</label>
                                            <div className="position-relative">
                                                <input
                                                    type="text"
                                                    name="model"
                                                    id="model"
                                                    className="form-control"
                                                    value={formData.model}
                                                    onChange={handleChange}
                                                    onFocus={() => vehicle?.type === 'car' && formData.brand && setShowModelSuggestions(true)}
                                                    onBlur={() => setTimeout(() => setShowModelSuggestions(false), 200)}
                                                    required
                                                />
                                                {vehicle?.type === 'car' && showModelSuggestions && filteredModels.length > 0 && (
                                                    <div className="list-group position-absolute w-100" style={{ zIndex: 1000, top: '100%' }}>
                                                        {filteredModels.map((model, index) => (
                                                            <button
                                                                key={index}
                                                                type="button"
                                                                className="list-group-item list-group-item-action"
                                                                onClick={() => handleModelSelect(model)}
                                                            >
                                                                {model}
                                                            </button>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="engineCapacity" className="form-label">Engine Capacity (L)</label>
                                            <input
                                                type="number"
                                                name="engineCapacity"
                                                id="engineCapacity"
                                                className="form-control"
                                                value={formData.engineCapacity}
                                                onChange={handleChange}
                                                step="0.1"
                                                min="0"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="colour" className="form-label">Colour</label>
                                            <input
                                                type="text"
                                                name="colour"
                                                id="colour"
                                                className="form-control"
                                                value={formData.colour}
                                                onChange={handleChange}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="price" className="form-label">Price ($)</label>
                                            <input
                                                type="number"
                                                name="price"
                                                id="price"
                                                className="form-control"
                                                value={formData.price}
                                                onChange={handleChange}
                                                step="0.01"
                                                min="0"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="mb-3">
                                            <label htmlFor="quantity" className="form-label">Quantity</label>
                                            <input
                                                type="number"
                                                name="quantity"
                                                id="quantity"
                                                className="form-control"
                                                value={formData.quantity}
                                                onChange={handleChange}
                                                min="0"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Car-specific fields */}
                                {vehicle?.type === 'car' && (
                                    <div className="row">
                                        <div className="col-md-6">
                                            <div className="mb-3">
                                                <label htmlFor="doors" className="form-label">Doors</label>
                                                <select
                                                    name="doors"
                                                    id="doors"
                                                    className="form-select"
                                                    value={formData.doors}
                                                    onChange={handleChange}
                                                >
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div className="col-md-6">
                                            <div className="mb-3">
                                                <label htmlFor="category" className="form-label">Category</label>
                                                <input
                                                    type="text"
                                                    name="category"
                                                    id="category"
                                                    className="form-control"
                                                    value={formData.category}
                                                    onChange={handleChange}
                                                    placeholder="e.g., Sedan, SUV, Hatchback"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Truck-specific fields */}
                                {vehicle?.type === 'truck' && (
                                    <div className="mb-3">
                                        <label htmlFor="beds" className="form-label">Beds</label>
                                        <input
                                            type="number"
                                            name="beds"
                                            id="beds"
                                            className="form-control"
                                            value={formData.beds}
                                            onChange={handleChange}
                                            min="1"
                                        />
                                    </div>
                                )}

                                {/* Trailer-specific fields */}
                                {vehicle?.type === 'trailer' && (
                                    <div className="row">
                                        <div className="col-md-6">
                                            <div className="mb-3">
                                                <label htmlFor="loadCapacityKg" className="form-label">Load Capacity (kg)</label>
                                                <input
                                                    type="number"
                                                    name="loadCapacityKg"
                                                    id="loadCapacityKg"
                                                    className="form-control"
                                                    value={formData.loadCapacityKg}
                                                    onChange={handleChange}
                                                    step="0.1"
                                                    min="0"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-md-6">
                                            <div className="mb-3">
                                                <label htmlFor="axles" className="form-label">Axles</label>
                                                <input
                                                    type="number"
                                                    name="axles"
                                                    id="axles"
                                                    className="form-control"
                                                    value={formData.axles}
                                                    onChange={handleChange}
                                                    min="1"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" className="btn btn-secondary" onClick={() => navigate('/merchant/vehicles')}>
                                        Cancel
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={loading}>
                                        {loading ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            </>
                                        ) : (
                                            'Update Vehicle'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default VehicleEdit;
