import React from 'react';
import { StepIndicatorProps } from '../types';

/**
 * Step indicator component for form wizard
 */
const StepIndicator: React.FC<StepIndicatorProps> = ({ 
  currentStep,
  totalSteps
}) => {
  // Calculate progress percentage
  const progressPercentage = ((currentStep + 1) / totalSteps) * 100;
  
  return (
    <div className="mb-8 space-y-2">
      {/* Progress bar */}
      <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
        <div
          className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
          style={{ width: `${progressPercentage}%` }}
        />
      </div>
      
      {/* Step counter */}
      <div className="flex justify-between items-center">
        <div className="text-sm font-medium text-gray-700">
          Step {currentStep + 1} of {totalSteps}
        </div>
        <div className="text-sm text-gray-500">
          {Math.round(progressPercentage)}% Complete
        </div>
      </div>
      
      {/* Step bubbles */}
      <div className="hidden md:flex justify-between items-center mt-2">
        {Array.from({ length: totalSteps }).map((_, index) => (
          <div key={index} className="flex flex-col items-center">
            <div 
              className={`w-8 h-8 rounded-full flex items-center justify-center transition-all
                ${index <= currentStep 
                  ? 'bg-emerald-500 text-white' 
                  : 'bg-gray-200 text-gray-400'
                }
                ${index === currentStep 
                  ? 'ring-2 ring-offset-2 ring-emerald-500' 
                  : ''
                }
              `}
            >
              {index + 1}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default StepIndicator;