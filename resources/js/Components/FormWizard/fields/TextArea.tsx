import React from 'react';
import { AlertCircle } from 'lucide-react';

interface TextAreaProps {
  fieldId: string;
  label: string;
  required: boolean;
  placeholder: string;
  onChange: (value: string) => void;
  value: string;
  readOnly: boolean;
  isInvalid: boolean;
}

const TextArea: React.FC<TextAreaProps> = ({
  fieldId,
  label,
  required,
  placeholder,
  onChange,
  value,
  readOnly,
  isInvalid
}) => {
  const baseInputStyles = "w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300";
  const invalidStyles = isInvalid ? 'border-red-300 ring-1 ring-red-300' : '';
  const readOnlyStyles = readOnly ? 'bg-gray-100 cursor-not-allowed' : 'hover:border-emerald-300';

  return (
    <div className="mb-4">
      <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
        {label} {required && <span className="text-emerald-500">*</span>}
      </label>
      <textarea
        id={fieldId}
        className={`${baseInputStyles} ${readOnlyStyles} ${invalidStyles} min-h-[100px]`}
        required={required}
        onChange={(e) => onChange(e.target.value)}
        value={value || ''}
        placeholder={placeholder}
        readOnly={readOnly}
      />
      {isInvalid && (
        <div className="text-red-500 text-sm mt-1 flex items-center">
          <AlertCircle size={14} className="mr-1" />
          This field is required
        </div>
      )}
    </div>
  );
};

export default TextArea;