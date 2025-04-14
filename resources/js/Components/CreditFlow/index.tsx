import React, { useState, useEffect } from 'react';
import StepIndicator from './StepIndicator';
import LanguageSelection from './steps/LanguageSelection';
import IntentSelection from './steps/IntentSelection';
import EmployerSelection from './steps/EmployerSelection';
import AccountCheck from './steps/AccountCheck';
import WantAccount from './steps/WantAccount';
import StatusCheck from './steps/StatusCheck';
import ProductSelection from './steps/ProductSelection';
import DeliveryTracking from './steps/DeliveryTracking';
import FinalStep from './steps/FinalStep';
import { calculateFormProgress, getAccountTypeByEmployer } from './utils';
import { fetchCategories, checkApplicationStatus, trackDelivery } from './api';
import { 
  Category, 
  CreditOption, 
  FormData, 
  Product, 
  Step, 
  CreditFlowProps, 
  DeliveryDetails, 
  ApplicationStatus 
} from './types';
// Import calculateLoanDates directly here since we removed the utils directory
const calculateLoanDates = (months: number): { startDate: string; endDate: string } => {
  // Get the first day of next month for the start date
  const today = new Date();
  const startDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);

  // Calculate end date based on loan period (months)
  // To get the last day of a month: create a date for the first day of the next month, then subtract one day
  const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + months + 1, 0);

  return {
    startDate: startDate.toISOString().slice(0, 10), // YYYY-MM-DD format
    endDate: endDate.toISOString().slice(0, 10)
  };
};

