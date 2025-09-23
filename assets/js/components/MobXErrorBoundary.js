import React from 'react';

/**
 * Error boundary component for MobX stores
 * Catches and displays errors that occur in MobX stores
 * Note: Error boundaries must be class components and don't need observer
 */
class MobXErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        // Update state so the next render will show the fallback UI
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        // Log the error to console in development
        if (process.env.NODE_ENV === 'development') {
            console.error('MobX Error Boundary caught an error:', error, errorInfo);
        }
        
        // You could also log to an error reporting service here
        // logErrorToService(error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            // Fallback UI
            return (
                <div className="container mt-5">
                    <div className="alert alert-danger" role="alert">
                        <h4 className="alert-heading">Something went wrong!</h4>
                        <p>An error occurred in the application. Please refresh the page or contact support if the problem persists.</p>
                        <hr />
                        <div className="mb-0">
                            <button 
                                className="btn btn-outline-danger btn-sm"
                                onClick={() => window.location.reload()}
                            >
                                Refresh Page
                            </button>
                        </div>
                        {process.env.NODE_ENV === 'development' && (
                            <details className="mt-3">
                                <summary>Error Details (Development Only)</summary>
                                <pre className="mt-2 p-2 bg-light border rounded">
                                    {this.state.error?.toString()}
                                </pre>
                            </details>
                        )}
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}

export default MobXErrorBoundary;
