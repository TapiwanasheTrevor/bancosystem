import React from 'react';
import { CreditCard, Briefcase, Box, Truck } from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { IntentSelectionProps } from '../types';

const IntentSelection: React.FC<IntentSelectionProps> = ({
  onNext,
  onBack,
  selectedIntent = ''
}) => {
  const options = [
    {
      id: 'hirePurchase',
      title: 'Apply for Hire Purchase Credit',
      subtitle: 'Personal and Household Products',
      icon: CreditCard
    },
    {
      id: 'starterPack',
      title: 'Apply for Micro Biz',
      subtitle: 'Ngwavha, Hustle, Spana Starter Pack',
      icon: Briefcase
    },
    {
      id: 'checkStatus',
      title: 'Get an update on your application status',
      subtitle: 'Check your existing application',
      icon: Box
    },
    {
      id: 'trackDelivery',
      title: 'Track the delivery of product/equipment',
      subtitle: 'Track your order',
      icon: Truck
    }
  ];

  const handleIntentSelect = (intent: string) => {
    onNext(intent);
  };

  return (
    <StepContainer
      title="What would you like to do?"
      subtitle="Select an option to proceed with your application"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8 space-y-4">
        {options.map((option) => (
          <Button
            key={option.id}
            onClick={() => handleIntentSelect(option.id)}
            icon={option.icon}
            variant={selectedIntent === option.id ? 'primary' : 'default'}
            fullWidth
          >
            <div>
              <div className="font-medium">{option.title}</div>
              <div className="text-sm text-gray-500">{option.subtitle}</div>
            </div>
          </Button>
        ))}
      </div>
    </StepContainer>
  );
};

export default IntentSelection;