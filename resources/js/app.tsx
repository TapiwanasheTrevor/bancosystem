import React, {useState, useEffect} from 'react';
import {createRoot} from 'react-dom/client';
import CreditApplicationFlow from './Components/CreditApplicationFlow';
import DynamicFormWizard from './Components/DynamicFormWizard';
import KYCUpload from './Components/KYCUpload';

// Define the shape of form data
interface FormData {
    employer: string;
    hasAccount: string;
    wantsAccount: string;
    specificFormId?: string; // Optional parameter to explicitly specify form ID
}

const App: React.FC = () => {
    const [showDynamicForm, setShowDynamicForm] = useState(false);
    const [showKYCUpload, setShowKYCUpload] = useState(false);
    const [selectedFormId, setSelectedFormId] = useState<string>('');
    const [questionnaireData, setQuestionnaireData] = useState<FormData | null>(null);
    const [insertId, setInsertId] = useState<string>('');
    const [referralCode, setReferralCode] = useState<string | null>(null);
    
    // Extract referral code from URL parameters when app loads
    useEffect(() => {
        const queryParams = new URLSearchParams(window.location.search);
        const ref = queryParams.get('ref');
        if (ref) {
            setReferralCode(ref);
            console.log('Referral code detected:', ref);
            
            // Optional: Save referral code to localStorage for persistence across sessions
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

    const determineFormId = (formData: FormData): string => {
        const {employer, hasAccount, wantsAccount} = formData;

        if (wantsAccount === 'yes' || (hasAccount === 'no' && wantsAccount === 'yes')) {
            switch (employer) {
                case 'GOZ (Government of Zimbabwe) - SSB':
                    return 'ssb_account_opening_form';
                case 'GOZ - ZAPPA':
                case 'GOZ - Pension':
                    return 'pensioners_loan_account';
                case 'SME (Small & Medium Enterprises)':
                    return 'smes_business_account_opening';
                default:
                    return 'individual_account_opening';
            }
        } else if (hasAccount === 'yes') {
            return 'account_holder_loan_application';
        }

        return 'individual_account_opening';
    };

    const handleQuestionnaireComplete = (formData: any) => {
        // Use specificFormId if provided, otherwise determine based on rules
        const formId = formData.specificFormId || determineFormId(formData);
        
        // Pass through all form data
        setQuestionnaireData(formData);
        
        setSelectedFormId(formId);
        setShowDynamicForm(true);
    };

    const handleFormSubmit = (insertId: string) => {
        setInsertId(insertId);
        setShowDynamicForm(false);
        setShowKYCUpload(true);
        
        // Clear referral code from localStorage after successful form submission
        // to prevent it from being used for future submissions
        localStorage.removeItem('referralCode');
        setReferralCode(null);
    };

    const handleKYCComplete = (kycData: any) => {
        // Handle KYC completion logic here
        console.log('KYC completed:', kycData);
        setShowKYCUpload(false);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {!showDynamicForm && !showKYCUpload && (
                <CreditApplicationFlow onComplete={handleQuestionnaireComplete}/>
            )}
            {showDynamicForm && !showKYCUpload && (
                <DynamicFormWizard formId={selectedFormId} initialData={questionnaireData}
                                   onComplete={handleFormSubmit}/>
            )}
            {showKYCUpload && (
                <KYCUpload onComplete={handleKYCComplete} onBack={() => setShowKYCUpload(false)} insertId={insertId}/>
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
        <App/>
    </React.StrictMode>
);

export default App;
