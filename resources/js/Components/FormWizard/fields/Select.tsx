import React, { useState, useRef, useEffect } from 'react';
import { Search, ChevronDown } from 'lucide-react';

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
  
  // Refs
  const dropdownRef = useRef<HTMLDivElement>(null);
  
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
    setDropdownOpen(false);
    setQuery('');
  };
  
  return (
    <div className="w-full">
      {label && (
        <label htmlFor={fieldId} className="block text-sm font-medium text-gray-700 mb-1">
          {label} {required && <span className="text-red-500">*</span>}
        </label>
      )}
      
      <div className="relative" ref={dropdownRef}>
        <div 
          className={`flex items-center border rounded-lg shadow-sm focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500
            ${error ? 'border-red-300' : 'border-gray-300'}`
          }
        >
          <div className="pl-3 text-gray-400">
            <Search size={18} />
          </div>
          <input
            type="text"
            id={fieldId}
            value={value || query}
            onChange={(e) => {
              setQuery(e.target.value);
              if (value) onChange('');
              setDropdownOpen(true);
            }}
            onClick={() => setDropdownOpen(true)}
            placeholder={placeholder}
            className="w-full p-2.5 outline-none rounded-lg"
            required={required}
          />
          <div className="pr-3 text-gray-400">
            <ChevronDown 
              size={18} 
              className={`transition-transform duration-200 ${dropdownOpen ? 'rotate-180' : ''}`}
            />
          </div>
        </div>
        
        {dropdownOpen && (
          <div className="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
            {filteredOptions.length > 0 ? (
              filteredOptions.map((option, index) => (
                <div
                  key={index}
                  className={`px-4 py-2 cursor-pointer hover:bg-emerald-50 transition-colors
                    ${option === value ? 'bg-emerald-50 font-medium' : ''}`}
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
        <p className="mt-1 text-sm text-red-600">{error}</p>
      )}
    </div>
  );
};

export default Select;