import React from 'react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { DeliveryTrackingProps } from '../types';

const DeliveryTracking: React.FC<DeliveryTrackingProps> = ({
  onBack,
  trackingNumber,
  setTrackingNumber,
  deliveryDetails,
  isCheckingDelivery,
  deliveryError,
  onCheckDelivery
}) => {
  return (
    <StepContainer
      title="Track Your Delivery"
      subtitle="Enter your tracking number to see delivery status and updates"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8 space-y-6">
        <div className="space-y-4">
          <div className="flex flex-col">
            <label className="text-gray-700 mb-2">Tracking Number</label>
            <input
              type="text"
              value={trackingNumber}
              onChange={(e) => setTrackingNumber(e.target.value)}
              placeholder="Enter your tracking number"
              className="w-full p-3 border rounded-lg focus:ring focus:ring-emerald-400 outline-none"
            />
          </div>

          {deliveryError && (
            <div className="p-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
              {deliveryError}
            </div>
          )}

          <Button
            onClick={onCheckDelivery}
            variant="primary"
            disabled={isCheckingDelivery}
            fullWidth
          >
            {isCheckingDelivery ? 'Tracking...' : 'Track Delivery'}
          </Button>

          {deliveryDetails && (
            <div className="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
              <h3 className="text-lg font-semibold mb-4">Delivery Details</h3>

              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Status:</span>
                  <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                    deliveryDetails.status === 'delivered'
                      ? 'bg-green-100 text-green-800'
                      : deliveryDetails.status === 'delayed' || deliveryDetails.status === 'cancelled'
                        ? 'bg-red-100 text-red-800'
                        : deliveryDetails.status === 'out_for_delivery'
                          ? 'bg-orange-100 text-orange-800'
                          : deliveryDetails.status === 'in_transit'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-blue-100 text-blue-800'
                  }`}>
                    {deliveryDetails.status_label}
                  </span>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Tracking Number:</span>
                  <span className="font-medium">{deliveryDetails.tracking_number}</span>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Current Location:</span>
                  <span className="font-medium">{deliveryDetails.current_location || 'Not available'}</span>
                </div>

                <div className="flex items-center justify-between">
                  <span className="text-gray-600">Estimated Delivery:</span>
                  <span className="font-medium">{deliveryDetails.estimated_delivery_date || 'Not scheduled'}</span>
                </div>

                {deliveryDetails.product && (
                  <div className="flex items-center justify-between">
                    <span className="text-gray-600">Product:</span>
                    <span className="font-medium">{deliveryDetails.product.name}</span>
                  </div>
                )}
              </div>

              {/* Delivery Timeline */}
              {deliveryDetails.status_updates && deliveryDetails.status_updates.length > 0 && (
                <div className="mt-6">
                  <h4 className="font-medium mb-4">Delivery Progress</h4>
                  <div className="space-y-4">
                    {deliveryDetails.status_updates.map((update, index) => (
                      <div key={index} className="relative pl-8 pb-4">
                        {/* Timeline dot */}
                        <div className={`absolute left-0 top-0 h-4 w-4 rounded-full border-2 ${
                          index === 0 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'
                        }`}></div>

                        {/* Line connecting dots */}
                        {index < deliveryDetails.status_updates!.length - 1 && (
                          <div className="absolute left-2 top-4 h-full w-0 border-l border-gray-300"></div>
                        )}

                        {/* Content */}
                        <div>
                          <div className="flex items-center">
                            <span className={`inline-block px-2 py-1 text-xs font-medium rounded-full mr-2 ${
                              update.status === 'delivered'
                                ? 'bg-green-100 text-green-800'
                                : update.status === 'delayed' || update.status === 'cancelled'
                                  ? 'bg-red-100 text-red-800'
                                  : update.status === 'out_for_delivery'
                                    ? 'bg-orange-100 text-orange-800'
                                    : update.status === 'in_transit'
                                      ? 'bg-purple-100 text-purple-800'
                                      : 'bg-blue-100 text-blue-800'
                            }`}>
                              {update.status_label}
                            </span>
                            <span className="text-xs text-gray-500">{update.datetime}</span>
                          </div>
                          {update.location && (
                            <div className="mt-1 text-sm">{update.location}</div>
                          )}
                          {update.notes && (
                            <div className="mt-1 text-sm text-gray-600">{update.notes}</div>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </StepContainer>
  );
};

export default DeliveryTracking;