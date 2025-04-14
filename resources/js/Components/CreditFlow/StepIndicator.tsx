import React from 'react';
import { 
  Languages, 
  CreditCard, 
  Building2, 
  ShoppingCart, 
  CreditCard as AccountIcon,
  CircleDollarSign
} from 'lucide-react';
import { Step, StepIndicatorProps } from './types';

const StepIndicator: React.FC<StepIndicatorProps> = ({ currentStep, progress }) => {
  const steps: Array<{ id: Step; label: string; icon: React.ElementType }> = [
    { id: 'language', label: 'Language', icon: Languages },
    { id: 'intent', label: 'Purpose', icon: CreditCard },
    { id: 'employer', label: 'Employer', icon: Building2 },
    { id: 'product', label: 'Product', icon: ShoppingCart },
    { id: 'account-check', label: 'Account', icon: AccountIcon },
    { id: 'want-account', label: 'New Account', icon: CircleDollarSign }
  ];

  const currentStepIndex = steps.findIndex(step => step.id === currentStep);
  
  // Skip rendering for special steps that don't fit in the flow
  if (
    currentStep === 'check-status' || 
    currentStep === 'track-delivery' || 
    currentStep === 'terminate'
  ) {
    return null;
  }

  return (
    <div className="space-y-6">
      <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
        <div
          className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
          style={{ width: `${progress}%` }}
        />
      </div>

      <div className="grid grid-cols-6 gap-2">
        {steps.map((step, index) => {
          const Icon = step.icon;
          const isActive = currentStepIndex >= index;
          const isCurrent = currentStepIndex === index;
          
          return (
            <div key={step.id} className="flex flex-col items-center">
              <div 
                className={`
                  rounded-full w-10 h-10 flex items-center justify-center mb-1
                  ${isActive 
                    ? 'bg-emerald-500 text-white' 
                    : 'bg-gray-100 text-gray-400'}
                  ${isCurrent 
                    ? 'ring-2 ring-offset-2 ring-emerald-500' 
                    : ''}
                `}
              >
                <Icon className="w-5 h-5" />
              </div>
              <span 
                className={`
                  text-xs text-center
                  ${isActive ? 'text-emerald-600 font-medium' : 'text-gray-400'}
                `}
              >
                {step.label}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default StepIndicator;