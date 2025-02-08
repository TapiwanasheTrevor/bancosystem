import React, { useState } from 'react';
import CreditApplicationFlow from './Components/CreditApplicationFlow';
import DynamicFormWizard from './Components/DynamicFormWizard';

function App() {
    const [showDynamicForm, setShowDynamicForm] = useState(false);
    const [selectedFormId, setSelectedFormId] = useState('');
    const [questionnaireData, setQuestionnaireData] = useState(null);

    const determineFormId = (formData) => {
        const { employer, hasAccount, wantsAccount } = formData;

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

    const handleQuestionnaireComplete = (formData) => {
        const formId = determineFormId(formData);
        setQuestionnaireData(formData);
        setSelectedFormId(formId);
        setShowDynamicForm(true);
    };

    return (
        <div className="min-h-screen bg-gray-50">
            {!showDynamicForm ? (
                <CreditApplicationFlow onComplete={handleQuestionnaireComplete} />
            ) : (
                <DynamicFormWizard
                    formId={selectedFormId}
                    initialData={questionnaireData}
                />
            )}
        </div>
    );
}

export default App;
