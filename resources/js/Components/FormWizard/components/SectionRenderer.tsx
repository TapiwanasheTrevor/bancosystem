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
}) => {
  if (!section) return null;

  const sectionId = section.id || section.title.toLowerCase().replace(/\s+/g, '-');

  // Handle dynamic sections with variants
  if (section.dynamicSection && section.variants && sectionVariant) {
    const variant = section.variants[sectionVariant];
    if (variant && variant.fields) {
      return (
        <div className="py-4">
          <div className="mb-6">
            <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
            {section.description && (
              <p className="text-gray-600 mt-1">{section.description}</p>
            )}
          </div>
          
          <div className="space-y-4">
            {variant.fields.map((field, index) => (
              <FieldRenderer
                key={`${sectionId}-${index}`}
                field={field}
                value={formValues[field.id || field.label?.toLowerCase().replace(/\s+/g, '-')]}
                onChange={onChange}
                onValidationChange={onValidationChange}
                sectionId={sectionId}
                attemptedValidation={attemptedValidation}
                fieldValidation={fieldValidation}
              />
            ))}
          </div>
        </div>
      );
    }
  }

  // Handle director templates for company forms
  if (section.generateFromCount && section.templates?.director && directorCount > 0) {
    return (
      <div className="py-4">
        <div className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
          {section.description && (
            <p className="text-gray-600 mt-1">{section.description}</p>
          )}
        </div>

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
    );
  }

  // Regular section rendering
  return (
    <div className="py-4">
      <div className="mb-6">
        <h2 className="text-xl font-semibold text-gray-800">{section.title}</h2>
        {section.description && (
          <p className="text-gray-600 mt-1">{section.description}</p>
        )}
      </div>
      
      <div className="space-y-4">
        {section.fields?.map((field, index) => (
          <FieldRenderer
            key={`${sectionId}-${index}`}
            field={field}
            value={formValues[field.id || field.label?.toLowerCase().replace(/\s+/g, '-')]}
            onChange={onChange}
            onValidationChange={onValidationChange}
            sectionId={sectionId}
            attemptedValidation={attemptedValidation}
            fieldValidation={fieldValidation}
          />
        ))}
      </div>
    </div>
  );
};

export default SectionRenderer;