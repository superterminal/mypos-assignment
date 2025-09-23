import React from 'react';
import { StoreContext } from '../stores/RootStore';

const StoreProvider = ({ children }) => {
    return (
        <StoreContext.Provider value={window.rootStore}>
            {children}
        </StoreContext.Provider>
    );
};

export default StoreProvider;
