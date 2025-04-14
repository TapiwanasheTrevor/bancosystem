import React from 'react';
import { Languages } from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { LanguageSelectionProps } from '../types';

const LanguageSelection: React.FC<LanguageSelectionProps> = ({ 
  onNext, 
  selectedLanguage = '' 
}) => {
  const handleLanguageSelect = (language: string) => {
    onNext(language);
  };

  return (
    <StepContainer
      title="Hi there! I am Adala, a smart assistant chatbot"
      subtitle="Consider me your digital uncle. My mission is to ensure you get the best online service experience possible for your next credit consideration because we are family."
    >
      <div className="p-6 md:p-8 space-y-8">
        <div className="flex justify-center">
          <img
            src="/adala.jpg"
            alt="Adala Bot"
            className="w-24 h-24 rounded-full object-cover ring-4 ring-emerald-500/20"
          />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {['English', 'Shona', 'Ndebele'].map((language) => (
            <Button
              key={language}
              onClick={() => handleLanguageSelect(language)}
              icon={Languages}
              variant={selectedLanguage === language ? 'primary' : 'default'}
              fullWidth
            >
              {language}
            </Button>
          ))}
        </div>
      </div>
    </StepContainer>
  );
};

export default LanguageSelection;