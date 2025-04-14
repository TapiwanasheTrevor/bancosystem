import React from 'react';
import { ChevronLeft, ChevronRight, Save } from 'lucide-react';
import { NavigationButtonsProps } from '../types';

/**
 * Navigation buttons for form wizard
 */
const NavigationButtons: React.FC<NavigationButtonsProps> = ({
  currentStep,
  totalSteps,
  onNext,
  onBack,
  isNextDisabled,
  isSubmitting
}) => {
  const isLastStep = currentStep === totalSteps - 1;
  
  return (
    <div className="sticky bottom-0 p-6 md:p-8 bg-gray-50 border-t border-gray-100">
      <div className="flex justify-between items-center">
        {/* Back button */}
        <button
          type="button"
          onClick={onBack}
          disabled={currentStep === 0 || isSubmitting}
          className={`px-4 py-2 rounded-xl flex items-center space-x-2 transition-colors
            ${currentStep === 0 || isSubmitting
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-emerald-300'
            }`}
        >
          <ChevronLeft className="w-5 h-5" />
          <span>Back</span>
        </button>
        
        {/* Next/Submit button */}
        <button
          type="button"
          onClick={onNext}
          disabled={isNextDisabled || isSubmitting}
          className={`px-6 py-2 rounded-xl flex items-center space-x-2 transition-colors
            ${isNextDisabled || isSubmitting
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:from-emerald-600 hover:to-emerald-700'
            }`}
        >
          <span>{isSubmitting ? 'Processing...' : isLastStep ? 'Submit' : 'Next'}</span>
          {isLastStep ? (
            <Save className="w-5 h-5" />
          ) : (
            <ChevronRight className="w-5 h-5" />
          )}
        </button>
      </div>
    </div>
  );
};

export default NavigationButtons;