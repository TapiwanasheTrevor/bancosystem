import React from 'react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { StatusCheckProps } from '../types';
// Import formatDateText directly from parent component
const formatDateText = (dateString: string): string => {
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch (error) {
    console.error('Error formatting date:', error);
    return dateString;
  }
};

const StatusCheck: React.FC<StatusCheckProps> = ({
  onNext,
  onBack,
  referenceNumber,
  setReferenceNumber,
  applicationStatus,
  isCheckingStatus,
  statusError,
  onCheckStatus
}) => {
  return (
    <StepContainer
      title="Check Your Application Status"
      subtitle="Enter your reference number to check the status of your application"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8 space-y-6">
        <div className="space-y-4">
          <div className="flex flex-col">
            <label className="text-gray-700 mb-2">Reference Number</label>
            <input
              type="text"
              value={referenceNumber}
              onChange={(e) => setReferenceNumber(e.target.value)}
              placeholder="Enter your reference number"
              className="w-full p-3 border rounded-lg focus:ring focus:ring-emerald-400 outline-none"
            />
          </div>

          {statusError && (
            <div className="p-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
              {statusError}
            </div>
          )}

          <Button
            onClick={onCheckStatus}
            variant="primary"
            disabled={isCheckingStatus}
            fullWidth
          >
            {isCheckingStatus ? 'Checking...' : 'Check Status'}
          </Button>

          {applicationStatus && (
            <div className="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
              <h3 className="text-lg font-semibold mb-4">Application Details</h3>

              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Status:</span>
                  <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                    applicationStatus.status === 'approved'
                      ? 'bg-green-100 text-green-800'
                      : applicationStatus.status === 'rejected'
                        ? 'bg-red-100 text-red-800'
                        : 'bg-yellow-100 text-yellow-800'
                  }`}>
                    {applicationStatus.status.toUpperCase()}
                  </span>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Reference Number:</span>
                  <span className="font-medium">{applicationStatus.uuid}</span>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Application Date:</span>
                  <span className="font-medium">{formatDateText(applicationStatus.created_at)}</span>
                </div>

                {applicationStatus.product && (
                  <>
                    <div className="flex items-center justify-between">
                      <span className="text-gray-600">Product:</span>
                      <span className="font-medium">{applicationStatus.product.name}</span>
                    </div>

                    <div className="flex items-center justify-between">
                      <span className="text-gray-600">Category:</span>
                      <span className="font-medium">{applicationStatus.product.category}</span>
                    </div>

                    <div className="flex items-center justify-between">
                      <span className="text-gray-600">Monthly Payment:</span>
                      <span className="font-medium">${applicationStatus.product.installment_amount}/month</span>
                    </div>

                    <div className="flex items-center justify-between">
                      <span className="text-gray-600">Payment Period:</span>
                      <span className="font-medium">{applicationStatus.product.months} months</span>
                    </div>
                  </>
                )}
              </div>

              {applicationStatus.status === 'approved' && (
                <div className="mt-4 p-3 bg-green-50 text-green-700 rounded-lg border border-green-200">
                  Your application has been approved! Our representative will contact you soon with
                  next steps.
                </div>
              )}

              {applicationStatus.status === 'rejected' && (
                <div className="mt-4 p-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
                  Unfortunately, your application was not approved at this time. Please contact our
                  customer service for more details.
                </div>
              )}

              {applicationStatus.status === 'pending' && (
                <div className="mt-4 p-3 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                  Your application is still being processed. Please check back later for updates.
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </StepContainer>
  );
};

export default StatusCheck;