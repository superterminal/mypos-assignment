import { makeAutoObservable, runInAction, action, computed } from 'mobx';
import axios from 'axios';

class VehicleStore {
    vehicles = [];
    followedVehicles = [];
    currentVehicle = null;
    isLoading = false;
    error = null;
    filterOptions = null;
    carData = null;

    constructor() {
        makeAutoObservable(this);
    }

    fetchVehicles = action(async () => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.get('/api/vehicles');
            
            runInAction(() => {
                this.vehicles = response.data.vehicles || [];
                this.isLoading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Failed to fetch vehicles';
            });
        }
    })

    fetchVehicle = action(async (id) => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.get(`/api/vehicles/${id}`);
            
            runInAction(() => {
                this.currentVehicle = response.data;
                this.isLoading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Failed to fetch vehicle';
            });
        }
    })

    fetchMerchantVehicles = action(async () => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.get('/api/merchant/vehicles');
            
            runInAction(() => {
                this.vehicles = response.data.vehicles || [];
                this.isLoading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Failed to fetch merchant vehicles';
            });
        }
    })

    fetchFollowedVehicles = action(async () => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.get('/api/buyer/followed-vehicles');
            
            runInAction(() => {
                this.followedVehicles = response.data.vehicles || [];
                this.isLoading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Failed to fetch followed vehicles';
            });
        }
    })

    createVehicle = action(async (vehicleData) => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.post('/api/vehicles', vehicleData);
            
            runInAction(() => {
                this.isLoading = false;
            });

            return { success: true, data: response.data };
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.errors || [error.response?.data?.error || 'Failed to create vehicle'];
            });
            return { success: false, errors: this.error };
        }
    })

    updateVehicle = action(async (id, vehicleData) => {
        try {
            this.isLoading = true;
            this.error = null;

            const response = await axios.put(`/api/vehicles/${id}`, vehicleData);
            
            runInAction(() => {
                this.isLoading = false;
                // Update the vehicle in the list if it exists
                const index = this.vehicles.findIndex(v => v.id === id);
                if (index !== -1) {
                    this.vehicles[index] = { ...this.vehicles[index], ...vehicleData };
                }
                // Update current vehicle if it's the same
                if (this.currentVehicle?.id === id) {
                    this.currentVehicle = { ...this.currentVehicle, ...vehicleData };
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.errors || [error.response?.data?.error || 'Failed to update vehicle'];
            });
            return { success: false, errors: this.error };
        }
    })

    deleteVehicle = action(async (id) => {
        try {
            this.isLoading = true;
            this.error = null;

            await axios.delete(`/api/vehicles/${id}`);
            
            runInAction(() => {
                this.vehicles = this.vehicles.filter(v => v.id !== id);
                this.followedVehicles = this.followedVehicles.filter(v => v.id !== id);
                if (this.currentVehicle?.id === id) {
                    this.currentVehicle = null;
                }
                this.isLoading = false;
            });

            return { success: true };
        } catch (error) {
            runInAction(() => {
                this.isLoading = false;
                this.error = error.response?.data?.error || 'Failed to delete vehicle';
            });
            return { success: false, error: this.error };
        }
    })

    followVehicle = action(async (id) => {
        try {
            this.error = null;

            const response = await axios.post(`/api/vehicles/${id}/follow`);
            
            runInAction(() => {
                // Update the vehicle in the list to mark it as followed
                const vehicle = this.vehicles.find(v => v.id === id);
                if (vehicle) {
                    vehicle.isFollowed = true;
                }
                if (this.currentVehicle?.id === id) {
                    this.currentVehicle.isFollowed = true;
                }
            });

            return { success: true, message: response.data.message };
        } catch (error) {
            runInAction(() => {
                this.error = error.response?.data?.error || 'Failed to follow vehicle';
            });
            return { success: false, error: this.error };
        }
    })

    unfollowVehicle = action(async (id) => {
        try {
            this.error = null;

            const response = await axios.delete(`/api/vehicles/${id}/follow`);
            
            runInAction(() => {
                // Update the vehicle in the list to mark it as not followed
                const vehicle = this.vehicles.find(v => v.id === id);
                if (vehicle) {
                    vehicle.isFollowed = false;
                }
                if (this.currentVehicle?.id === id) {
                    this.currentVehicle.isFollowed = false;
                }
                // Remove from followed vehicles list
                this.followedVehicles = this.followedVehicles.filter(v => v.id !== id);
            });

            return { success: true, message: response.data.message };
        } catch (error) {
            runInAction(() => {
                this.error = error.response?.data?.error || 'Failed to unfollow vehicle';
            });
            return { success: false, error: this.error };
        }
    })

    fetchFilterOptions = action(async () => {
        try {
            const response = await axios.get('/api/vehicles/filter-options');
            
            runInAction(() => {
                this.filterOptions = response.data;
            });
        } catch (error) {
            console.error('Failed to fetch filter options:', error);
        }
    })

    fetchCarData = action(async () => {
        try {
            const response = await axios.get('/api/car-data');
            
            runInAction(() => {
                this.carData = response.data;
            });
        } catch (error) {
            console.error('Failed to fetch car data:', error);
        }
    })

    clearError = action(() => {
        this.error = null;
    })

    clearCurrentVehicle = action(() => {
        this.currentVehicle = null;
    })

    // Computed values
    get vehicleCount() {
        return this.vehicles.length;
    }

    get followedVehicleCount() {
        return this.followedVehicles.length;
    }

    get vehiclesByType() {
        return this.vehicles.reduce((acc, vehicle) => {
            acc[vehicle.type] = (acc[vehicle.type] || 0) + 1;
            return acc;
        }, {});
    }
}

export default VehicleStore;
