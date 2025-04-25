import React, { useEffect, useState, useRef } from 'react';
import { AlertCircle, Calendar, ChevronLeft, ChevronRight } from 'lucide-react';

interface DateFieldProps {
  fieldId: string;
  label: string;
  required: boolean;
  onChange: (value: string) => void;
  value: string;
  isInvalid: boolean;
}

// Helper function to calculate 20 years ago 
const getTwentyYearsAgo = (): Date => {
  const date = new Date();
  date.setFullYear(date.getFullYear() - 20);
  return date;
};

// Format date for display
const formatDate = (date: Date): string => {
  return date.toISOString().split('T')[0];
};

// Format for display to user
const formatDateForDisplay = (dateString: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const DateField: React.FC<DateFieldProps> = ({
  fieldId,
  label,
  required,
  onChange,
  value,
  isInvalid
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [displayValue, setDisplayValue] = useState('');
  const popupRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);
  
  // Set default date for DOB fields
  useEffect(() => {
    // If it's a DOB field and no value is set yet
    if ((fieldId.toLowerCase().includes('birth') || 
        fieldId.toLowerCase().includes('dob') || 
        label.toLowerCase().includes('birth') || 
        label.toLowerCase().includes('dob')) 
        && !value) {
      // Set default to 20 years ago
      const defaultDate = formatDate(getTwentyYearsAgo());
      onChange(defaultDate);
    }
    
    // Update display value when actual value changes
    if (value) {
      setDisplayValue(formatDateForDisplay(value));
    }
  }, [value, fieldId, label, onChange]);
  
  // Handle clicking outside to close popup
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (popupRef.current && 
          buttonRef.current && 
          !popupRef.current.contains(event.target as Node) && 
          !buttonRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);
  
  // Generate an array of years
  const generateYears = () => {
    const currentYear = new Date().getFullYear();
    // For DOB fields, limit years from 2005 (as requested) to 100 years ago
    if (fieldId.toLowerCase().includes('birth') || 
        fieldId.toLowerCase().includes('dob') || 
        label.toLowerCase().includes('birth') || 
        label.toLowerCase().includes('dob')) {
      return Array.from({ length: (currentYear - 1923) }, (_, i) => currentYear - i)
        .filter(year => year <= 2005);
    }
    // For other date fields, provide a range of years from 20 years ago to 5 years in future
    return Array.from({ length: 25 }, (_, i) => currentYear - 20 + i);
  };
  
  // Generate an array of months
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  
  // Generate an array of days based on selected month and year
  const generateDays = (year: number, month: number) => {
    return Array.from(
      { length: new Date(year, month + 1, 0).getDate() }, 
      (_, i) => i + 1
    );
  };
  
  // Parse the current value into year, month, day components
  const parseValue = () => {
    if (!value) {
      const today = new Date();
      return {
        year: today.getFullYear(),
        month: today.getMonth(),
        day: today.getDate()
      };
    }
    
    const date = new Date(value);
    return {
      year: date.getFullYear(),
      month: date.getMonth(),
      day: date.getDate()
    };
  };
  
  const { year, month, day } = parseValue();
  const years = generateYears();
  const days = generateDays(year, month);
  
  const handleDateSelect = (
    selectedYear: number = year, 
    selectedMonth: number = month, 
    selectedDay: number = day
  ) => {
    // Adjust day if it exceeds the days in the new month
    const maxDays = new Date(selectedYear, selectedMonth + 1, 0).getDate();
    const adjustedDay = Math.min(selectedDay, maxDays);
    
    const newDate = new Date(selectedYear, selectedMonth, adjustedDay);
    onChange(formatDate(newDate));
    setDisplayValue(formatDateForDisplay(formatDate(newDate)));
    setIsOpen(false);
  };
  
  const baseInputStyles = "w-full p-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300 hover:border-emerald-300 bg-white text-sm";
  const invalidStyles = isInvalid ? 'border-red-300 ring-1 ring-red-300' : '';

  return (
    <div className="mb-3 relative">
      <label className="block text-xs font-medium mb-1 text-gray-700" htmlFor={fieldId}>
        {label} {required && <span className="text-emerald-500">*</span>}
      </label>
      
      {/* Hidden actual date input for form submission */}
      <input
        type="date"
        id={fieldId}
        className="hidden"
        required={required}
        value={value || ''}
        readOnly
      />
      
      {/* Custom date display button */}
      <button
        ref={buttonRef}
        type="button"
        className={`${baseInputStyles} ${invalidStyles} flex justify-between items-center`}
        onClick={() => setIsOpen(!isOpen)}
      >
        <span className={displayValue ? 'text-gray-800' : 'text-gray-400'}>
          {displayValue || 'Select date...'}
        </span>
        <Calendar className="h-5 w-5 text-gray-500" />
      </button>
      
      {/* Date picker popup */}
      {isOpen && (
        <div 
          ref={popupRef}
          className="absolute z-50 mt-1 bg-white border border-gray-200 rounded-md shadow-lg p-4 w-full max-w-md"
        >
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-medium text-gray-800">Select Date</h3>
            <button 
              type="button" 
              className="text-gray-400 hover:text-gray-600"
              onClick={() => setIsOpen(false)}
            >
              &times;
            </button>
          </div>
          
          {/* Year Selection - Modern Dial */}
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2 text-gray-700">
              Year
            </label>
            <div className="relative flex items-center justify-center">
              <button 
                type="button"
                className="absolute left-0 p-2 rounded-full hover:bg-gray-100"
                onClick={() => {
                  const idx = years.indexOf(year);
                  if (idx > 0) {
                    handleDateSelect(years[idx - 1], month, day);
                  }
                }}
              >
                <ChevronLeft className="h-5 w-5" />
              </button>
              
              <select
                className="appearance-none bg-transparent text-center font-medium text-lg py-2 focus:outline-none"
                value={year}
                onChange={(e) => handleDateSelect(parseInt(e.target.value), month, day)}
              >
                {years.map(y => (
                  <option key={y} value={y}>{y}</option>
                ))}
              </select>
              
              <button 
                type="button"
                className="absolute right-0 p-2 rounded-full hover:bg-gray-100"
                onClick={() => {
                  const idx = years.indexOf(year);
                  if (idx < years.length - 1) {
                    handleDateSelect(years[idx + 1], month, day);
                  }
                }}
              >
                <ChevronRight className="h-5 w-5" />
              </button>
            </div>
          </div>
          
          {/* Month Selection - Horizontal Dial */}
          <div className="mb-4">
            <label className="block text-sm font-medium mb-2 text-gray-700">
              Month
            </label>
            <div className="relative flex items-center justify-center">
              <button 
                type="button"
                className="absolute left-0 p-2 rounded-full hover:bg-gray-100"
                onClick={() => {
                  if (month > 0) {
                    handleDateSelect(year, month - 1, day);
                  } else {
                    handleDateSelect(year - 1, 11, day);
                  }
                }}
              >
                <ChevronLeft className="h-5 w-5" />
              </button>
              
              <select
                className="appearance-none bg-transparent text-center font-medium text-lg py-2 focus:outline-none"
                value={month}
                onChange={(e) => handleDateSelect(year, parseInt(e.target.value), day)}
              >
                {months.map((m, idx) => (
                  <option key={m} value={idx}>{m}</option>
                ))}
              </select>
              
              <button 
                type="button"
                className="absolute right-0 p-2 rounded-full hover:bg-gray-100"
                onClick={() => {
                  if (month < 11) {
                    handleDateSelect(year, month + 1, day);
                  } else {
                    handleDateSelect(year + 1, 0, day);
                  }
                }}
              >
                <ChevronRight className="h-5 w-5" />
              </button>
            </div>
          </div>
          
          {/* Day Selection - Grid */}
          <div>
            <label className="block text-sm font-medium mb-2 text-gray-700">
              Day
            </label>
            <div className="grid grid-cols-7 gap-1">
              {['S', 'M', 'T', 'W', 'T', 'F', 'S'].map((d, idx) => (
                <div key={idx} className="text-center text-xs font-medium text-gray-500 py-1">
                  {d}
                </div>
              ))}
              
              {/* Empty cells for proper day alignment */}
              {Array.from({ length: new Date(year, month, 1).getDay() }, (_, i) => (
                <div key={`empty-${i}`} />
              ))}
              
              {/* Day buttons */}
              {days.map(d => (
                <button
                  key={d}
                  type="button"
                  className={`
                    h-8 w-8 flex items-center justify-center rounded-full
                    ${d === day ? 'bg-emerald-500 text-white' : 'hover:bg-gray-100 text-gray-700'}
                  `}
                  onClick={() => handleDateSelect(year, month, d)}
                >
                  {d}
                </button>
              ))}
            </div>
          </div>
          
          {/* Today button */}
          <div className="mt-4 flex justify-end">
            <button
              type="button"
              className="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-md hover:bg-emerald-200"
              onClick={() => {
                const today = new Date();
                handleDateSelect(today.getFullYear(), today.getMonth(), today.getDate());
              }}
            >
              Today
            </button>
          </div>
        </div>
      )}
      
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