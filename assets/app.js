/*
 * Welcome to your main app file! This is your entry point for your React app.
 * If you want to add any JavaScript (or other assets), you can import them here.
 */

// Import React and ReactDOM
import React from 'react';
import ReactDOM from 'react-dom/client';

// Import CSS
import './css/app.css';

// Import components
import App from './js/App';

// Initialize React app
document.addEventListener('DOMContentLoaded', function() {
    const rootElement = document.getElementById('react-app');
    if (rootElement) {
        const root = ReactDOM.createRoot(rootElement);
        root.render(<App />);
    }
});