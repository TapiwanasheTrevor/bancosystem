import React from 'react';
import { Field, FieldConfig } from '../types';
import BranchLocator from './BranchLocator';
import Select from './Select';
import SignaturePad from './SignaturePad';
import DateField from './DateField';
import FileUpload from './FileUpload';
import TextArea from './TextArea';
import { AlertCircle } from 'lucide-react';

interface FieldRendererProps {
  field: Field;
  value: any;
  onChange: (fieldId: string, value: any, field?: Field) => void;
  onValidationChange: (fieldId: string, isValid: boolean) => void;
  sectionId?: string;
  attemptedValidation: boolean;
  fieldValidation: Record<string, boolean>;
}

const FieldRenderer: React.FC<FieldRendererProps> = ({
  field,
  value,
  onChange,
  onValidationChange,
  sectionId = '',
  attemptedValidation,
  fieldValidation,
}) => {
  if (!field) return null;

  const fieldId = field.id || `${field.label?.toLowerCase().replace(/\s+/g, '-')}`;
  const isReadOnly = field.readOnly || false;
  const baseInputStyles = "w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300";
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
      return (
        <div className="mb-4">
          <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
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
      return (
        <div className="mb-4">
          <button 
            type="button"
            className="flex items-start text-left w-full bg-transparent border border-transparent active:border-emerald-300 cursor-pointer py-2 px-1 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent" 
            onClick={(e) => {
              e.preventDefault();
              e.stopPropagation();
              
              // Special handling for declaration/agree checkboxes - always set to true
              if (field.label?.toLowerCase().includes('confirm') || 
                  field.label?.toLowerCase().includes('agree') || 
                  field.label?.toLowerCase().includes('declaration')) {
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
              <div className={`w-5 h-5 rounded border ${Boolean(value) ? 'bg-emerald-500 border-emerald-500' : 'border-gray-300'} flex items-center justify-center`}>
                {Boolean(value) && (
                  <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                )}
              </div>
            </div>
            <div className="ml-3 text-sm">
              <span className="text-gray-700 font-medium">{field.label}</span>
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
              This checkbox is required
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