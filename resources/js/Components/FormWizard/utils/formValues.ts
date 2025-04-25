/**
 * Form value handling utilities
 */
import { Field, FormValues, Section } from '../types';
import { formatIdNumber, formatPhoneNumber } from './validation';
import { getFieldId } from './validation';

/**
 * Extract default values from form schema
 */
export const getDefaultValues = (sections: Section[]): FormValues => {
  const defaultValues: FormValues = {};

  sections.forEach(section => {
    if (!section.fields) return;

    section.fields.forEach(field => {
      if (field.default !== undefined) {
        const fieldId = getFieldId(field);
        defaultValues[fieldId] = field.default;
      }
    });
  });

  return defaultValues;
};

/**
 * Process input value based on field type and field ID
 */
export const processInputValue = (fieldId: string, value: any, field?: Field): any => {
  // Don't process null or undefined values
  if (value === null || value === undefined) {
    return value;
  }

  // If this is a name field or the field label contains "name"
  const isNameField = ['first-name', 'forename', 'forenames', 'surname', 'last-name', 'full-name', 'maiden-name', 'other-names', 'name', 'spouse', 'supervisor'].includes(fieldId.toLowerCase()) ||
      ['First Name', 'Forename', 'Forenames', 'Surname', 'Last Name', 'Full Name', 'Maiden Name', 'Other Names', 'Name', 'Spouse', 'Supervisor'].includes(fieldId) ||
      (field?.label && (
        field.label.toLowerCase().includes('name') || 
        field.label.toLowerCase().includes('supervisor') ||
        field.label.toLowerCase().includes('spouse')
      ));

  // Auto capitalize names
  if (isNameField && typeof value === 'string') {
    // Capitalize first letter of each word
    return value.replace(/\b\w/g, (char) => char.toUpperCase());
  }

  // Special handling for cell numbers to begin with +263 7
  if (['cell-number', 'phone-number', 'phone', 'mobile', 'telephone', 'contact'].includes(fieldId.toLowerCase()) ||
      ['Cell Number', 'Phone Number', 'Phone', 'Mobile', 'Telephone', 'Contact Number'].includes(fieldId) ||
      (field?.label && (
        field.label.toLowerCase().includes('phone') || 
        field.label.toLowerCase().includes('mobile') ||
        field.label.toLowerCase().includes('cell') ||
        field.label.toLowerCase().includes('telephone') ||
        field.label.toLowerCase().includes('contact number')
      )) ||
      field?.isPhoneNumber) {
    if (typeof value === 'string') {
      return formatPhoneNumber(value);
    }
  }

  // Special handling for ID number fields
  if (['id-number', 'national-id', 'identity-number'].includes(fieldId.toLowerCase()) ||
      ['ID Number', 'National ID', 'Identity Number'].includes(fieldId) ||
      fieldId.includes('id-number') || fieldId.includes('national-id') ||
      (field?.label && field.label.toLowerCase().includes('id number'))) {
    if (typeof value === 'string') {
      // Format ID number
      value = formatIdNumber(value);
      
      // The check letter (letter in position 8) is already capitalized in formatIdNumber
      
      return value;
    }
  }

  return value;
};

/**
 * Update related form values when a value changes
 */
