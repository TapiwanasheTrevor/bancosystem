import React, { useState, useEffect, useCallback } from 'react';
import { ChevronLeft, ChevronRight, AlertCircle } from 'lucide-react';
import Lottie from 'react-lottie';
import completeAnimation from './assets/complete.json';
import { FormData, Field, Section } from './types';
import SectionRenderer from './components/SectionRenderer';
import StepIndicator from './components/StepIndicator';
import NavigationButtons from './components/NavigationButtons';
import Modal from '../Modal';
import { fetchFormData, submitFormData } from './utils/api';
import {
  processInputValue as handleInputChange,
  updateRelatedValues,
  getDefaultValues
} from './utils/formValues';
import { getFieldId as generateFieldId } from './utils/validation';
import { validateSection } from './utils/validation';
import { 
  calculateLoanStartDate, 
  calculateLoanEndDate, 
  formatDateForDisplay 
} from './utils/dateUtils';

interface FormWizardProps {
  formId: string;
  initialData?: any;
  onComplete: (insertId: string) => void;
}

const FormWizard: React.FC<FormWizardProps> = ({
  formId,
  initialData,
  onComplete
}) => {
  // Form state
  const [formData, setFormData] = useState<FormData | null>(null);
  const [currentSection, setCurrentSection] = useState(0);
  const [formValues, setFormValues] = useState<Record<string, any>>({});
  const [sectionVariants, setSectionVariants] = useState<Record<string, string>>({});
  const [directorCount, setDirectorCount] = useState<number>(1);
  
  // Validation state
  const [fieldValidation, setFieldValidation] = useState<Record<string, boolean>>({});
  const [attemptedValidation, setAttemptedValidation] = useState<boolean>(false);
  
  // UI state
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  
  // Modal state
  const [showConfirmDialog, setShowConfirmDialog] = useState(false);
  const [confirmDialogMessage, setConfirmDialogMessage] = useState('');
  const [confirmDialogAction, setConfirmDialogAction] = useState<() => void>(() => {});
  
  // Track referral code if present
  const [agentId, setAgentId] = useState<string | null>(null);

  // Initialize form data
  useEffect(() => {
    const loadFormData = async () => {
      setLoading(true);
      setError(null);
      
      try {
        const formSchema = await fetchFormData(formId);
        
        if (!formSchema || !Array.isArray(formSchema.sections)) {
          throw new Error('Invalid form structure received from server');
        }
        
        console.log(`Form data loaded for ${formId}:`, formSchema);
        setFormData(formSchema);
        
        // Initialize form with default values from form definition
        const defaultValues = getDefaultValues(formSchema.sections);
        setFormValues(prevValues => ({ ...defaultValues, ...prevValues }));
      } catch (err) {
        console.error("Error fetching form data:", err);
        setError(err instanceof Error ? err.message : 'Failed to load form data');
      } finally {
        setLoading(false);
      }
    };
    
    // Check for referral code in URL
    const checkReferralCode = () => {
      const urlParams = new URLSearchParams(window.location.search);
      const referral = urlParams.get('referral');
      if (referral) {
        setAgentId(referral);
        setFormValues(prev => ({ ...prev, referralCode: referral }));
      }
    };
    
    loadFormData();
    checkReferralCode();
  }, [formId]);
  
  // Initialize form with product data when available
  useEffect(() => {
    if (formData && initialData) {
      try {
        const initialFormValues: Record<string, any> = {};
    
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
        }
    
        // Set default account currency type
        initialFormValues['account-type'] = 'USD Account';
        initialFormValues['currency-of-account'] = 'USD';
        
        setFormValues(prev => ({ ...prev, ...initialFormValues }));
      } catch (error) {
        console.error('Error formatting form values:', error);
      }
    }
  }, [formData, initialData]);


  // Handle field value changes
  const handleFieldChange = useCallback((fieldId: string, value: any, field?: Field) => {
    // Process the value (format, validate, etc.)
    const processedValue = handleInputChange(fieldId, value, field);
    
    console.log(`Changing field ${fieldId} to:`, processedValue);
    
    setFormValues(prev => {
      // Update form values with processed input and any related fields
      const updatedValues = updateRelatedValues(fieldId, processedValue, prev, field);
      
      // Handle field-specific onChange behaviors
      const { action, values } = field?.onChange || {};
      
      // Process specific form field actions
      if (action === 'updateNextOfKinSection' && values) {
        const selectedVariant = values[processedValue] || values.default;
        setSectionVariants(prevVariants => ({
          ...prevVariants,
          ['nextOfKinSection']: selectedVariant
        }));
      }
      
      if (action === 'generateDirectorSections' && field?.label?.includes('Directors')) {
        const count = parseInt(processedValue as string) || 1;
        setDirectorCount(count);
      }
      
      if (action === 'confirmProceed' && 
          values && 
          processedValue in values) {
        if (values[processedValue] === 'showConfirmation') {
          setConfirmDialogMessage('Are you sure you don\'t want to proceed with the application?');
          setConfirmDialogAction(() => () => {
            window.location.href = '/';
          });
          setShowConfirmDialog(true);
        }
      }
      
      // For debugging - if this is a declaration/confirmation checkbox
      if (field?.type === 'checkbox' && (
          field.label?.toLowerCase().includes('confirm') || 
          field.label?.toLowerCase().includes('agree') || 
          field.label?.toLowerCase().includes('declaration')
      )) {
        console.log('Declaration/confirmation checkbox changed:', processedValue);
        console.log('Updated form values:', updatedValues);
      }
      
      return updatedValues;
    });
  }, []);

  // Handle field validation changes
  const handleValidationChange = useCallback((fieldId: string, isValid: boolean) => {
    setFieldValidation(prev => ({
      ...prev,
      [fieldId]: isValid
    }));
  }, []);

  // Move to next section with validation
  const handleNext = useCallback(() => {
    if (!formData) return;
    
    const currentSectionData = formData.sections[currentSection];
    if (!currentSectionData) return;
    
    // Validate all required fields in the current section
    setAttemptedValidation(true);
    const validation = validateSection(currentSectionData, formValues);
    setFieldValidation(validation);
    
    // Check if all fields are valid
    const isValid = Object.values(validation).every(valid => valid);
    
    if (isValid) {
      setAttemptedValidation(false);
      setCurrentSection(prev => Math.min(prev + 1, (formData?.sections?.length || 1) - 1));
    } else {
      // Scroll to the first invalid field
      const firstInvalidField = document.querySelector('[class*="border-red-300"]');
      if (firstInvalidField) {
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  }, [currentSection, formData, formValues, fieldValidation]);

  // Move to previous section
  const handlePrevious = useCallback(() => {
    if (currentSection > 0) {
      setAttemptedValidation(false);
      setCurrentSection(prev => prev - 1);
    }
  }, [currentSection]);

  // Submit the form
  const handleSubmit = useCallback(async () => {
    if (!formData) return;
    
    // Validate the current section one last time
    const currentSectionData = formData.sections[currentSection];
    if (!currentSectionData) return;
    
    setAttemptedValidation(true);
    const validation = validateSection(currentSectionData, formValues);
    setFieldValidation(validation);
    
    // Check if all fields are valid
    const isValid = Object.values(validation).every(valid => valid);
    
    if (!isValid) {
      // Scroll to the first invalid field
      const firstInvalidField = document.querySelector('[class*="border-red-300"]');
      if (firstInvalidField) {
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }
    
    // Prepare and submit form data
    setSubmitting(true);
    setError(null);
    
    try {
      const { insertId, success } = await submitFormData(formId, formValues, formData, agentId, initialData);
      
      if (success) {
        console.log('Form submission successful! Setting success state and calling onComplete with insertId:', insertId);
        setSuccess(true);
        if (onComplete) {
          onComplete(insertId || '0');
        }
      } else {
        throw new Error('Form submission did not return success=true');
      }
    } catch (err) {
      console.error("Error submitting form:", err);
      setError(err instanceof Error ? err.message : 'An error occurred while submitting the form');
      setAttemptedValidation(false);
    } finally {
      setSubmitting(false);
    }
  }, [formData, currentSection, formValues, fieldValidation, formId, agentId, onComplete, initialData]);

  // If still loading, show loading state
  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center p-8 min-h-[400px]">
        <div className="w-12 h-12 border-4 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p className="text-gray-600">Loading form...</p>
      </div>
    );
  }

  // If there's an error loading the form
  if (error && !formData) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <AlertCircle className="mx-auto h-10 w-10 text-red-500 mb-2" />
        <h3 className="text-lg font-medium text-red-800">Failed to load form</h3>
        <p className="text-red-600 mt-1">{error}</p>
        <button
          onClick={() => window.location.reload()}
          className="mt-4 bg-red-100 hover:bg-red-200 text-red-800 py-2 px-4 rounded-md transition-colors"
        >
          Try Again
        </button>
      </div>
    );
  }

  // If form submission was successful
  if (success) {
    return (
      <div className="flex flex-col items-center justify-center p-8 min-h-[400px]">
        <div className="w-56 h-56 mb-6">
          <Lottie
            options={{
              loop: false,
              autoplay: true,
              animationData: completeAnimation,
              rendererSettings: {
                preserveAspectRatio: 'xMidYMid slice'
              }
            }}
          />
        </div>
        <h2 className="text-2xl font-semibold text-emerald-800 mb-2">Application Submitted Successfully!</h2>
        <p className="text-gray-600 text-center max-w-md">
          Thank you for your application. We will review your information and contact you soon.
        </p>
      </div>
    );
  }

  // Determine if this is the last section
  const isLastSection = formData ? currentSection === formData.sections.length - 1 : false;
  const currentSectionData = formData?.sections[currentSection];
  const sectionVariant = currentSectionData?.id 
    ? sectionVariants[currentSectionData.id] 
    : sectionVariants[currentSectionData?.title?.toLowerCase().replace(/\s+/g, '-') || ''];
  
  // Determine if this is the Declaration section (which usually contains a checkbox that needs special handling)
  const isDeclarationSection = currentSectionData?.title === 'Declaration';
  
  // Find the declaration checkbox field ID - looking at both fieldsets and direct fields
  let declarationCheckboxFieldId = '';
  if (isDeclarationSection && currentSectionData?.fields) {
    // Look for fieldsets with declaration checkboxes
    const declarationFields = currentSectionData.fields.flatMap(field => {
      if (field.type === 'fieldset' && field.children) {
        return field.children.filter(child => 
          child.type === 'checkbox' && 
          (child.label?.toLowerCase().includes('confirm') || 
           child.label?.toLowerCase().includes('agree') || 
           child.label?.toLowerCase().includes('declaration'))
        );
      }
      // Also check direct fields
      if (field.type === 'checkbox' && 
         (field.label?.toLowerCase().includes('confirm') || 
          field.label?.toLowerCase().includes('agree') || 
          field.label?.toLowerCase().includes('declaration'))) {
        return [field];
      }
      return [];
    });
    
    if (declarationFields.length > 0) {
      const field = declarationFields[0];
      declarationCheckboxFieldId = field.id || field.label?.toLowerCase().replace(/\s+/g, '-') || '';
      console.log('Found declaration checkbox field ID:', declarationCheckboxFieldId);
    }
  }
  
  // Debug output the values for the declaration section
  if (isDeclarationSection) {
    console.log('Declaration section detected');
    console.log('Current form values:', formValues);
    console.log('Declaration checkbox ID:', declarationCheckboxFieldId);
    console.log('Declaration checkbox value:', formValues[declarationCheckboxFieldId]);
    console.log('Submit button should be disabled:', 
      isDeclarationSection && declarationCheckboxFieldId && !formValues[declarationCheckboxFieldId]);
  }
  
  return (
    <div className="bg-white rounded-xl shadow-sm p-6">
      {formData && (
        <>
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-gray-900 mb-2">{formData.title}</h1>
            {formData.description && (
              <p className="text-gray-600">{formData.description}</p>
            )}
          </div>
          
          <StepIndicator 
            currentStep={currentSection}
            totalSteps={formData.sections.length} 
          />
          
          <div className="my-8">
            {currentSectionData && (
              <SectionRenderer
                section={currentSectionData}
                formValues={formValues}
                onChange={handleFieldChange}
                onValidationChange={handleValidationChange}
                attemptedValidation={attemptedValidation}
                fieldValidation={fieldValidation}
                sectionVariant={sectionVariant}
                directorCount={directorCount}
              />
            )}
          </div>
          
          <NavigationButtons
            currentStep={currentSection}
            totalSteps={formData.sections.length}
            onBack={handlePrevious}
            onNext={isLastSection ? handleSubmit : handleNext}
            isNextDisabled={false} // Remove the conditional disabling for now to allow submission
            isSubmitting={submitting}
          />
          
          {/* Error message for form submission */}
          {error && (
            <div className="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
              <div className="flex">
                <AlertCircle className="h-5 w-5 text-red-500 mr-2" />
                <p className="text-red-800">{error}</p>
              </div>
            </div>
          )}
          
          {/* Confirmation Dialog */}
          {showConfirmDialog && (
            <Modal
              title="Confirmation"
              message={confirmDialogMessage}
              onConfirm={() => {
                confirmDialogAction();
                setShowConfirmDialog(false);
              }}
              onCancel={() => setShowConfirmDialog(false)}
            />
          )}
        </>
      )}
    </div>
  );
};

export default FormWizard;