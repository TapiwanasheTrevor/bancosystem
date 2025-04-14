import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import CreditFlow from './Components/CreditFlow';
import DynamicFormWizard from './Components/FormWizard';
import KYCUpload from './Components/Upload/KYCUpload';
import { getFormIdByEmployer } from './Components/CreditFlow/utils';
import { FormData as CreditFlowFormData } from './Components/CreditFlow/types';

const App: React.FC = () => {
    // Application state
    const [showDynamicForm, setShowDynamicForm] = useState(false);
    const [showKYCUpload, setShowKYCUpload] = useState(false);
    const [selectedFormId, setSelectedFormId] = useState<string>('');
    const [questionnaireData, setQuestionnaireData] = useState<CreditFlowFormData | null>(null);
    const [insertId, setInsertId] = useState<string>('');
    const [referralCode, setReferralCode] = useState<string | null>(null);

    // Extract referral code from URL parameters when app loads
    useEffect(() => {
        const queryParams = new URLSearchParams(window.location.search);
        const ref = queryParams.get('ref');
        
        if (ref) {
            setReferralCode(ref);
            console.log('Referral code detected:', ref);
            localStorage.setItem('referralCode', ref);
        } else {
            // Check if there's a saved referral code in localStorage
            const savedReferralCode = localStorage.getItem('referralCode');
            if (savedReferralCode) {
                setReferralCode(savedReferralCode);
                console.log('Using saved referral code:', savedReferralCode);
            }
        }
    }, []);

    // Handle completion of the questionnaire flow
    const handleQuestionnaireComplete = (formData: CreditFlowFormData) => {
        // Use specificFormId if provided, otherwise determine based on rules
        const formId = formData.specificFormId || 
            getFormIdByEmployer(
                formData.employer,
                formData.hasAccount,
                formData.wantsAccount
            );

        // Store the data
        setQuestionnaireData(formData);
        setSelectedFormId(formId);
        setShowDynamicForm(true);
    };

    // Handle form submission
    const handleFormSubmit = (insertId: string) => {
        setInsertId(insertId);
        setShowDynamicForm(false);
        setShowKYCUpload(true);

        // Clear referral code from localStorage after successful form submission
        localStorage.removeItem('referralCode');
        setReferralCode(null);
    };

    // Handle KYC completion
    const handleKYCComplete = (kycData: any) => {
        console.log('KYC completed:', kycData);
        setShowKYCUpload(false);
    };

    // Handle going back from KYC
    const handleKYCBack = () => {
        setShowKYCUpload(false);
        setShowDynamicForm(true);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {!showDynamicForm && !showKYCUpload && (
                <CreditFlow onComplete={handleQuestionnaireComplete} />
            )}
            
            {showDynamicForm && !showKYCUpload && (
                <DynamicFormWizard 
                    formId={selectedFormId} 
                    initialData={questionnaireData}
                    onComplete={handleFormSubmit}
                />
            )}
            
            {showKYCUpload && (
                <KYCUpload 
                    onComplete={handleKYCComplete} 
                    onBack={handleKYCBack} 
                    insertId={insertId}
                />
            )}
        </div>
    );
};

// Ensure the root element exists in the HTML file
const container = document.getElementById('react-root');
if (!container) {
    throw new Error('Failed to find the root element. Make sure there is an element with id "react-root" in your HTML');
}

// Create a root and render the app
const root = createRoot(container);
root.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);

export default App;
