import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface DeliveryStatus {
  status: string;
  status_label: string;
  location?: string;
  notes?: string;
  datetime: string;
}

interface DeliveryDetails {
  tracking_number: string;
  status: string;
  status_label: string;
  status_color: string;
  current_location?: string;
  estimated_delivery_date?: string;
  actual_delivery_date?: string;
  product: {
    name: string;
    image?: string;
  };
  status_updates: DeliveryStatus[];
}

interface DeliveryTrackerProps {
  formUuid?: string;
  initialTrackingNumber?: string;
}

const DeliveryTracker: React.FC<DeliveryTrackerProps> = ({ formUuid, initialTrackingNumber }) => {
  const [trackingNumber, setTrackingNumber] = useState(initialTrackingNumber || '');
  const [delivery, setDelivery] = useState<DeliveryDetails | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [deliveries, setDeliveries] = useState<any[]>([]);

  // Load deliveries for a form if formUuid is provided
  useEffect(() => {
    if (formUuid) {
      loadFormDeliveries();
    }
  }, [formUuid]);

  // Load deliveries for a specific form
  const loadFormDeliveries = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await axios.post('/api/user-deliveries', { form_uuid: formUuid });
      if (response.data.success) {
        setDeliveries(response.data.deliveries || []);
        
        // If there's only one delivery, load its details automatically
        if (response.data.deliveries.length === 1) {
          await loadDeliveryDetails(response.data.deliveries[0].id);
        }
      } else {
        setError('No deliveries found for this application');
      }
    } catch (err) {
      setError('Failed to load deliveries. Please try again later.');
      console.error('Error loading deliveries:', err);
    } finally {
      setLoading(false);
    }
  };

  // Load details for a specific delivery
  const loadDeliveryDetails = async (deliveryId: number) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await axios.post('/api/delivery-details', { delivery_id: deliveryId });
      if (response.data.success) {
        setDelivery(response.data.delivery);
      } else {
        setError('Could not find delivery details');
      }
    } catch (err) {
      setError('Failed to load delivery details. Please try again later.');
      console.error('Error loading delivery details:', err);
    } finally {
      setLoading(false);
    }
  };

  // Track using tracking number
  const trackDelivery = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!trackingNumber.trim()) {
      setError('Please enter a tracking number');
      return;
    }
    
    setLoading(true);
    setError(null);
    
    try {
      const response = await axios.post('/api/track-delivery', { tracking_number: trackingNumber });
      if (response.data.success) {
        setDelivery(response.data.delivery);
      } else {
        setError('Tracking number not found');
      }
    } catch (err) {
      setError('Failed to track delivery. Please check your tracking number and try again.');
      console.error('Error tracking delivery:', err);
    } finally {
      setLoading(false);
    }
  };

  // Get status color class
  const getStatusColorClass = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-gray-100 text-gray-800';
      case 'processing': return 'bg-blue-100 text-blue-800';
      case 'dispatched': return 'bg-indigo-100 text-indigo-800';
      case 'in_transit': return 'bg-purple-100 text-purple-800';
      case 'at_station': return 'bg-yellow-100 text-yellow-800';
      case 'out_for_delivery': return 'bg-orange-100 text-orange-800';
      case 'delivered': return 'bg-green-100 text-green-800';
      case 'delayed': return 'bg-red-100 text-red-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-semibold mb-4">Product Delivery Tracking</h2>
      
      {/* Tracking Form */}
      {!formUuid && (
        <form onSubmit={trackDelivery} className="mb-6">
          <div className="flex items-center gap-2">
            <input
              type="text"
              value={trackingNumber}
              onChange={(e) => setTrackingNumber(e.target.value)}
              placeholder="Enter tracking number"
              className="flex-grow p-2 border border-gray-300 rounded"
            />
            <button
              type="submit"
              disabled={loading}
              className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
            >
              {loading ? 'Tracking...' : 'Track'}
            </button>
          </div>
        </form>
      )}
      
      {/* Form deliveries selector */}
      {formUuid && deliveries.length > 1 && (
        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">Select Product Delivery:</label>
          <select 
            className="w-full p-2 border border-gray-300 rounded"
            onChange={(e) => loadDeliveryDetails(parseInt(e.target.value))}
          >
            <option value="">Select a delivery to track</option>
            {deliveries.map(delivery => (
              <option key={delivery.id} value={delivery.id}>
                {delivery.product.name} - {delivery.tracking_number} ({delivery.status_label})
              </option>
            ))}
          </select>
        </div>
      )}
      
      {/* Error message */}
      {error && (
        <div className="mb-4 p-3 bg-red-100 text-red-700 rounded">
          {error}
        </div>
      )}
      
      {/* Loading indicator */}
      {loading && !delivery && (
        <div className="flex justify-center items-center py-8">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
      )}
      
      {/* Delivery details */}
      {delivery && (
        <div>
          <div className="mb-6 pb-4 border-b">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p className="text-gray-500 text-sm">Tracking Number:</p>
                <p className="font-semibold">{delivery.tracking_number}</p>
              </div>
              <div>
                <p className="text-gray-500 text-sm">Status:</p>
                <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColorClass(delivery.status)}`}>
                  {delivery.status_label}
                </span>
              </div>
              <div>
                <p className="text-gray-500 text-sm">Current Location:</p>
                <p>{delivery.current_location || 'Not available'}</p>
              </div>
              <div>
                <p className="text-gray-500 text-sm">Estimated Delivery:</p>
                <p>{delivery.estimated_delivery_date || 'Not scheduled'}</p>
              </div>
            </div>
            
            {delivery.product && (
              <div className="mt-4">
                <p className="text-gray-500 text-sm">Product:</p>
                <p className="font-medium">{delivery.product.name}</p>
              </div>
            )}
          </div>
          
          {/* Timeline */}
          <div>
            <h3 className="font-semibold mb-3">Delivery Progress</h3>
            <div className="space-y-4">
              {delivery.status_updates.map((update, index) => (
                <div key={index} className="relative pl-8 pb-4">
                  {/* Timeline dot */}
                  <div className={`absolute left-0 top-0 h-4 w-4 rounded-full border-2 ${
                    index === 0 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'
                  }`}></div>
                  
                  {/* Line connecting dots */}
                  {index < delivery.status_updates.length - 1 && (
                    <div className="absolute left-2 top-4 h-full w-0 border-l border-gray-300"></div>
                  )}
                  
                  {/* Content */}
                  <div>
                    <span className={`inline-block px-2 py-1 text-xs font-medium rounded-full mb-1 ${getStatusColorClass(update.status)}`}>
                      {update.status_label}
                    </span>
                    <div className="text-xs text-gray-500">{update.datetime}</div>
                    {update.location && <div className="mt-1 text-sm">{update.location}</div>}
                    {update.notes && <div className="mt-1 text-sm text-gray-600">{update.notes}</div>}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DeliveryTracker;