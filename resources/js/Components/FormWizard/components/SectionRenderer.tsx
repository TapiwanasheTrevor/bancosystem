import React from 'react';
import { Section, Field } from '../types';
import FieldRenderer from '../fields/FieldRenderer';
import DirectorSignatureSection from './DirectorSignatureSection';

interface SectionRendererProps {
  section: Section;
  formValues: Record<string, any>;
  onChange: (fieldId: string, value: any, field?: Field) => void;
  onValidationChange: (fieldId: string, isValid: boolean) => void;
  attemptedValidation: boolean;
  fieldValidation: Record<string, boolean>;
  sectionVariant?: string;
  directorCount?: number;
  currentStep?: number;
  totalSteps?: number;
}

const SectionRenderer: React.FC<SectionRendererProps> = ({
  section,
  formValues,
  onChange,
  onValidationChange,
  attemptedValidation,
  fieldValidation,
  sectionVariant,
  directorCount = 1,
  currentStep = 0,
  totalSteps = 1,
}) => {
  if (!section) return null;

  const sectionId = section.id || section.title.toLowerCase().replace(/\s+/g, '-');

  // Handle dynamic sections with variants
  if (section.dynamicSection && section.variants && sectionVariant) {
    const variant = section.variants[sectionVariant];
    if (variant && variant.fields) {
      return (
        <div className="py-4">
          <div className="mb-4">
            <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
            {section.description && (
              <p className="text-gray-600 mt-1">{section.description}</p>
            )}
          </div>
          
          {/* Progress indicator to show there are more fields below */}
          <div className="sticky top-0 z-10 bg-white shadow-sm mb-4 rounded-md overflow-hidden">
            <div className="h-1 bg-gray-100 w-full">
              <div 
                className="h-full bg-emerald-500 transition-all duration-300" 
                style={{ width: `${Math.min(100, (currentStep + 1) / totalSteps * 100)}%` }}
              ></div>
            </div>
          </div>
          
          {/* Two-column grid layout for dynamic variant */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            {variant.fields.map((field, index) => {
              // Determine if this field should take a full row
              const isFullRow = shouldTakeFullRow(field);
              
              return (
                <div 
                  key={`${sectionId}-${index}`} 
                  className={isFullRow ? "col-span-1 md:col-span-2" : "col-span-1"}
                >
                  <FieldRenderer
                    field={field}
                    value={formValues[field.id || field.label?.toLowerCase().replace(/\s+/g, '-')]}
                    onChange={onChange}
                    onValidationChange={onValidationChange}
                    sectionId={sectionId}
                    attemptedValidation={attemptedValidation}
                    fieldValidation={fieldValidation}
                    formValues={formValues}
                  />
                </div>
              );
            })}
          </div>
        </div>
      );
    }
  }

  // Handle director templates for company forms
  if (section.generateFromCount && section.templates?.director && directorCount > 0) {
    return (
      <div className="py-4">
        <div className="mb-4">
          <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
          {section.description && (
            <p className="text-gray-600 mt-1">{section.description}</p>
          )}
        </div>
        
        {/* Progress indicator to show there are more fields below */}
        <div className="sticky top-0 z-10 bg-white shadow-sm mb-4 rounded-md overflow-hidden">
          <div className="h-1 bg-gray-100 w-full">
            <div 
              className="h-full bg-emerald-500 transition-all duration-300" 
              style={{ width: `${Math.min(100, (currentStep + 1) / totalSteps * 100)}%` }}
            ></div>
          </div>
        </div>

        <div className="space-y-6">
          {Array.from({ length: directorCount }).map((_, index) => (
            <DirectorSignatureSection
              key={`director-${index}`}
              directorIndex={index}
              template={section.templates.director}
              formValues={formValues}
              onChange={onChange}
              onValidationChange={onValidationChange}
              attemptedValidation={attemptedValidation}
              fieldValidation={fieldValidation}
            />
          ))}
        </div>
      </div>
    );
  }

  // Helper to determine if a field should take a full row
  const shouldTakeFullRow = (field: Field): boolean => {
    // These field types should span the full width
    return field.type === 'textarea' || 
           field.type === 'html' || 
           field.type === 'signature' || 
           field.type === 'file' || 
           field.type === 'subtitle' || 
           field.type === 'fieldset' ||
           field.type === 'checkbox' ||
           (field.label && field.label.length > 30) || // Long labels
           (field.content && field.content.length > 100); // Fields with long content
  };
  
  // Regular section rendering with two columns
  return (
    <div className="py-4">
      <div className="mb-4">
        <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
        {section.description && (
          <p className="text-gray-600 mt-1">{section.description}</p>
        )}
      </div>
      
      {/* Progress indicator to show there are more fields below */}
      <div className="sticky top-0 z-10 bg-white shadow-sm mb-4 rounded-md overflow-hidden">
        <div className="h-1 bg-gray-100 w-full">
          <div 
            className="h-full bg-emerald-500 transition-all duration-300" 
            style={{ width: `${Math.min(100, (currentStep + 1) / totalSteps * 100)}%` }}
          ></div>
        </div>
      </div>
      
      {/* Two-column grid layout */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
        {section.fields?.map((field, index) => {
          // Determine if this field should take a full row
          const isFullRow = shouldTakeFullRow(field);
          
          return (
            <div 
              key={`${sectionId}-${index}`} 
              className={isFullRow ? "col-span-1 md:col-span-2" : "col-span-1"}
            >
              <FieldRenderer
                field={field}
                value={formValues[field.id || field.label?.toLowerCase().replace(/\s+/g, '-')]}
                onChange={onChange}
                onValidationChange={onValidationChange}
                sectionId={sectionId}
                attemptedValidation={attemptedValidation}
                fieldValidation={fieldValidation}
                formValues={formValues}
              />
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default SectionRenderer;