export const updateRelatedValues = (
  fieldId: string,
  value: any,
  formValues: FormValues,
  field?: Field
): FormValues => {
  const newValues = { ...formValues, [fieldId]: value };

  // Handle bindTo - direct binding from this field to another field
  if (field?.bindTo && field.bindTo in formValues) {
    newValues[field.bindTo] = value;
  }

  // Special case for all forms to sync customer data across sections

  // Handle first name variants
  if (['first-name', 'forename', 'forenames'].includes(fieldId.toLowerCase()) ||
      ['First Name', 'Forename', 'Forenames'].includes(fieldId)) {
    newValues['customerFirstName'] = value;
    newValues['customerForename'] = value;
    newValues['customerForenames'] = value;

    // Update full name in all possible fields
    const surname = formValues['surname'] || formValues['customerSurname'] || '';
    const fullName = `${value} ${surname}`.trim();
    newValues['customerFullName'] = fullName;
    newValues['full-name'] = fullName;
  }

  // Handle surname variants
  if (['surname', 'last-name'].includes(fieldId.toLowerCase()) ||
      ['Surname', 'Last Name'].includes(fieldId)) {
    newValues['customerSurname'] = value;

    // Update full name in all possible fields
    const firstName = formValues['first-name'] || formValues['forename'] || formValues['customerFirstName'] || '';
    const fullName = `${firstName} ${value}`.trim();
    newValues['customerFullName'] = fullName;
    newValues['full-name'] = fullName;
  }

  // Handle ID Number variants
  if (['id-number', 'national-id', 'identity-number'].includes(fieldId.toLowerCase()) ||
      ['ID Number', 'National ID', 'Identity Number'].includes(fieldId)) {
    newValues['customerIdNumber'] = value;
    newValues['national-id'] = value;
    newValues['id-number'] = value;
  }

  // Handle phone number variants
  if (['cell-number', 'phone-number', 'phone', 'mobile'].includes(fieldId.toLowerCase()) ||
      ['Cell Number', 'Phone Number', 'Phone', 'Mobile'].includes(fieldId)) {
    newValues['customerCellNumber'] = value;
    newValues['cell-number'] = value;
    newValues['phone'] = value;
  }

  // Handle email variants
  if (['email-address', 'email'].includes(fieldId.toLowerCase()) ||
      ['Email Address', 'Email'].includes(fieldId)) {
    newValues['customerEmail'] = value;
    newValues['email'] = value;
    newValues['email-address'] = value;
  }

  // Handle ministry/employer specific fields
  if (fieldId === 'Name of Responsible Ministry' || fieldId === 'name-of-responsible-ministry') {
    newValues['customerMinistry'] = value;
  }

  if (['employer-name', 'employer'].includes(fieldId.toLowerCase()) ||
      ['Employer Name', 'Employer'].includes(fieldId)) {
    newValues['customerEmployer'] = value;
  }

  // Handle address fields
  if (['address', 'residential-address'].includes(fieldId.toLowerCase()) ||
      ['Address', 'Residential Address'].includes(fieldId)) {
    newValues['customerAddress'] = value;
  }

  // Handle address type selection (urban/rural)
  if (fieldId.toLowerCase() === 'address-type' || fieldId === 'Address Type') {
    if (value === 'Urban') {
      // Show urban address fields
      newValues['isUrbanAddress'] = true;
      newValues['isRuralAddress'] = false;
      
      // Reset rural-specific flags
      newValues['showProvinceField'] = false;
      newValues['showDistrictField'] = false;
      newValues['showWardField'] = false;
      
      // Enable city field
      newValues['showCityField'] = true;
      newValues['showStreetAddressField'] = true;
      
      // Preserve existing values but mark fields as visible/hidden
      newValues['addressFieldsConfig'] = {
        type: 'urban',
        visibleFields: ['city', 'street', 'suburb', 'building'],
        hiddenFields: ['province', 'district', 'ward', 'village', 'chief']
      };
    } else if (value === 'Rural') {
      // Show rural address fields
      newValues['isUrbanAddress'] = false;
      newValues['isRuralAddress'] = true;

      // Show province selection
      newValues['showProvinceField'] = true;
      
      // Hide city field
      newValues['showCityField'] = false;
      newValues['showStreetAddressField'] = false;
      
      // Configure fields visibility
      newValues['addressFieldsConfig'] = {
        type: 'rural',
        visibleFields: ['province', 'district', 'ward', 'village', 'chief'],
        hiddenFields: ['city', 'street', 'suburb', 'building']
      };
    }
  }
  
  // Handle province selection for rural addresses
  if ((fieldId.toLowerCase() === 'province' || fieldId === 'Province') && 
      currentValues['isRuralAddress']) {
    newValues['selectedProvince'] = value;
    
    // Enable district dropdown based on province
    newValues['showDistrictField'] = true;
    
    // Clear any previously selected district and ward
    newValues['selectedDistrict'] = '';
    newValues['showWardField'] = false;
    
    // Special case for Harare and Bulawayo (no districts/wards needed)
    if (value === 'Harare' || value === 'Bulawayo') {
      newValues['isMetropolitanProvince'] = true;
      newValues['showDistrictField'] = false;
      newValues['showWardField'] = false;
    } else {
      newValues['isMetropolitanProvince'] = false;
    }
  }
  
  // Handle district selection for rural addresses
  if ((fieldId.toLowerCase() === 'district' || fieldId === 'District') && 
      currentValues['isRuralAddress'] && 
      !currentValues['isMetropolitanProvince']) {
    newValues['selectedDistrict'] = value;
    newValues['showWardField'] = true;
  }

  return newValues;
};

