import React from 'react';
import { ArrowLeft } from 'lucide-react';

interface StepContainerProps {
  children: React.ReactNode;
  title?: string;
  subtitle?: string;
  showBackButton?: boolean;
  onBack?: () => void;
  progress?: number;
}

const StepContainer: React.FC<StepContainerProps> = ({
  children,
  title,
  subtitle,
  showBackButton = false,
  onBack,
  progress
}) => {
  const renderProgressBar = () => {
    if (progress === undefined) return null;
    
    return (
      <div className="space-y-2">
        <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
          <div
            className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
            style={{ width: `${progress}%` }}
          />
        </div>
        <div className="text-sm text-gray-600 text-right">
          Progress: {Math.round(progress)}%
        </div>
      </div>
    );
  };

  return (
    <div className="flex-1 overflow-auto">
      <div className="min-h-full p-4 md:p-6">
        <div className="max-w-4xl mx-auto space-y-6">
          {showBackButton && onBack && (
            <button
              onClick={onBack}
              className="flex items-center text-emerald-600 hover:text-emerald-700 transition-colors"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </button>
          )}
          
          {renderProgressBar()}
          
          <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
            {title && (
              <div className="p-6 md:p-8 text-center space-y-2">
                <h2 className="text-2xl font-semibold text-gray-800">{title}</h2>
                {subtitle && <p className="text-gray-600">{subtitle}</p>}
              </div>
            )}
            {children}
          </div>
        </div>
      </div>
    </div>
  );
};

export default StepContainer;