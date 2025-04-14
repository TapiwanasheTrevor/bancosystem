/**
 * API service functions for the Credit Application Flow
 */
import { 
  Category, 
  CategoriesResponse, 
  CategoryResponse, 
  ApplicationStatus,
  DeliveryDetails
} from './types';

const API_BASE_URL = `${import.meta.env.VITE_API_BASE_URL || ''}/api`;

/**
 * Fetches categories based on intent (hirePurchase or other)
 */
export const fetchCategories = async (
  intent: string,
  categoryId?: number
): Promise<{ data: Category | Category[] }> => {
  try {
    // Use different API endpoint for hire purchase products
    let apiUrl: string;
    
    if (intent === 'hirePurchase') {
      apiUrl = categoryId
        ? `${API_BASE_URL}/hirepurchase/categories/${categoryId}`
        : `${API_BASE_URL}/hirepurchase/categories`;
    } else {
      apiUrl = categoryId
        ? `${API_BASE_URL}/categories/${categoryId}`
        : `${API_BASE_URL}/categories`;
    }
    
    const response = await fetch(apiUrl);
    if (!response.ok) {
      throw new Error(`Failed to fetch categories: ${response.status}`);
    }
    
    const responseData = await response.json();
    
    if (categoryId) {
      const categoryData = responseData as CategoryResponse;
      return { data: categoryData.data };
    } else {
      const categoriesData = responseData as CategoriesResponse;
      return { data: categoriesData.data };
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
    throw error;
  }
};

/**
 * Checks the status of an application by reference number
 */
export const checkApplicationStatus = async (
  referenceNumber: string
): Promise<ApplicationStatus> => {
  try {
    const response = await fetch(`${API_BASE_URL}/check-application-status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ reference_number: referenceNumber }),
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to check application status');
    }
    
    return data.data;
  } catch (error) {
    console.error('Error checking application status:', error);
    throw error;
  }
};

/**
 * Tracks delivery status by tracking number
 */
export const trackDelivery = async (
  trackingNumber: string
): Promise<DeliveryDetails> => {
  try {
    const response = await fetch(`${API_BASE_URL}/track-delivery`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ tracking_number: trackingNumber }),
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to find tracking information');
    }
    
    return data.delivery;
  } catch (error) {
    console.error('Error checking delivery status:', error);
    throw error;
  }
};