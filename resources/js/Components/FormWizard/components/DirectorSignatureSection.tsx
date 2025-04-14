import React from 'react';
import { Field } from '../types';
import FieldRenderer from '../fields/FieldRenderer';

interface DirectorTemplate {
  title: string;
  fields?: Field[];
}

interface DirectorSignatureSectionProps {
  directorIndex: number;
  template: DirectorTemplate;
  formValues: Record<string, any>;
  onChange: (fieldId: string, value: any, field?: Field) => void;
  onValidationChange: (fieldId: string, isValid: boolean) => void;
  attemptedValidation: boolean;
  fieldValidation: Record<string, boolean>;
}

const DirectorSignatureSection: React.FC<DirectorSignatureSectionProps> = ({
  directorIndex,
  template,
  formValues,
  onChange,
  onValidationChange,
  attemptedValidation,
  fieldValidation,
}) => {
  const directorNumber = directorIndex + 1;
  
  // Process fields for this specific director
  const processFieldsForDirector = (fields: Field[] | undefined) => {
    if (!fields) return [];
    
    return fields.map(field => {
      // Create a copy of the field with modified properties for this director
      const processedField: Field = { ...field };
      
      // Add director index to field ID to make it unique per director
      if (field.id) {
        processedField.id = `${field.id}-director-${directorIndex}`;
      } else if (field.label) {
        processedField.id = `${field.label.toLowerCase().replace(/\s+/g, '-')}-director-${directorIndex}`;
      }
      
      // Update label to include director number
      if (field.label) {
        processedField.label = field.label.replace('[DIRECTOR_NUMBER]', `${directorNumber}`);
      }
      
      // Handle nested children for fieldsets
      if (field.type === 'fieldset' && field.children) {
        processedField.children = processFieldsForDirector(field.children);
      }
      
      return processedField;
    });
  };
  
  const directorFields = processFieldsForDirector(template.fields);
  const directorTitle = template.title.replace('[DIRECTOR_NUMBER]', `${directorNumber}`);
  
  return (
    <div className="mb-8 p-5 border border-gray-200 rounded-lg bg-gray-50">
      <h3 className="text-lg font-medium text-gray-800 mb-4">{directorTitle}</h3>
      
      <div className="space-y-4">
        {directorFields.map((field, index) => (
          <FieldRenderer
            key={`director-${directorIndex}-field-${index}`}
            field={field}
            value={formValues[field.id || '']}
            onChange={onChange}
            onValidationChange={onValidationChange}
            sectionId={`director-${directorIndex}`}
            attemptedValidation={attemptedValidation}
            fieldValidation={fieldValidation}
          />
        ))}
      </div>
    </div>
  );
};

export default DirectorSignatureSection;