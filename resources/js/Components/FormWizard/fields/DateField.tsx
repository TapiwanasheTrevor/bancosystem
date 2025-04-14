import React from 'react';
import { AlertCircle } from 'lucide-react';

interface DateFieldProps {
  fieldId: string;
  label: string;
  required: boolean;
  onChange: (value: string) => void;
  value: string;
  isInvalid: boolean;
}

const DateField: React.FC<DateFieldProps> = ({
  fieldId,
  label,
  required,
  onChange,
  value,
  isInvalid
}) => {
  const baseInputStyles = "w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300 hover:border-emerald-300";
  const invalidStyles = isInvalid ? 'border-red-300 ring-1 ring-red-300' : '';

  return (
    <div className="mb-4">
      <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
        {label} {required && <span className="text-emerald-500">*</span>}
      </label>
      <input
        type="date"
        id={fieldId}
        className={`${baseInputStyles} ${invalidStyles}`}
        required={required}
        onChange={(e) => onChange(e.target.value)}
        value={value || ''}
      />
      {isInvalid && (
        <div className="text-red-500 text-sm mt-1 flex items-center">
          <AlertCircle size={14} className="mr-1" />
          Please select a date
        </div>
      )}
    </div>
  );
};

export default DateField;