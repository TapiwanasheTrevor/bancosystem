/**
 * API services for the form wizard
 */
import { FormSchema, FormValues, FormSubmissionResponse } from '../types';
import { calculateLoanStartDate, calculateLoanEndDate, formatDateForDisplay } from './dateUtils';

/**
 * Fetch form schema data from server
 */
export const fetchFormData = async (formId: string): Promise<FormSchema> => {
  try {
    const response = await fetch(`/api/forms/${formId}`);
    
    if (!response.ok) {
      throw new Error(`Failed to fetch form data: ${response.status}`);
    }
    
    // Get the raw data from the server
    const data = await response.json();
    
    // Check if it has a nested 'form' property (from the JSON files)
    if (data?.form && Array.isArray(data.form.sections)) {
      return data.form;
    } 
    
    // Check if data itself is structured like a form (direct response)
    if (Array.isArray(data.sections)) {
      return data;
    }
    
    throw new Error('Invalid form structure received from server');
  } catch (error) {
    console.error('Error fetching form data:', error);
    throw error;
  }
};

/**
 * Initialize form values from product data
 */
export const getInitialFormValues = (
  initialData: any,
  formId: string
): FormValues => {
  try {
    const initialFormValues: FormValues = {};

    if (initialData?.applicationDetails) {
      const { name, phone, email, idNumber, ecNumber } = initialData.applicationDetails;
      
      // Handle name fields
      if (name) {
        const nameParts = name.split(' ');
        let firstName, surname;
        if (nameParts.length >= 2) {
          firstName = nameParts[0];
          surname = nameParts.slice(1).join(' ');
          initialFormValues['first-name'] = firstName;
          initialFormValues['surname'] = surname;
          initialFormValues['forename'] = firstName;
          initialFormValues['forenames'] = firstName;
        } else {
          firstName = name;
          initialFormValues['first-name'] = firstName;
          initialFormValues['forename'] = firstName;
          initialFormValues['forenames'] = firstName;
        }

        // Populate customer name fields for ALL forms
        initialFormValues['customerFirstName'] = firstName;
        initialFormValues['customerSurname'] = surname || '';
        initialFormValues['customerFullName'] = name;
        initialFormValues['customerForename'] = firstName;
        initialFormValues['customerForenames'] = firstName;
      }

      // Handle phone number
      if (phone) {
        // Ensure phone starts with 07 format
        let formattedPhone = phone;
        if (formattedPhone && !formattedPhone.startsWith('07') && formattedPhone !== '0') {
          if (formattedPhone.startsWith('0') && formattedPhone.length > 1) {
            formattedPhone = '07' + formattedPhone.substring(2);
          } else if (!formattedPhone.startsWith('0')) {
            formattedPhone = '07' + formattedPhone;
          }
        }

        initialFormValues['cell-number'] = formattedPhone;
        initialFormValues['phone'] = formattedPhone;
        initialFormValues['phone-number'] = formattedPhone;
        initialFormValues['customerCellNumber'] = formattedPhone;
      }

      // Handle email
      if (email) {
        initialFormValues['email-address'] = email;
        initialFormValues['email'] = email;
        initialFormValues['customerEmail'] = email;
      }

      // Handle ID number
      if (idNumber) {
        initialFormValues['id-number'] = idNumber;
        initialFormValues['national-id'] = idNumber;
        initialFormValues['customerIdNumber'] = idNumber;
      }

      // Handle EC number
      if (ecNumber) {
        initialFormValues['ec-number'] = ecNumber;
        initialFormValues['employment-code'] = ecNumber;
        initialFormValues['ec-check-letter'] = '';
        initialFormValues['employment-number'] = ecNumber;
      }
    }

    // Handle employer information
    if (initialData?.employer) {
      initialFormValues['employer-name'] = initialData.employer;
      initialFormValues['employer'] = initialData.employer;
      initialFormValues['customerEmployer'] = initialData.employer;

      // Populate SSB form fields 
      if (initialData.employer === 'GOZ (Government of Zimbabwe) - SSB') {
        initialFormValues['formType'] = 'ssb';
      }
    }

    // Handle product and credit option
    if (initialData?.selectedProduct) {
      const { product, selectedCreditOption } = initialData.selectedProduct;

      if (product) {
        initialFormValues['purpose/asset-applied-for'] = product.name || '';
        initialFormValues['productDescription'] = product.description || product.name || '';
      }

      if (selectedCreditOption) {
        const startDate = calculateLoanStartDate();
        const endDate = calculateLoanEndDate(startDate, selectedCreditOption.months);

        initialFormValues['productInstallment'] = selectedCreditOption.installment_amount ||
          (parseFloat(selectedCreditOption.final_price) / selectedCreditOption.months).toFixed(2);
        initialFormValues['productLoanPeriod'] = `${selectedCreditOption.months} months`;
        initialFormValues['productLoanPeriodMonths'] = selectedCreditOption.months;
        initialFormValues['autoLoanStartDate'] = startDate;
        initialFormValues['autoLoanEndDate'] = endDate;
        initialFormValues['autoLoanStartDateText'] = formatDateForDisplay(startDate);
        initialFormValues['autoLoanEndDateText'] = formatDateForDisplay(endDate);

        if (selectedCreditOption.final_price) {
          initialFormValues['loan-amount'] = selectedCreditOption.final_price;
          initialFormValues['applied-amount'] = selectedCreditOption.final_price;
        }
      }
    }

    // Set default account currency type
    initialFormValues['account-type'] = 'USD Account';
    initialFormValues['currency-of-account'] = 'USD';

    return initialFormValues;
  } catch (error) {
    console.error('Error initializing form values:', error);
    return {};
  }
};

