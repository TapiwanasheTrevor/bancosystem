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
  employer: string,
  hasAccount: string,
  wantsAccount: string
): string => {
  // If user has account, use account holder loan application
  if (hasAccount === 'yes') {
    return 'account_holder_loan_application';
  }
  
  // If user wants a new account or needs one
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
  }
  
  // Default to individual account opening
  return 'individual_account_opening';
};

/**
 * Gets account type text based on employer
 */
export const getAccountTypeByEmployer = (employer: string): string => {
  if (employer === 'SME (Small & Medium Enterprises)') {
    return 'SME Transaction Account';
  }
  
  return 'Individual Transaction Account';
};