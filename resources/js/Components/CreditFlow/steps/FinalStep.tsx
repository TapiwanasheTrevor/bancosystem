import React from 'react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { FinalStepProps } from '../types';
// Import formatDateText directly
const formatDateText = (dateString: string): string => {
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch (error) {
    console.error('Error formatting date:', error);
    return dateString;
  }
};

const FinalStep: React.FC<FinalStepProps> = ({
  onNext,
  onBack,
  formData,
  isTerminated = false
}) => {
  // Define a success message based on the form type
  const getSuccessMessage = () => {
    // Check if this is a new account application
    if (formData.wantsAccount === 'yes' && formData.hasAccount !== 'yes') {
      return "Thank you for applying for your New ZB Account. We will inform you of your account number once your application is processed.";
    } 
    // For loan applications (has account already)
    else if (formData.hasAccount === 'yes' || formData.hasAccount === 'SSB') {
      return "Thank you for your application. Your application number will be provided shortly.";
    }
    // Default message
    return "Thank you for your application. We will process it shortly.";
  };
  
  const handleSubmit = () => {
    // Here we could show a success alert with the appropriate message
    // but for now, let's just call onNext which will complete the flow
    onNext();
  };

  return (
    <StepContainer
      title={isTerminated ? "Thank you for your interest" : "Application Summary"}
      subtitle={
        isTerminated
          ? "Unfortunately, we cannot proceed without a ZB Bank account"
          : "Please review your selections before proceeding"
      }
      showBackButton
      onBack={onBack}
    >
      {!isTerminated && (
        <div className="p-6 md:p-8 space-y-6">
          <div className="bg-gray-50 p-4 rounded-xl space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <h4 className="text-sm text-gray-500">Language</h4>
                <p className="font-medium">{formData.language}</p>
              </div>
              <div>
                <h4 className="text-sm text-gray-500">Application Type</h4>
                <p className="font-medium">{formData.intent}</p>
              </div>
              {formData.employer && (
                <div>
                  <h4 className="text-sm text-gray-500">Employer</h4>
                  <p className="font-medium">{formData.employer}</p>
                  {formData.employer === 'Government of Zimbabwe' && (
                    <p className="text-xs text-emerald-600">SSB form will be used</p>
                  )}
                  {formData.employer === 'GOZ - Pension' && (
                    <p className="text-xs text-emerald-600">Pensioners form will be used</p>
                  )}
                  {formData.employer === 'SME (Small & Medium Enterprises)' && (
                    <p className="text-xs text-emerald-600">
                      SME Business form will be used
                    </p>
                  )}
                  {formData.employer.startsWith('Parastatal - ') && (
                    <p className="text-xs text-emerald-600">
                      Parastatal Employee form will be used
                    </p>
                  )}
                  {formData.employer.startsWith('Corporate - ') && (
                    <p className="text-xs text-emerald-600">
                      Corporate Employee form will be used
                    </p>
                  )}
                </div>
              )}
              {/* Product details - hide for first-time account openers */}
              {formData.selectedProduct && !(formData.wantsAccount === 'yes' && formData.hasAccount !== 'yes') && (
                <>
                  <div className="col-span-2">
                    <h4 className="text-sm text-gray-500">Selected Product</h4>
                    <p className="font-medium">{formData.selectedProduct.product.name}</p>
                    <p className="text-sm text-gray-500">
                      Category: {formData.selectedProduct.category}
                    </p>
                  </div>
                  <div>
                    <h4 className="text-sm text-gray-500">Credit Terms</h4>
                    <p className="font-medium">
                      {formData.selectedProduct.selectedCreditOption.months} Months
                    </p>
                  </div>
                  <div>
                    <h4 className="text-sm text-gray-500">Monthly Installment</h4>
                    <p className="font-medium text-emerald-600">
                      ${formData.selectedProduct.selectedCreditOption.installment_amount}/month
                    </p>
                  </div>
                  <div>
                    <h4 className="text-sm text-gray-500">Payment Period</h4>
                    <p className="font-medium">
                      {formatDateText(formData.selectedProduct.loanStartDate)} to {formatDateText(formData.selectedProduct.loanEndDate)}
                    </p>
                  </div>
                </>
              )}

              {formData.hasAccount === 'yes' && (
                <div>
                  <h4 className="text-sm text-gray-500">Account Status</h4>
                  <p className="font-medium">Existing ZB Customer</p>
                </div>
              )}

              {formData.wantsAccount === 'yes' && (
                <div>
                  <h4 className="text-sm text-gray-500">Account Type</h4>
                  <p className="font-medium">{formData.accountType}</p>
                  <p className="text-sm text-gray-500">USD Account will be opened</p>
                </div>
              )}
            </div>
          </div>
          
          {/* Success message box */}
          <div className="bg-emerald-50 border border-emerald-200 p-4 rounded-lg text-emerald-800 mb-4">
            <p className="font-medium">{getSuccessMessage()}</p>
          </div>
          
          <Button
            onClick={handleSubmit}
            variant="primary"
            fullWidth
          >
            Click to Confirm Details and Proceed to Application Form
          </Button>
        </div>
      )}
    </StepContainer>
  );
};

export default FinalStep;