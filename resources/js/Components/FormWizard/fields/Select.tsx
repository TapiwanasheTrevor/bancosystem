import React, { useState, useRef, useEffect } from 'react';
import { Search, ChevronDown, AlertCircle } from 'lucide-react';

interface SelectProps {
  fieldId: string;
  options: string[];
  onChange: (value: string) => void;
  value: string;
  required?: boolean;
  placeholder?: string;
  label?: string;
  error?: string;
}

/**
 * Enhanced searchable select component
 */
const Select: React.FC<SelectProps> = ({
  fieldId,
  options,
  onChange,
  value,
  required = false,
  placeholder = 'Select an option',
  label,
  error
}) => {
  // State
  const [query, setQuery] = useState('');
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const [displayValue, setDisplayValue] = useState(value || '');
  
  // Refs
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Special handling for "Period at current address" to ensure it renders correctly
  const isPeriodField = fieldId.toLowerCase().includes('period') || 
                       (label && label.toLowerCase().includes('period'));
  
  // Update displayed value when value prop changes (important for field re-rendering)
  useEffect(() => {
    setDisplayValue(value || '');
  }, [value]);
  
  // Handle outside clicks to close dropdown
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setDropdownOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);
  
  // Filter options based on search query
  const filteredOptions = options.filter(option =>
    option.toLowerCase().includes(query.toLowerCase())
  );
  
  // Handle option selection
  const handleSelect = (option: string) => {
    onChange(option);
    setDisplayValue(option);
    setDropdownOpen(false);
    setQuery('');
  };
  
  // Force initial display of dropdown for period fields (to fix the rendering bug)
  useEffect(() => {
    if (isPeriodField && options.length > 0 && !value) {
      // Short delay to ensure field is rendered properly
      const timer = setTimeout(() => {
        setDropdownOpen(true);
      }, 100);
      return () => clearTimeout(timer);
    }
  }, [isPeriodField, options.length, value]);
  
  return (
    <div className="w-full mb-3">
      {label && (
        <label htmlFor={fieldId} className="block text-xs font-medium text-gray-700 mb-1">
          {label} {required && <span className="text-emerald-500">*</span>}
        </label>
      )}
      
      <div className="relative" ref={dropdownRef}>
        <div 
          className={`flex items-center border rounded-md shadow-sm focus-within:ring-1 focus-within:ring-emerald-300 focus-within:border-emerald-400
            ${error ? 'border-red-300' : 'border-gray-300'}
            cursor-pointer hover:border-emerald-300 transition-all duration-300`
          }
          onClick={() => setDropdownOpen(!dropdownOpen)}
        >
          <div className="pl-2 text-gray-400">
            <Search size={16} />
          </div>
          <input
            type="text"
            id={fieldId}
            value={displayValue || query}
            onChange={(e) => {
              setQuery(e.target.value);
              setDisplayValue('');
              if (value) onChange('');
              setDropdownOpen(true);
            }}
            onClick={(e) => {
              e.stopPropagation();
              setDropdownOpen(true);
            }}
            placeholder={placeholder}
            className="w-full p-2 outline-none rounded-md bg-transparent text-sm"
            required={required}
            readOnly={isPeriodField} // Make period fields use dropdown only
          />
          <div className="pr-3 text-gray-400">
            <ChevronDown 
              size={18} 
              className={`transition-transform duration-200 ${dropdownOpen ? 'rotate-180' : ''}`}
            />
          </div>
        </div>
        
        {dropdownOpen && (
          <div className="absolute z-50 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
            {filteredOptions.length > 0 ? (
              filteredOptions.map((option, index) => (
                <div
                  key={index}
                  className={`px-4 py-2 cursor-pointer hover:bg-emerald-50 transition-colors
                    ${option === displayValue ? 'bg-emerald-50 font-medium' : ''}`}
                  onClick={() => handleSelect(option)}
                >
                  {option}
                </div>
              ))
            ) : (
              <div className="px-4 py-2 text-gray-500">No options found</div>
            )}
          </div>
        )}
      </div>
      
      {error && (
        <div className="text-red-500 text-sm mt-1 flex items-center">
          <AlertCircle size={14} className="mr-1" />
          {error}
        </div>
      )}
    </div>
  );
};

export default Select;