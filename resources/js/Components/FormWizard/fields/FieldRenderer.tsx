import React from 'react';
import { Field, FieldConfig } from '../types';
import BranchLocator from './BranchLocator';
import Select from './Select';
import SignaturePad from './SignaturePad';
import DateField from './DateField';
import FileUpload from './FileUpload';
import TextArea from './TextArea';
import PhoneInput from './PhoneInput';
import { AlertCircle } from 'lucide-react';

interface FieldRendererProps {
  field: Field;
  value: any;
  onChange: (fieldId: string, value: any, field?: Field) => void;
  onValidationChange: (fieldId: string, isValid: boolean) => void;
  sectionId?: string;
  attemptedValidation: boolean;
  fieldValidation: Record<string, boolean>;
  formValues?: Record<string, any>;
}

const FieldRenderer: React.FC<FieldRendererProps> = ({
  field,
  value,
  onChange,
  onValidationChange,
  sectionId = '',
  attemptedValidation,
  fieldValidation,
  formValues = {},
}) => {
  if (!field) return null;

  const fieldId = field.id || `${field.label?.toLowerCase().replace(/\s+/g, '-')}`;
  const isReadOnly = field.readOnly || false;
  // More compact input styles with reduced padding
  const baseInputStyles = "w-full p-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300 text-sm";
  const invalidStyles = attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300 ring-1 ring-red-300' : '';
  const readOnlyStyles = isReadOnly ? 'bg-gray-100 cursor-not-allowed' : 'hover:border-emerald-300';
  
  const handleChange = (newValue: any) => {
    try {
      console.log(`Field ${fieldId} changing to:`, newValue);
      
      // Update the form value
      onChange(fieldId, newValue, field);
      
      // Simple validation
      const isValid = field.required ? Boolean(newValue) : true;
      onValidationChange(fieldId, isValid);
      
      console.log(`Field ${fieldId} validation:`, isValid);
    } catch (error) {
      console.error(`Error in field ${fieldId} handleChange:`, error);
    }
  };

  // Handle different field types
  switch (field.type) {
    case 'subtitle':
      return (
        <div className="mb-4 mt-6">
          <h3 className="text-lg font-medium text-gray-800 border-b pb-2">{field.label}</h3>
        </div>
      );

    case 'text':
    case 'email':
    case 'number':
    case 'tel':
      // Check if this is a phone number field either by explicit flag or by label
      const isPhoneField = 
        field.isPhoneNumber || 
        field.type === 'tel' || 
        field.label.toLowerCase().includes('phone') || 
        field.label.toLowerCase().includes('mobile') || 
        field.label.toLowerCase().includes('telephone') || 
        field.label.toLowerCase().includes('cell no') || 
        field.label.toLowerCase().includes('contact number');
      
      if (isPhoneField) {
        return (
          <PhoneInput
            fieldId={fieldId}
            label={field.label}
            required={field.required || false}
            onChange={handleChange}
            value={value || ''}
            isInvalid={attemptedValidation && !fieldValidation[fieldId] && field.required}
            readOnly={isReadOnly}
            placeholder={field.placeholder || ''}
          />
        );
      }
      
      // Regular text/email/number input
      return (
        <div className="mb-3">
          <label className="block text-xs font-medium mb-1 text-gray-700" htmlFor={fieldId}>
            {field.label} {field.required && <span className="text-emerald-500">*</span>}
          </label>
          <input
            type={field.type}
            id={fieldId}
            className={`${baseInputStyles} ${readOnlyStyles} ${invalidStyles}`}
            required={field.required}
            onChange={(e) => handleChange(e.target.value)}
            value={value || ''}
            placeholder={field.placeholder || ''}
            readOnly={isReadOnly}
          />
          {attemptedValidation && field.required && !fieldValidation[fieldId] && (
            <div className="text-red-500 text-sm mt-1 flex items-center">
              <AlertCircle size={14} className="mr-1" />
              This field is required
            </div>
          )}
          
          {/* Next of Kin validation error message */}
          {formValues._nextOfKinNameError && 
           formValues._nextOfKinErrorField === fieldId && (
            <div className="text-red-500 text-sm mt-1 flex items-center">
              <AlertCircle size={14} className="mr-1" />
              {formValues._nextOfKinErrorMessage || "Next of kin cannot be the same as the applicant"}
            </div>
          )}
        </div>
      );

    case 'textarea':
      return (
        <TextArea 
          fieldId={fieldId}
          label={field.label}
          required={field.required || false}
          placeholder={field.placeholder || ''}
          onChange={handleChange}
          value={value || ''}
          readOnly={isReadOnly}
          isInvalid={attemptedValidation && !fieldValidation[fieldId] && field.required}
        />
      );

    case 'select':
      if (field.component === 'branchLocator') {
        return (
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
              {field.label} {field.required && <span className="text-emerald-500">*</span>}
            </label>
            <BranchLocator
              fieldId={fieldId}
              required={field.required || false}
              onChange={handleChange}
              value={value}
            />
            {attemptedValidation && field.required && !fieldValidation[fieldId] && (
              <div className="text-red-500 text-sm mt-1 flex items-center">
                <AlertCircle size={14} className="mr-1" />
                Please select a branch
              </div>
            )}
          </div>
        );
      }
      
      return (
        <div className="mb-4">
          <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
            {field.label} {field.required && <span className="text-emerald-500">*</span>}
          </label>
          <Select
            fieldId={fieldId}
            options={field.options || []}
            required={field.required || false}
            onChange={handleChange}
            value={value}
            placeholder={field.placeholder || `Select ${field.label}`}
          />
          {attemptedValidation && field.required && !fieldValidation[fieldId] && (
            <div className="text-red-500 text-sm mt-1 flex items-center">
              <AlertCircle size={14} className="mr-1" />
              Please make a selection
            </div>
          )}
        </div>
      );

    case 'date':
      return (
        <DateField
          fieldId={fieldId}
          label={field.label}
          required={field.required || false}
          onChange={handleChange}
          value={value}
          isInvalid={attemptedValidation && !fieldValidation[fieldId] && field.required}
        />
      );

    case 'file':
      return (
        <FileUpload
          fieldId={fieldId}
          label={field.label}
          required={field.required || false}
          onChange={handleChange}
          value={value}
          accept={field.accept || ''}
          isInvalid={attemptedValidation && !fieldValidation[fieldId] && field.required}
        />
      );

    case 'signature':
      return (
        <SignaturePad
          fieldId={fieldId}
          label={field.label}
          required={field.required || false}
          onChange={handleChange}
          value={value}
          isInvalid={attemptedValidation && !fieldValidation[fieldId] && field.required}
        />
      );

    case 'radio':
      return (
        <div className="mb-4">
          <fieldset>
            <legend className="block text-sm font-medium mb-2 text-gray-700">
              {field.label} {field.required && <span className="text-emerald-500">*</span>}
            </legend>
            <div className="mt-2 space-y-2">
              {field.options?.map((option, index) => (
                <div key={index} className="flex items-center">
                  <input
                    id={`${fieldId}-${index}`}
                    name={fieldId}
                    type="radio"
                    value={option}
                    checked={value === option}
                    onChange={() => handleChange(option)}
                    className="h-4 w-4 text-emerald-500 focus:ring-emerald-400"
                  />
                  <label htmlFor={`${fieldId}-${index}`} className="ml-3 text-sm text-gray-700">
                    {option}
                  </label>
                </div>
              ))}
            </div>
          </fieldset>
          {attemptedValidation && field.required && !fieldValidation[fieldId] && (
            <div className="text-red-500 text-sm mt-1 flex items-center">
              <AlertCircle size={14} className="mr-1" />
              Please select an option
            </div>
          )}
        </div>
      );

    case 'checkbox':
      // Determine if this is a declaration/confirmation checkbox
      const isDeclaration = field.label?.toLowerCase().includes('confirm') || 
                           field.label?.toLowerCase().includes('agree') || 
                           field.label?.toLowerCase().includes('declaration');
      
      // Apply special styling for declarations
      const declarationStyles = isDeclaration 
        ? "bg-emerald-50 border-2 border-emerald-200 shadow-sm p-4 hover:bg-emerald-100 hover:border-emerald-300" 
        : "bg-transparent border border-transparent active:border-emerald-300 hover:bg-gray-50 py-2 px-1";
      
      const checkboxSize = isDeclaration ? "w-6 h-6" : "w-5 h-5";
      const textStyles = isDeclaration ? "text-gray-800 font-semibold text-base" : "text-gray-700 font-medium text-sm";
      
      return (
        <div className="mb-4">
          <button 
            type="button"
            className={`flex items-start text-left w-full cursor-pointer rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent ${declarationStyles}`}
            onClick={(e) => {
              e.preventDefault();
              e.stopPropagation();
              
              // Special handling for declaration/agree checkboxes - always set to true
              if (isDeclaration) {
                console.log(`Declaration checkbox ${fieldId} setting to TRUE`);
                handleChange(true);
              } else {
                // Normal toggle behavior for other checkboxes
                const newValue = !Boolean(value);
                console.log(`Checkbox ${fieldId} toggling to:`, newValue);
                handleChange(newValue);
              }
            }}
          >
            <div className="flex items-center h-5 shrink-0">
              <div className={`${checkboxSize} rounded border ${Boolean(value) ? 'bg-emerald-500 border-emerald-500' : 'border-gray-300'} flex items-center justify-center`}>
                {Boolean(value) && (
                  <svg className={`${isDeclaration ? 'w-5 h-5' : 'w-4 h-4'} text-white`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                )}
              </div>
            </div>
            <div className="ml-3">
              <span className={textStyles}>{field.label}</span>
              {field.content && <p className="text-gray-500 mt-1">{field.content}</p>}
            </div>
          </button>
          
          {/* For compatibility with legacy code - hidden actual checkbox */}
          <input 
            type="checkbox" 
            id={fieldId}
            checked={Boolean(value)} 
            onChange={() => {}} 
            className="hidden" 
          />
          
          {attemptedValidation && field.required && !fieldValidation[fieldId] && (
            <div className="text-red-500 text-sm mt-1 flex items-center">
              <AlertCircle size={14} className="mr-1" />
              {isDeclaration ? 'You must agree to the declaration before proceeding' : 'This checkbox is required'}
            </div>
          )}
        </div>
      );

    case 'html':
      return (
        <div className="mb-4">
          <div dangerouslySetInnerHTML={{ __html: field.html || '' }} />
        </div>
      );

    case 'fieldset':
      return (
        <fieldset className="mb-6 border p-4 rounded-md">
          {field.legend && <legend className="text-sm font-medium px-2">{field.legend}</legend>}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {field.children?.map((childField, index) => (
              <FieldRenderer
                key={index}
                field={childField}
                value={value?.[childField.id || `${childField.label?.toLowerCase().replace(/\s+/g, '-')}`]}
                onChange={onChange}
                onValidationChange={onValidationChange}
                sectionId={sectionId}
                attemptedValidation={attemptedValidation}
                fieldValidation={fieldValidation}
              />
            ))}
          </div>
        </fieldset>
      );

    default:
      return null;
  }
};

export default FieldRenderer;