/**
 * Handle special field onChange events
 */
export const handleFieldOnChange = (
  field: Field,
  value: any,
  currentValues: FormValues
): FormValues => {
  const newValues = { ...currentValues };
  
  try {
    const { action, dependency, values, target } = field.onChange || {};

    // Check if this is a Next of Kin name field to prevent using applicant's name
    if (field.label &&
        (field.label.toLowerCase().includes('next of kin') ||
         field.label.toLowerCase().includes('kin name') ||
         field.label.toLowerCase().includes('spouse') ||
         (field.label.toLowerCase().includes('full name') && 
          field.id && (
            field.id.toLowerCase().includes('nextofkin') ||
            field.id.toLowerCase().includes('next-of-kin') ||
            field.id.toLowerCase().includes('kin') ||
            field.id.toLowerCase().includes('spouse')
          )))) {

      // Get applicant's name from form values
      const applicantFirstName = currentValues['first-name'] ||
                               currentValues['forename'] ||
                               currentValues['customerFirstName'] || '';

      const applicantSurname = currentValues['surname'] ||
                             currentValues['last-name'] ||
                             currentValues['customerSurname'] || '';

      // If input matches applicant's name, add error flag and return unchanged values
      if ((applicantFirstName && value && 
           value.toLowerCase().includes(applicantFirstName.toLowerCase())) ||
          (applicantSurname && value && 
           value.toLowerCase().includes(applicantSurname.toLowerCase()))) {
        
        // Add an error flag to the form values that the UI can detect and show
        newValues['_nextOfKinNameError'] = true;
        newValues['_nextOfKinErrorField'] = field.id || field.label;
        newValues['_nextOfKinErrorMessage'] = "Next of kin cannot be the same as the applicant";
        
        // Return the current values unchanged but with the error flags
        return { ...currentValues, ...newValues };
      } else {
        // Clear any previous error if this field now has a valid value
        if (newValues['_nextOfKinErrorField'] === (field.id || field.label)) {
          newValues['_nextOfKinNameError'] = false;
          newValues['_nextOfKinErrorField'] = '';
          newValues['_nextOfKinErrorMessage'] = '';
        }
      }
    }

    // Handle relationship selection based on gender
    if (action === 'updateNextOfKin' && dependency && values) {
      const dependencyField = dependency.toLowerCase().replace(/\s+/g, '-');
      const dependencyValue = currentValues[dependencyField];

      if (dependencyValue && values[dependencyValue] && field.label.toLowerCase() === 'gender') {
        const gender = value;
        const relationship = values[dependencyValue][gender];
        
        if (relationship) {
          newValues['relationship'] = relationship;
        }
      }
    }

    return newValues;
  } catch (error) {
    console.error('Error in handleFieldOnChange:', error);
    return currentValues;
  }
};