const CreditFlow: React.FC<CreditFlowProps> = ({ onComplete }) => {
  // Main state
  const [currentStep, setCurrentStep] = useState<Step>('language');
  const [formData, setFormData] = useState<FormData>({
    language: '',
    intent: '',
    employer: '',
    selectedProduct: null,
    hasAccount: '',
    wantsAccount: '',
    accountType: 'Individual Transaction Account',
    applicationDetails: {
      name: '',
      phone: '',
      email: '',
      idNumber: ''
    }
  });

  // UI state
  const [formProgress, setFormProgress] = useState<number>(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  // Step-specific state
  const [categories, setCategories] = useState<Category[]>([]);
  const [currentCategory, setCurrentCategory] = useState<Category | null>(null);
  const [categoryHistory, setCategoryHistory] = useState<Category[]>([]);
  const [selectedProductId, setSelectedProductId] = useState<number | null>(null);
  
  // Status check state
  const [referenceNumber, setReferenceNumber] = useState<string>('');
  const [applicationStatus, setApplicationStatus] = useState<ApplicationStatus | null>(null);
  const [statusError, setStatusError] = useState<string | null>(null);
  const [isCheckingStatus, setIsCheckingStatus] = useState(false);
  
  // Delivery tracking state
  const [trackingNumber, setTrackingNumber] = useState<string>('');
  const [deliveryDetails, setDeliveryDetails] = useState<DeliveryDetails | null>(null);
  const [deliveryError, setDeliveryError] = useState<string | null>(null);
  const [isCheckingDelivery, setIsCheckingDelivery] = useState(false);

  // Calculate form progress whenever form data changes
  useEffect(() => {
    const progress = calculateFormProgress(formData);
    setFormProgress(progress);
  }, [formData]);

  // Load categories whenever we reach the product selection step
  useEffect(() => {
    if (currentStep === 'product') {
      loadCategories();
    }
  }, [currentStep]);

  // Function to load categories
  const loadCategories = async (categoryId?: number) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await fetchCategories(formData.intent, categoryId);
      const data = response.data;
      
      if (categoryId) {
        const categoryData = data as Category;
        setCurrentCategory(categoryData);
        
        if (categoryData.products?.length) {
          setCategories([]);
        } else if (categoryData.subcategories?.length) {
          setCategories(categoryData.subcategories);
        }
      } else {
        setCategories(data as Category[]);
        setCurrentCategory(null);
      }
    } catch (err) {
      console.error('Error fetching categories:', err);
      setError('Failed to load categories. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Handler for category clicks
  const handleCategoryClick = async (category: Category) => {
    setCategoryHistory(prev => [...prev, category]);
    await loadCategories(category.id);
  };

  // Handler for going back in category navigation
  const handleCategoryBackClick = async () => {
    if (categoryHistory.length <= 1) {
      setCurrentCategory(null);
      setCategoryHistory([]);
      await loadCategories();
    } else {
      const newHistory = [...categoryHistory];
      newHistory.pop();
      const previousCategory = newHistory[newHistory.length - 1];
      setCategoryHistory(newHistory);
      
      if (previousCategory) {
        await loadCategories(previousCategory.id);
      } else {
        await loadCategories();
      }
    }
  };

  // Handler for product selection
  const handleProductSelect = (product: Product, option: CreditOption) => {
    const { startDate, endDate } = calculateLoanDates(option.months);
    
    setApplicationStatus(null);
    setStatusError(null);
    
    setFormData(prev => ({
      ...prev,
      selectedProduct: {
        product,
        selectedCreditOption: option,
        category: currentCategory?.name || '',
        loanStartDate: startDate,
        loanEndDate: endDate
      }
    }));
    
    setSelectedProductId(product.id);
    navigateToStep('account-check');
  };

  // Handler for application status check
  const handleCheckStatus = async () => {
    if (!referenceNumber.trim()) {
      setStatusError('Please enter a reference number');
      return;
    }
    
    setIsCheckingStatus(true);
    setStatusError(null);
    setApplicationStatus(null);
    
    try {
      const data = await checkApplicationStatus(referenceNumber);
      setApplicationStatus(data);
    } catch (err) {
      console.error('Error checking application status:', err);
      setStatusError(typeof err === 'string' ? err : 'Failed to check application status');
    } finally {
      setIsCheckingStatus(false);
    }
  };

  // Handler for delivery tracking
  const handleCheckDelivery = async () => {
    if (!trackingNumber.trim()) {
      setDeliveryError('Please enter a tracking number');
      return;
    }
    
    setIsCheckingDelivery(true);
    setDeliveryError(null);
    setDeliveryDetails(null);
    
    try {
      const data = await trackDelivery(trackingNumber);
      setDeliveryDetails(data);
    } catch (err) {
      console.error('Error checking delivery status:', err);
      setDeliveryError(typeof err === 'string' ? err : 'Failed to check delivery status');
    } finally {
      setIsCheckingDelivery(false);
    }
  };

  // Navigation function
  const navigateToStep = (step: Step) => {
    setCurrentStep(step);
  };

  // Handler for going back
  const handleBack = () => {
    // Map to determine the previous step based on current step
    const prevStepMap: Record<Step, Step> = {
      'language': 'language', // Can't go back from first step
      'intent': 'language',
      'employer': 'intent',
      'product': 'employer',
      'account-check': 'product',
      'want-account': 'account-check',
      'final': 'want-account',
      'check-status': 'intent',
      'track-delivery': 'intent',
      'terminate': 'want-account'
    };
    
    navigateToStep(prevStepMap[currentStep]);
  };

  // Handler for language selection
  const handleLanguageSelect = (language: string) => {
    setFormData(prev => ({ ...prev, language }));
    navigateToStep('intent');
  };

  // Handler for intent selection
  const handleIntentSelect = (intent: string) => {
    setFormData(prev => ({ ...prev, intent }));
    
    // Special handling for different intents
    if (intent === 'checkStatus') {
      navigateToStep('check-status');
    } else if (intent === 'trackDelivery') {
      navigateToStep('track-delivery');
    } else {
      navigateToStep('employer');
    }
  };

  // Handler for employer selection
  const handleEmployerSelect = (employer: string) => {
    // Set account type based on employer
    const accountType = getAccountTypeByEmployer(employer);
    
    setFormData(prev => ({ 
      ...prev, 
      employer,
      accountType
    }));
    
    navigateToStep('product');
  };

  // Handler for account check
  const handleAccountCheck = (hasAccount: string) => {
    setFormData(prev => ({ ...prev, hasAccount }));
    
    if (hasAccount === 'yes') {
      navigateToStep('final');
    } else {
      navigateToStep('want-account');
    }
  };

  // Handler for want account
  const handleWantAccount = (wantsAccount: string) => {
    setFormData(prev => ({ ...prev, wantsAccount }));
    
    if (wantsAccount === 'yes') {
      navigateToStep('final');
    } else {
      navigateToStep('terminate');
    }
  };

  // Handler for final step submission
  const handleFinalSubmit = () => {
    onComplete(formData);
  };

  // Render the appropriate step
  const renderStep = () => {
    switch (currentStep) {
      case 'language':
        return (
          <LanguageSelection 
            onNext={handleLanguageSelect}
            selectedLanguage={formData.language}
          />
        );
      
      case 'intent':
        return (
          <IntentSelection 
            onNext={handleIntentSelect}
            onBack={handleBack}
            selectedIntent={formData.intent}
          />
        );
      
      case 'employer':
        return (
          <EmployerSelection 
            onNext={handleEmployerSelect}
            onBack={handleBack}
            selectedEmployer={formData.employer}
          />
        );
      
      case 'product':
        return (
          <ProductSelection 
            onNext={() => {}}
            onBack={handleBack}
            categories={categories}
            currentCategory={currentCategory}
            categoryHistory={categoryHistory}
            selectedProductId={selectedProductId}
            onCategoryClick={handleCategoryClick}
            onBackClick={handleCategoryBackClick}
            onProductSelect={handleProductSelect}
            loading={loading}
            error={error}
          />
        );
      
      case 'account-check':
        return (
          <AccountCheck 
            onNext={handleAccountCheck}
            onBack={handleBack}
            selectedOption={formData.hasAccount}
            employerType={formData.employer}
          />
        );
      
      case 'want-account':
        return (
          <WantAccount 
            onNext={handleWantAccount}
            onBack={handleBack}
            selectedOption={formData.wantsAccount}
          />
        );
      
      case 'check-status':
        return (
          <StatusCheck 
            onNext={() => {}}
            onBack={handleBack}
            referenceNumber={referenceNumber}
            setReferenceNumber={setReferenceNumber}
            applicationStatus={applicationStatus}
            isCheckingStatus={isCheckingStatus}
            statusError={statusError}
            onCheckStatus={handleCheckStatus}
          />
        );
      
      case 'track-delivery':
        return (
          <DeliveryTracking 
            onNext={() => {}}
            onBack={handleBack}
            trackingNumber={trackingNumber}
            setTrackingNumber={setTrackingNumber}
            deliveryDetails={deliveryDetails}
            isCheckingDelivery={isCheckingDelivery}
            deliveryError={deliveryError}
            onCheckDelivery={handleCheckDelivery}
          />
        );
      
      case 'final':
      case 'terminate':
        return (
          <FinalStep 
            onNext={handleFinalSubmit}
            onBack={handleBack}
            formData={formData}
            isTerminated={currentStep === 'terminate'}
          />
        );
      
      default:
        return null;
    }
  };

  return (
    <div className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex flex-col">
      {/* Display loading indicator if loading */}
      {loading && currentStep !== 'product' && (
        <div className="fixed inset-0 flex items-center justify-center bg-black/20 z-50">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
        </div>
      )}
      
      {/* Display error message if error and not in product step */}
      {error && currentStep !== 'product' && (
        <div className="fixed inset-0 flex items-center justify-center bg-black/20 z-50 p-4">
          <div className="bg-white p-6 rounded-xl shadow-lg max-w-md w-full">
            <h3 className="text-lg font-medium text-red-600">Error</h3>
            <p className="mt-2 text-gray-600">{error}</p>
            <button 
              onClick={() => setError(null)}
              className="mt-4 w-full py-2 bg-emerald-500 text-white rounded-lg"
            >
              Dismiss
            </button>
          </div>
        </div>
      )}
      
      {/* Step indicator */}
      <div className="container mx-auto px-4 pt-6">
        <StepIndicator currentStep={currentStep} progress={formProgress} />
      </div>
      
      {/* Current step */}
      {renderStep()}
    </div>
  );
};

export default CreditFlow;