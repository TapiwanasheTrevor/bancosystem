import React from 'react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { WantAccountProps } from '../types';

const WantAccount: React.FC<WantAccountProps> = ({
  onNext,
  onBack,
  selectedOption = ''
}) => {
  const handleOptionSelect = (wantsAccount: string) => {
    onNext(wantsAccount);
  };

  return (
    <StepContainer
      title="Would you like to open a ZB Account?"
      subtitle="Having a ZB account gives you access to exclusive benefits"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Button
            onClick={() => handleOptionSelect('yes')}
            variant={selectedOption === 'yes' ? 'primary' : 'default'}
            fullWidth
          >
            Yes, I'd like to open an account
          </Button>
          <Button
            onClick={() => handleOptionSelect('no')}
            variant={selectedOption === 'no' ? 'outline' : 'default'}
            fullWidth
          >
            No, not at this time
          </Button>
        </div>
      </div>
    </StepContainer>
  );
};

export default WantAccount;