/**
 * Submit form data to server
 */
export const submitFormData = async (
  formId: string,
  formValues: FormValues,
  formSchema: FormSchema,
  agentId: string | null,
  initialData: any
): Promise<FormSubmissionResponse> => {
  try {
    console.log('Submitting form with ID:', formId);
    console.log('Form values:', formValues);
    
    const formData = new FormData();

    // Add form ID and form type
    formData.append('form_id', formId);
    formData.append('form_type', formId); // Using formId as the form type
    
    // Add agent ID/referral code if present
    if (agentId) {
      formData.append('agentId', agentId);
      formData.append('referralCode', agentId);
    }
    
    // Add product data if available
    if (initialData?.selectedProduct) {
      formData.append('productData', JSON.stringify(initialData.selectedProduct));
    }

    // If we have applicant name, use it
    const firstName = formValues['first-name'] || formValues['forename'] || formValues['customerFirstName'] || '';
    const surname = formValues['surname'] || formValues['last-name'] || formValues['customerSurname'] || '';
    if (firstName || surname) {
      formData.append('applicantName', `${firstName} ${surname}`.trim());
    }

    // Add ID number if available
    if (formValues['id-number'] || formValues['national-id'] || formValues['customerIdNumber']) {
      formData.append('applicantIdNumber', 
        formValues['id-number'] || formValues['national-id'] || formValues['customerIdNumber']);
    }

    // Add all form values
    for (const [key, value] of Object.entries(formValues)) {
      if (value === undefined || value === null) continue;
      
      // Handle files
      if (value instanceof File) {
        formData.append(key, value);
      } 
      // Handle simple values
      else if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
        formData.append(key, String(value));
      } 
      // Handle objects by converting to JSON
      else if (typeof value === 'object') {
        formData.append(key, JSON.stringify(value));
      }
    }

    // Use the correct API endpoint from the routes
    const response = await fetch('/api/submit-form', {
      method: 'POST',
      body: formData,
      // Don't follow redirects automatically
      redirect: 'manual'
    });

    // Handle redirect (302) as success since many Laravel apps redirect after form submission
    if (response.status === 302 || response.type === 'opaqueredirect') {
      console.log('Form submission resulted in redirect - treating as success');
      return { insertId: '0', success: true };
    }

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Server response error:', errorText);
      throw new Error(`Failed to submit form: ${response.status} - ${errorText}`);
    }

    // Try to parse as JSON, but don't fail if it's not JSON
    try {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const responseData = await response.json();
        console.log('Form submission successful (JSON):', responseData);
        
        if (!responseData.insertId && responseData.id) {
          // Use 'id' if 'insertId' is not available
          return { insertId: responseData.id, success: true };
        }
        
        return { insertId: responseData.insertId || '0', success: true };
      } else {
        // Handle non-JSON response as success
        console.log('Form submission successful (non-JSON)');
        return { insertId: '0', success: true };
      }
    } catch (parseError) {
      // If we can't parse the response as JSON, still treat as success
      console.warn('Could not parse response as JSON:', parseError);
      console.log('Treating as successful submission anyway');
      return { insertId: '0', success: true };
    }
  } catch (error) {
    console.error('Error submitting form:', error);
    throw error;
  }
};