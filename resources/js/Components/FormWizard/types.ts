/**
 * Type definitions for the Dynamic Form Wizard
 */

export interface FormWizardProps {
  formId: string;
  initialData?: any;
  onComplete: (insertId: string) => void;
  onCancel?: () => void;
}

export interface Field {
  type: string;
  label: string;
  content?: string;
  required?: boolean;
  options?: string[];
  legend?: string;
  children?: Field[];
  html?: string;
  placeholder?: string;
  default?: string | number | boolean;
  readOnly?: boolean;
  bindTo?: string;
  accept?: string;
  component?: string;
  value?: string;
  id?: string;
  onChange?: {
    action: string;
    dependency?: string;
    values?: any;
    target?: string;
  };
}

export interface Section {
  title: string;
  id?: string;
  description?: string;
  fields?: Field[];
  dynamicSection?: boolean;
  variants?: Record<string, { fields?: Field[] }>;
  generateFromCount?: boolean;
  templates?: {
    director?: {
      title: string;
      fields?: Field[];
    }
  };
}

export interface FormSchema {
  title: string;
  description?: string;
  sections: Section[];
  fileName: string;
}

export interface FormValues {
  [key: string]: any;
}

export type FieldValidation = Record<string, boolean>;

export interface DirectorFormValues {
  name: string;
  idNumber: string;
  signature?: string;
}

export interface BranchInfo {
  name: string;
  code: string;
}

// Props for components

export interface StepIndicatorProps {
  currentStep: number;
  totalSteps: number;
}

export interface NavigationButtonsProps {
  currentStep: number;
  totalSteps: number;
  onNext: () => void;
  onBack: () => void;
  isNextDisabled: boolean;
  isSubmitting?: boolean;
}

export interface SectionRendererProps {
  section: Section;
  formValues: FormValues;
  fieldValidation: FieldValidation;
  attemptedValidation: boolean;
  directorCount: number;
  onInputChange: (fieldId: string, value: any, field?: Field) => void;
  onFileChange: (fieldId: string, file: File) => void;
  onSignatureChange: (fieldId: string, dataUrl: string) => void;
  onClearSignature: (fieldId: string) => void;
  onDirectorLinksGenerate: (totalDirectors: number) => void;
}

export interface FieldRendererProps {
  field: Field;
  sectionId?: string;
  formValues: FormValues;
  fieldValidation: FieldValidation;
  attemptedValidation: boolean;
  onInputChange: (fieldId: string, value: any, field?: Field) => void;
  onFileChange: (fieldId: string, file: File) => void;
  onSignatureChange: (fieldId: string, dataUrl: string) => void;
  onClearSignature: (fieldId: string) => void;
  onDirectorLinksGenerate?: (totalDirectors: number) => void;
  directorCount?: number;
}

export interface DirectorSectionProps {
  directorCount: number;
  formValues: FormValues;
  fieldValidation: FieldValidation;
  attemptedValidation: boolean;
  onSignatureChange: (fieldId: string, dataUrl: string) => void;
  onClearSignature: (fieldId: string) => void;
  onInputChange: (fieldId: string, value: any) => void;
}

export interface FormSubmissionResponse {
  success: boolean;
  insertId?: string;
  id?: string;
  message?: string;
}