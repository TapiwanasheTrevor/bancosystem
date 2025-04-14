/**
 * Form validation utilities
 */
import {Field, FormValues, FieldValidation} from '../types';

/**
 * Get a field ID from a field object
 */
export const getFieldId = (field: Field): string => {
    return field.id || field.label.toLowerCase().replace(/\s+/g, '-');
};

/**
 * Validate all fields in a section
 */
export const validateSection = (
    section: { fields?: Field[] },
    formValues: FormValues
): FieldValidation => {
    const validation: FieldValidation = {};

    if (!section.fields) return validation;

    const validateFields = (fields: Field[]) => {
        fields.forEach(field => {
            // Get field ID before checking type
            const fieldId = getFieldId(field);

            // Handle fieldsets as containers, but still validate the fieldset itself if required
            if (field.type === 'fieldset') {
                // Mark the fieldset as valid by default
                validation[fieldId] = true;

                // Process its children if any
                if (field.children && Array.isArray(field.children)) {
                    validateFields(field.children);
                }
                return;
            }

            const value = formValues[fieldId];

            // Skip validation for non-required fields
            if (!field.required) {
                validation[fieldId] = true;
                return;
            }

            // Skip validation for readonly fields
            if (field.readOnly) {
                validation[fieldId] = true;
                return;
            }

            // Special validation for different field types
            switch (field.type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                case 'select':
                    validation[fieldId] = !!value;
                    break;

                case 'checkbox':
                    validation[fieldId] = value === true;
                    break;

                case 'file':
                    validation[fieldId] = value !== null && value !== undefined;
                    break;

                case 'signature':
                    validation[fieldId] = !!value;
                    break;

                // Skip validation for non-input field types
                case 'heading':
                case 'paragraph':
                case 'divider':
                case 'button':
                    validation[fieldId] = true;
                    break;

                default:
                    validation[fieldId] = true;
            }
        });
    };

    validateFields(section.fields);
    return validation;
};

/**
 * Format an ID number with proper separators
 */
export const formatIdNumber = (value: string): string => {
    // If empty, just return empty
    if (!value) return '';

    // Remove any existing hyphens to work with clean value
    let cleanValue = value.replace(/-/g, '');

    // Simple formatting based on length
    let formattedValue = cleanValue;

    if (cleanValue.length > 0) {
        // Format first 2 digits
        if (cleanValue.length >= 2) {
            formattedValue = cleanValue.substring(0, 2);

            // Add first hyphen and more digits
            if (cleanValue.length > 2) {
                // Add digits after first hyphen (middle section)
                const middleSection = cleanValue.substring(2);

                // Check if we have a letter in the next section
                const letterMatch = middleSection.match(/[A-Za-z]/);

                if (letterMatch && letterMatch.index !== undefined) {
                    // We found a letter - ensure middle section is exactly 6 digits
                    const letterIndex = letterMatch.index;

                    // Format first section with hyphen
                    formattedValue = cleanValue.substring(0, 2) + '-';

                    // Add middle digits (up to 6 digits only)
                    const middleDigits = middleSection.substring(0, letterIndex);
                    formattedValue += middleDigits.length > 6 ? middleDigits.substring(0, 6) : middleDigits;

                    // Add hyphen and letter
                    formattedValue += '-' + middleSection.charAt(letterIndex);

                    // Add final section if we have it
                    if (middleSection.length > letterIndex + 1) {
                        formattedValue += '-' + middleSection.substring(letterIndex + 1, letterIndex + 3);
                    }
                } else {
                    // No letter found yet, just show what we have with first hyphen
                    // Limit middle section to 6 digits
                    if (middleSection.length > 6) {
                        formattedValue = cleanValue.substring(0, 2) + '-' + middleSection.substring(0, 6);
                    } else {
                        formattedValue = cleanValue.substring(0, 2) + '-' + middleSection;
                    }
                }
            }
        }
    }

    return formattedValue;
};

/**
 * Validate an ID number format
 */
export const validateIdNumber = (value: string): boolean => {
    // Check if it's in format XX-XXXXXX-X-XX
    // (e.g., 63-123456-F-42)
    const pattern = /^\d{2}-\d{6,7}[A-Z]\d{2}$/;
    return pattern.test(value);
};

/**
 * Validate a phone number format
 */
export const validatePhoneNumber = (value: string): boolean => {
    // Should start with 07 and have 10 digits total
    const pattern = /^07\d{8}$/;
    return pattern.test(value.replace(/\s+/g, ''));
};

/**
 * Format a phone number for display
 */
export const formatPhoneNumber = (value: string): string => {
    if (!value) return '';

    // Ensure it starts with 07
    let formattedValue = value;
    if (!value.startsWith('07') && value !== '0') {
        if (value.startsWith('0') && value.length > 1) {
            formattedValue = '07' + value.substring(2);
        } else if (!value.startsWith('0')) {
            formattedValue = '07' + value;
        }
    }

    return formattedValue;
};
