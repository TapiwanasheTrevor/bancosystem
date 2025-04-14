/**
 * Validation utilities for form fields
 */

/**
 * Validates an email address format
 */
export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

/**
 * Validates a Zimbabwe ID number format (e.g., 45-123456-T-78)
 */
export const isValidZimbabweID = (id: string): boolean => {
  // Support for format like 45-123456-T-78 or cleaned version
  const idRegex = /^(\d{2}-\d{6}-[A-Z]-\d{2}|\d{8}[A-Z]\d{2})$/;
  return idRegex.test(id);
};

/**
 * Validates a phone number (Zimbabwe format)
 */
export const isValidPhone = (phone: string): boolean => {
  // Support for format like +263 77 123 4567 or 077 123 4567
  const phoneRegex = /^(\+263|0)7[0-9]{1}[0-9]{7}$/;
  return phoneRegex.test(cleanPhoneNumber(phone));
};

/**
 * Validates required fields
 */
export const isNotEmpty = (value: any): boolean => {
  if (value === null || value === undefined) return false;
  if (typeof value === 'string') return value.trim().length > 0;
  if (typeof value === 'number') return true;
  if (typeof value === 'boolean') return true;
  if (Array.isArray(value)) return value.length > 0;
  if (typeof value === 'object') return Object.keys(value).length > 0;
  return false;
};

/**
 * Cleans a phone number by removing spaces, dashes, etc.
 */
export const cleanPhoneNumber = (phone: string): string => {
  return phone.replace(/\s+|-|\(|\)|\+/g, '');
};

/**
 * Formats a Zimbabwe ID number to standard format (XX-XXXXXX-X-XX)
 */
export const formatZimbabweID = (id: string): string => {
  // First, clean the ID by removing any existing dashes
  const cleanedId = id.replace(/-/g, '');
  
  // If it's already in the correct format, return it
  if (/^\d{2}-\d{6}-[A-Z]-\d{2}$/.test(id)) {
    return id;
  }
  
  // If the cleaned ID is in the numerical+letter+numerical format (e.g., 08123456A12)
  if (/^\d{8}[A-Z]\d{2}$/.test(cleanedId)) {
    return `${cleanedId.substring(0, 2)}-${cleanedId.substring(2, 8)}-${cleanedId.substring(8, 9)}-${cleanedId.substring(9)}`;
  }
  
  // If we can't format it properly, return the original
  return id;
};

/**
 * Formats a phone number to Zimbabwe standard format
 */
export const formatPhoneNumber = (phone: string): string => {
  const cleaned = cleanPhoneNumber(phone);
  
  // Convert to +263 format if it starts with 0
  let formatted = cleaned;
  if (cleaned.startsWith('0')) {
    formatted = `+263${cleaned.substring(1)}`;
  }
  
  // Format as +263 XX XXX XXXX
  if (formatted.startsWith('+263') && formatted.length >= 12) {
    return `${formatted.substring(0, 4)} ${formatted.substring(4, 6)} ${formatted.substring(6, 9)} ${formatted.substring(9)}`;
  }
  
  return phone;
};

/**
 * Validates form data against validation rules
 */
export const validateFormData = (data: Record<string, any>, validationRules: Record<string, any>): Record<string, string> => {
  const errors: Record<string, string> = {};
  
  Object.keys(validationRules).forEach(field => {
    const value = data[field];
    const rules = validationRules[field];
    
    if (rules.required && !isNotEmpty(value)) {
      errors[field] = 'This field is required';
    } else if (value) {
      if (rules.email && !isValidEmail(value)) {
        errors[field] = 'Please enter a valid email address';
      }
      
      if (rules.phone && !isValidPhone(value)) {
        errors[field] = 'Please enter a valid phone number';
      }
      
      if (rules.idNumber && !isValidZimbabweID(value)) {
        errors[field] = 'Please enter a valid Zimbabwe ID number';
      }
      
      if (rules.minLength && value.length < rules.minLength) {
        errors[field] = `This field must be at least ${rules.minLength} characters`;
      }
      
      if (rules.maxLength && value.length > rules.maxLength) {
        errors[field] = `This field must be no more than ${rules.maxLength} characters`;
      }
    }
  });
  
  return errors;
};