import { createContext, useContext } from 'react';
import AuthStore from './AuthStore';
import VehicleStore from './VehicleStore';
import UIStore from './UIStore';

class RootStore {
    constructor() {
        this.authStore = new AuthStore();
        this.vehicleStore = new VehicleStore();
        this.uiStore = new UIStore();
    }
}

const rootStore = new RootStore();

// Create React Context
export const StoreContext = createContext(rootStore);

// Custom hook to use stores
export const useStores = () => {
    const context = useContext(StoreContext);
    if (!context) {
        throw new Error('useStores must be used within a StoreProvider');
    }
    return context;
};

// Individual store hooks for convenience
export const useAuthStore = () => {
    const { authStore } = useStores();
    return authStore;
};

export const useVehicleStore = () => {
    const { vehicleStore } = useStores();
    return vehicleStore;
};

export const useUIStore = () => {
    const { uiStore } = useStores();
    return uiStore;
};

export default rootStore;
