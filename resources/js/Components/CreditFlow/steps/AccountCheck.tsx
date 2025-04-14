import React, { useEffect } from 'react';
import { CheckCircle2, XCircle } from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { AccountCheckProps } from '../types';

const AccountCheck: React.FC<AccountCheckProps> = ({
  onNext,
  onBack,
  selectedOption = '',
  employerType
}) => {
  useEffect(() => {
    if (employerType === 'Government SSB') {
      // Skip this step, set default value, and go to summary
      onNext('SSB');
    }
  }, [employerType, onNext]);

  if (employerType === 'Government SSB') {
    // Optionally render nothing or a loading indicator while skipping
    return null;
  }

  const handleOptionSelect = (hasAccount: string) => {
    onNext(hasAccount);
  };

  const accountTypeText = employerType === 'SME (Small & Medium Enterprises)'
    ? 'SME Transaction Account'
    : 'ZB Account';

  return (
    <StepContainer
      title={`Do you have a ${accountTypeText}?`}
      subtitle="This information helps us process your application faster"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Button
            onClick={() => handleOptionSelect('yes')}
            icon={CheckCircle2}
            variant={selectedOption === 'yes' ? 'primary' : 'default'}
            fullWidth
          >
            Yes, I have an account
          </Button>
          <Button
            onClick={() => handleOptionSelect('no')}
            icon={XCircle}
            variant={selectedOption === 'no' ? 'primary' : 'default'}
            fullWidth
          >
            No, I don't have an account
          </Button>
        </div>
      </div>
    </StepContainer>
  );
};

export default AccountCheck;