import React, {useState} from 'react';
import {createRoot} from 'react-dom/client';
import CreditApplicationFlow from './Components/CreditApplicationFlow';
import DynamicFormWizard from './Components/DynamicFormWizard';
import KYCUpload from './Components/KYCUpload';

// Define the shape of form data
interface FormData {
    employer: string;
    hasAccount: string;
    wantsAccount: string;
}

const App: React.FC = () => {
    const [showDynamicForm, setShowDynamicForm] = useState(false);
    const [showKYCUpload, setShowKYCUpload] = useState(false);
    const [selectedFormId, setSelectedFormId] = useState<string>('');
    const [questionnaireData, setQuestionnaireData] = useState<FormData | null>(null);
    const [insertId, setInsertId] = useState<string>('');

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

    const handleQuestionnaireComplete = (formData: FormData) => {
        const formId = determineFormId(formData);
        setQuestionnaireData(formData);
        setSelectedFormId(formId);
        setShowDynamicForm(true);
    };

    const handleFormSubmit = (insertId: string) => {
        setInsertId(insertId);
        setShowDynamicForm(false);
        setShowKYCUpload(true);
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
