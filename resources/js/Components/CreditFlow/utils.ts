import { FormData } from './types';

/**
 * Calculates the progress percentage based on completed form fields
 */
export const calculateFormProgress = (formData: FormData): number => {
  const fields = [
    formData.language,
    formData.intent,
    formData.employer,
    formData.selectedProduct,
    formData.hasAccount,
    formData.wantsAccount
  ];

  const totalFields = fields.length;
  const filledFields = fields.filter(field => {
    if (typeof field === 'string') return field !== '';
    if (field === null) return false;
    return true;
  }).length;

  return (filledFields / totalFields) * 100;
};

/**
 * Maps employer type to form ID
 */
export const getFormIdByEmployer = (
  employerId: string,
  hasAccount: string,
  wantsAccount: string
): string => {
  // Define the mapping from employer ID to base form type
  const employerFormMapping: Record<string, string> = {
    'Government of Zimbabwe': 'ssb_direct_loan', // Changed name and using direct loan for SSB
    'GOZ - ZAPPA': 'check-account',
    'GOZ - Pension': 'pensioners_loan_account',
    'Town Council': 'check-account',
    'Parastatal': 'check-account', // Base type for parastatals
    'Mission and Private Schools': 'check-account',
    'I am an Entrepreneur': 'smes_business_account_opening',
    'Large Corporate': 'check-account', // Base type for large corporates
    'Other': 'check-account',
  };

  // Get the base form type for the selected employer
  // Handle dynamic employer IDs like "Parastatal - Name (Acronym)"
  let baseFormType = employerFormMapping[employerId];

  // If the exact employerId is not found, check for dynamic types
  if (!baseFormType) {
    if (employerId.startsWith('Parastatal - ')) {
      baseFormType = 'check-account';
    } else if (employerId.startsWith('Corporate - ')) {
      baseFormType = 'check-account';
    } else {
      // Default to check-account if employer ID is unknown
      baseFormType = 'check-account';
    }
  }

  // Special case for SSB employees - bypass account creation, go straight to loan
  if (baseFormType === 'ssb_direct_loan') {
    return 'account_holder_loan_application'; // Skip account creation for SSB
  }

  // Determine the final form ID based on the base form type and account status
  if (baseFormType === 'check-account') {
    if (hasAccount === 'yes') {
      return 'account_holder_loan_application';
    } else if (wantsAccount === 'yes') {
      return 'individual_account_opening';
    } else {
      // Default for check-account if neither hasAccount nor wantsAccount is 'yes'
      // This case might need refinement based on actual flow, defaulting to individual for now
      return 'individual_account_opening';
    }
  } else {
    // For other base form types, return the type directly
    return baseFormType;
  }
};

/**
 * Gets account type text based on employer
 */
export const getAccountTypeByEmployer = (employer: string): string => {
  if (employer === 'SME (Small & Medium Enterprises)' || employer === 'I am an Entrepreneur') {
    return 'SME Transaction Account';
  }
  
  // Check if employer is a parastatal
  if (employer.startsWith('Parastatal - ')) {
    return 'Parastatal Employee Transaction Account';
  }
  
  // Check if employer is a corporate
  if (employer.startsWith('Corporate - ')) {
    return 'Corporate Employee Transaction Account';
  }
  
  return 'New ZB Transaction Account';
};