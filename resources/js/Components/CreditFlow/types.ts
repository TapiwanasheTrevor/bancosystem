/**
 * Type definitions for the Credit Application Flow
 */

export type CreditOption = {
  months: number;
  interest: string;
  final_price: string;
  installment_amount: string;
};

export type Product = {
  id: number;
  name: string;
  base_price: string;
  image: string;
  description: string;
  credit_options: CreditOption[];
};

export type Category = {
  id: number;
  name: string;
  parent_id: number | null;
  children_count?: number;
  subcategories?: Category[];
  products?: Product[];
};

export type CategoryResponse = {
  status: string;
  data: Category;
};

export type CategoriesResponse = {
  status: string;
  data: Category[];
};

export type ApplicationDetails = {
  name: string;
  phone: string;
  email: string;
  idNumber: string;
  ecNumber?: string;
};

export type SelectedProduct = {
  product: Product;
  selectedCreditOption: CreditOption;
  category: string;
  loanStartDate: string;
  loanEndDate: string;
};

export type FormData = {
  language: string;
  intent: string;
  employer: string;
  selectedProduct: SelectedProduct | null;
  hasAccount: string;
  wantsAccount: string;
  accountType: string;
  specificFormId?: string;
  applicationDetails: ApplicationDetails;
};

export type Step = 
  | 'language' 
  | 'intent' 
  | 'employer' 
  | 'product' 
  | 'account-check' 
  | 'want-account' 
  | 'final' 
  | 'check-status'
  | 'track-delivery'
  | 'terminate';

export type DeliveryStatus = 'pending' | 'processing' | 'in_transit' | 'out_for_delivery' | 'delivered' | 'delayed' | 'cancelled';

export type DeliveryUpdate = {
  status: DeliveryStatus;
  status_label: string;
  datetime: string;
  location?: string;
  notes?: string;
};

export type DeliveryDetails = {
  tracking_number: string;
  status: DeliveryStatus;
  status_label: string;
  current_location?: string;
  estimated_delivery_date?: string;
  product?: {
    name: string;
    id: number;
  };
  status_updates?: DeliveryUpdate[];
};

export type ApplicationStatus = {
  uuid: string;
  status: 'pending' | 'approved' | 'rejected';
  created_at: string;
  product?: {
    name: string;
    category: string;
    installment_amount: string;
    months: number;
  };
};

// Component Props Types
export interface CreditFlowProps {
  onComplete: (formData: FormData) => void;
}

export interface StepProps {
  onNext: (data?: any) => void;
  onBack?: () => void;
  data?: any;
}

export interface ProductSelectionProps extends StepProps {
  categories: Category[];
  currentCategory: Category | null;
  categoryHistory: Category[];
  selectedProductId: number | null;
  onCategoryClick: (category: Category) => void;
  onBackClick: () => void;
  onProductSelect: (product: Product, option: CreditOption) => void;
  loading: boolean;
  error: string | null;
}

export interface LanguageSelectionProps extends StepProps {
  selectedLanguage?: string;
}

export interface IntentSelectionProps extends StepProps {
  selectedIntent?: string;
}

export interface EmployerSelectionProps extends StepProps {
  selectedEmployer?: string;
}

export interface AccountCheckProps extends StepProps {
  selectedOption?: string;
  employerType: string;
}

export interface WantAccountProps extends StepProps {
  selectedOption?: string;
}

export interface FinalStepProps extends StepProps {
  formData: FormData;
  isTerminated?: boolean; // Added isTerminated prop
}

export interface StatusCheckProps extends StepProps {
  referenceNumber: string;
  setReferenceNumber: (value: string) => void;
  applicationStatus: ApplicationStatus | null;
  isCheckingStatus: boolean;
  statusError: string | null;
  onCheckStatus: () => void;
}

export interface DeliveryTrackingProps extends StepProps {
  trackingNumber: string;
  setTrackingNumber: (value: string) => void;
  deliveryDetails: DeliveryDetails | null;
  isCheckingDelivery: boolean;
  deliveryError: string | null;
  onCheckDelivery: () => void;
}

export interface StepIndicatorProps {
  currentStep: Step;
  progress: number;
}

export interface StepRendererProps {
  currentStep: Step;
  formData: FormData;
  setFormData: React.Dispatch<React.SetStateAction<FormData>>;
  onNext: (step: Step, data?: any) => void;
  onBack: () => void;
  stepState: {
    categories: Category[];
    currentCategory: Category | null;
    categoryHistory: Category[];
    selectedProductId: number | null;
    referenceNumber: string;
    setReferenceNumber: (value: string) => void;
    applicationStatus: ApplicationStatus | null;
    statusError: string | null;
    isCheckingStatus: boolean;
    trackingNumber: string;
    setTrackingNumber: (value: string) => void;
    deliveryDetails: DeliveryDetails | null;
    deliveryError: string | null;
    isCheckingDelivery: boolean;
  };
  handlers: {
    onCategoryClick: (category: Category) => void;
    onBackClick: () => void;
    onProductSelect: (product: Product, option: CreditOption) => void;
    onCheckStatus: () => void;
    onCheckDelivery: () => void;
  };
  loading: boolean;
  error: string | null;
}