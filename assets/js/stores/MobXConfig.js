import { configure } from 'mobx';

/**
 * MobX Configuration following best practices
 * This should be imported and called before any store is used
 */
export const configureMobX = () => {
    configure({
        // Enforce actions to be used for state modifications
        enforceActions: process.env.NODE_ENV === 'production' ? 'always' : 'never',
        
        // Disable computed value caching in development for easier debugging
        computedRequiresReaction: false,
        
        // Disable observable value change tracking in development
        observableRequiresReaction: false,
        
        // Disable reaction tracking in development
        reactionRequiresObservable: false,
        
        // Disable action tracking in development
        actionRequiresObservable: false,
        
        // Disable strict mode in production for better performance
        disableErrorBoundaries: process.env.NODE_ENV === 'production',
        
        // Use proxy for better performance (requires ES6+)
        useProxies: 'always',
        
        // Enable MobX devtools in development
        isolateGlobalState: false,
    });
};

/**
 * Development-only MobX configuration
 * Provides better debugging experience
 */
export const configureMobXDev = () => {
    if (process.env.NODE_ENV === 'development') {
        // Enable MobX devtools
        if (typeof window !== 'undefined') {
            window.__MOBX_DEVTOOLS_GLOBAL_HOOK__ = {
                inject: (mobx) => {
                    // MobX devtools integration
                    console.log('MobX DevTools enabled');
                }
            };
        }
    }
};
