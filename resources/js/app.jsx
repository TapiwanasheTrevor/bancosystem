import React from 'react';
import ReactDOM from 'react-dom/client';
import CreditApplicationFlow from './components/CreditApplicationFlow';

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('react-root');
    if (rootElement) {
        const root = ReactDOM.createRoot(rootElement);
        root.render(<CreditApplicationFlow/>);
    }
});
