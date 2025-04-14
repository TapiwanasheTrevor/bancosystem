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
  const handleSubmit = () => {
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
                  {formData.employer === 'GOZ (Government of Zimbabwe) - SSB' && (
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
                </div>
              )}
              {formData.selectedProduct && (
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