import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useVehicleStore } from '../stores/RootStore';
import { formatNumberWithCommas, parseFormattedNumber } from '../utils/formatUtils';

const VehicleNew = observer(() => {
    const vehicleStore = useVehicleStore();
    const navigate = useNavigate();
    
    const [formData, setFormData] = useState({
        type: '',
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
    const [brandSuggestions, setBrandSuggestions] = useState([]);
    const [modelSuggestions, setModelSuggestions] = useState([]);
    const [showBrandSuggestions, setShowBrandSuggestions] = useState(false);
    const [showModelSuggestions, setShowModelSuggestions] = useState(false);

    // Fetch car data on component mount
    useEffect(() => {
        vehicleStore.fetchCarData();
    }, [vehicleStore]);

    // Memoized brand suggestions
    const filteredBrands = useMemo(() => {
        if (formData.type !== 'car' || !formData.brand || formData.brand.length < 1 || !vehicleStore.carData) {
            return [];
        }
        
        const query = formData.brand.toLowerCase();
        return vehicleStore.carData
            .map(car => car.brand)
            .filter(brand => brand.toLowerCase().includes(query))
            .slice(0, 10);
    }, [vehicleStore.carData, formData.brand, formData.type]);

    // Memoized model suggestions
    const filteredModels = useMemo(() => {
        if (formData.type !== 'car' || !formData.model || formData.model.length < 1 || !formData.brand || !vehicleStore.carData) {
            return [];
        }
        
        const brandData = vehicleStore.carData.find(car => car.brand === formData.brand);
        if (!brandData) return [];
        
        const query = formData.model.toLowerCase();
        return brandData.models
            .filter(model => model.toLowerCase().includes(query))
            .slice(0, 10);
    }, [vehicleStore.carData, formData.brand, formData.model, formData.type]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        
        // Special handling for price field
        if (name === 'price') {
            const formattedValue = formatNumberWithCommas(value);
            setFormData({
                ...formData,
                [name]: formattedValue
            });
            return;
        }
        
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
        
        // Parse the formatted price back to numeric value
        const submitData = {
            ...formData,
            price: parseFormattedNumber(formData.price)
        };
        
        const result = await vehicleStore.createVehicle(submitData);
        
        if (result.success) {
            navigate('/merchant/vehicles');
        }
    };

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">
                            <h3>Add New Vehicle</h3>
                        </div>
                        <div className="card-body">
                            {vehicleStore.error && (
                                <div className="alert alert-danger">
                                    <h6>Please fix the following errors:</h6>
                                    <ul className="mb-0">
                                        {Array.isArray(vehicleStore.error) ? 
                                            vehicleStore.error.map((error, index) => (
                                                <li key={index}>{error}</li>
                                            )) : 
                                            <li>{vehicleStore.error}</li>
                                        }
                                    </ul>
                                </div>
                            )}

                            <form onSubmit={handleSubmit}>
                                <div className="mb-3">
                                    <label htmlFor="type" className="form-label">Vehicle Type</label>
                                    <select
                                        name="type"
                                        id="type"
                                        className="form-select"
                                        value={formData.type}
                                        onChange={handleChange}
                                        required
                                    >
                                        <option value="">Select Type</option>
                                        <option value="motorcycle">Motorcycle</option>
                                        <option value="car">Car</option>
                                        <option value="truck">Truck</option>
                                        <option value="trailer">Trailer</option>
                                    </select>
                                </div>

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
                            onFocus={() => formData.type === 'car' && setShowBrandSuggestions(true)}
                            onBlur={() => setTimeout(() => setShowBrandSuggestions(false), 200)}
                            required
                        />
                        {formData.type === 'car' && showBrandSuggestions && filteredBrands.length > 0 && (
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
                            onFocus={() => formData.type === 'car' && formData.brand && setShowModelSuggestions(true)}
                            onBlur={() => setTimeout(() => setShowModelSuggestions(false), 200)}
                            required
                        />
                        {formData.type === 'car' && showModelSuggestions && filteredModels.length > 0 && (
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
                                                type="text"
                                                name="price"
                                                id="price"
                                                className="form-control"
                                                value={formData.price}
                                                onChange={handleChange}
                                                placeholder="0.00"
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
                                {formData.type === 'car' && (
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
                                {formData.type === 'truck' && (
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
                                {formData.type === 'trailer' && (
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
                                    <button type="submit" className="btn btn-primary" disabled={vehicleStore.isLoading}>
                                        {vehicleStore.isLoading ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            </>
                                        ) : (
                                            'Add Vehicle'
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
});

export default VehicleNew;
