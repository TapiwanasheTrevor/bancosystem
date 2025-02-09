import React from 'react';
import {createRoot} from 'react-dom/client';
import App from './app';


// Make sure the container element exists
const container = document.getElementById('react-root');

if (!container) {
    throw new Error(
        'Failed to find the root element. Make sure there is an element with id "react-root" in your HTML'
    );
}

// Create a root
const root = createRoot(container);

// Initial render
root.render(
    <React.StrictMode>
        <App/>
    </React.StrictMode>
);
