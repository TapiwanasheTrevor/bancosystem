import React, {useState, useEffect} from 'react';
import {
    ArrowLeft,
    ChevronRight,
    Languages,
    Building2,
    Briefcase,
    CreditCard,
    Box,
    Truck,
    CheckCircle2,
    XCircle
} from 'lucide-react';

interface CreditApplicationFlowProps {
    onComplete: (formData: any) => void;
}

type CreditOption = {
    months: number;
    interest: string;
    final_price: string;
    installment_amount: string; // Added field for monthly installment
};

type Product = {
    id: number;
    name: string;
    base_price: string;
    image: string;
    description: string; // Added field for product description
    credit_options: CreditOption[];
};

type Category = {
    id: number;
    name: string;
    parent_id: number | null;
    children_count?: number;
    subcategories?: Category[];
    products?: Product[];
};

type CategoryResponse = {
    status: string;
    data: Category;
};

type CategoriesResponse = {
    status: string;
    data: Category[];
};

type FormData = {
    language: string;
    intent: string;
    employer: string;
    selectedProduct: {
        product: Product;
        selectedCreditOption: CreditOption;
        category: string;
        loanStartDate: string; // Added field for loan start date
        loanEndDate: string;   // Added field for loan end date
    } | null;
    hasAccount: string;
    wantsAccount: string;
    accountType: string;      // Added field for account type
    applicationDetails: {
        name: string;
        phone: string;
        email: string;
        idNumber: string;
    };
};

const CreditApplicationFlow = ({onComplete}: CreditApplicationFlowProps) => {
    const [step, setStep] = useState<number | 'final' | 'terminate'>(1);
    const [formProgress, setFormProgress] = useState<number>(0);
    const [formData, setFormData] = useState<FormData>({
        language: '',
        intent: '',
        employer: '',
        selectedProduct: null,
        hasAccount: '',
        wantsAccount: '',
        accountType: 'Individual Transaction Account', // Default account type as requested
        applicationDetails: {
            name: '',
            phone: '',
            email: '',
            idNumber: ''
        }
    });
    const [categories, setCategories] = useState<Category[]>([]);
    const [currentCategory, setCurrentCategory] = useState<Category | null>(null);
    const [categoryHistory, setCategoryHistory] = useState<Category[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [selectedProductId, setSelectedProductId] = useState<number | null>(null);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [referenceNumber, setReferenceNumber] = useState<string>('');
    const [applicationStatus, setApplicationStatus] = useState<any | null>(null);
    const [statusError, setStatusError] = useState<string | null>(null);
    const [isCheckingStatus, setIsCheckingStatus] = useState(false);

    useEffect(() => {
        let progress = 0;
        const fields = [
            formData.language,
            formData.intent,
            formData.employer,
            formData.selectedProduct,
            formData.hasAccount,
            formData.wantsAccount
        ];

        const totalFields = fields.length;
        const filledFields = fields.filter(field => {
            if (typeof field === 'string') return field !== '';
            if (field === null) return false;
            return true;
        }).length;

        progress = (filledFields / totalFields) * 100;
        setFormProgress(progress);
    }, [formData]);

    // Function to calculate loan start and end dates
    const calculateLoanDates = (creditOption: CreditOption) => {
        // Get the first day of next month for the start date
        const today = new Date();
        const startDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);

        // Calculate end date based on loan period (months)
        // To get the last day of a month: create a date for the first day of the next month, then subtract one day
        const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + creditOption.months + 1, 0);

        return {
            startDate: startDate.toISOString().slice(0, 10), // YYYY-MM-DD format
            endDate: endDate.toISOString().slice(0, 10)
        };
    };

    // Function to format date for display in the deduction order form
    const formatDateText = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'});
    };

    const fetchCategories = async (categoryId?: number) => {
        setLoading(true);
        setError(null);
        try {
            // Determine which API endpoint to use based on intent
            const baseUrl = `${import.meta.env.VITE_API_BASE_URL}/api`;
            let apiUrl;
            
            // Use different API endpoint for hire purchase products
            if (formData.intent === 'hirePurchase') {
                apiUrl = categoryId
                    ? `${baseUrl}/hirepurchase/categories/${categoryId}`
                    : `${baseUrl}/hirepurchase/categories`;
            } else {
                apiUrl = categoryId
                    ? `${baseUrl}/categories/${categoryId}`
                    : `${baseUrl}/categories`;
            }

            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error('Failed to fetch categories');

            const responseData = await response.json();

            if (categoryId) {
                const categoryData = responseData as CategoryResponse;
                setCurrentCategory(categoryData.data);
                if (categoryData.data.products?.length) {
                    setCategories([]);
                } else if (categoryData.data.subcategories?.length) {
                    setCategories(categoryData.data.subcategories);
                }
            } else {
                const categoriesData = responseData as CategoriesResponse;
                setCategories(categoriesData.data);
                setCurrentCategory(null);
            }
        } catch (err) {
            console.error('Error fetching categories:', err);
            setError('Failed to load categories. Please try again later.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (step === 4) {
            fetchCategories();
        }
    }, [step]);

    const handleCategoryClick = async (category: Category) => {
        setCategoryHistory(prev => [...prev, category]);
        await fetchCategories(category.id);
    };

    const handleBackClick = async () => {
        if (categoryHistory.length <= 1) {
            setCurrentCategory(null);
            setCategoryHistory([]);
            await fetchCategories();
        } else {
            const newHistory = [...categoryHistory];
            newHistory.pop();
            const previousCategory = newHistory[newHistory.length - 1];
            setCategoryHistory(newHistory);
            if (previousCategory) {
                await fetchCategories(previousCategory.id);
            } else {
                await fetchCategories();
            }
        }
    };

    const handleCreditOptionSelection = (product: Product, option: CreditOption) => {
        // Calculate loan dates
        const {startDate, endDate} = calculateLoanDates(option);

        // Reset any previous application status when selecting a new product
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

        // Determine the next step based on employer selection
        if (formData.employer === 'GOZ (Government of Zimbabwe) - SSB') {
            // Update form data with SSB form ID but don't bypass confirmation screen
            setFormData(prev => ({
                ...prev,
                specificFormId: 'ssb_account_opening_form' // Explicitly specify the form ID
            }));
            setStep('final'); // Go to final confirmation step
        } else if (formData.employer === 'GOZ - Pension') {
            // Update form data with pensioners form ID but don't bypass confirmation screen
            setFormData(prev => ({
                ...prev,
                specificFormId: 'pensioners_loan_account' // Explicitly specify the form ID
            }));
            setStep('final'); // Go to final confirmation step
        } else {
            setStep(5);
        }
    };

    const handleDoNotAgreeTerms = () => {
        // Show confirmation dialog when user doesn't agree to terms
        setShowConfirmDialog(true);
    };

    const handleConfirmNoTerms = (confirm: boolean) => {
        setShowConfirmDialog(false);
        if (confirm) {
            // User wants to proceed anyway
            onComplete(formData);
        }
        // Otherwise, stay on current step to allow user to change their mind
    };
    
    // Handle checking application status
    const handleCheckStatus = async () => {
        if (!referenceNumber.trim()) {
            setStatusError('Please enter a reference number');
            return;
        }

        setIsCheckingStatus(true);
        setStatusError(null);
        setApplicationStatus(null);

        try {
            const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/check-application-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reference_number: referenceNumber }),
            });

            const data = await response.json();

            if (!response.ok) {
                setStatusError(data.message || 'Failed to check application status');
                return;
            }

            setApplicationStatus(data.data);
        } catch (err) {
            console.error('Error checking application status:', err);
            setStatusError('An error occurred while checking your application status');
        } finally {
            setIsCheckingStatus(false);
        }
    };

    const renderStepIndicator = () => {
        return (
            <div className="space-y-2">
                <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div
                        className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
                        style={{width: `${formProgress}%`}}
                    />
                </div>
                <div className="text-sm text-gray-600 text-right">
                    Progress: {Math.round(formProgress)}%
                </div>
            </div>
        );
    };

    const StepContainer = ({children, title, subtitle}) => (
        <div className="flex-1 overflow-auto">
            <div className="min-h-full p-4 md:p-6">
                <div className="max-w-4xl mx-auto space-y-6">
                    {step !== 1 && (
                        <button
                            onClick={() => setStep(prev => {
                                if (typeof prev === 'number') return prev - 1;
                                return 1;
                            })}
                            className="flex items-center text-emerald-600 hover:text-emerald-700 transition-colors"
                        >
                            <ArrowLeft className="w-4 h-4 mr-2"/>
                            Back
                        </button>
                    )}
                    {renderStepIndicator()}
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

    const Button = ({children, onClick, variant = 'default', icon: Icon, disabled = false}) => (
        <button
            onClick={onClick}
            disabled={disabled}
            className={`w-full p-4 rounded-xl transition-all duration-300 flex items-center justify-between
                ${variant === 'primary'
                ? 'bg-gradient-to-r from-emerald-500 to-orange-400 text-white hover:from-emerald-600 hover:to-orange-500'
                : 'border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50'}
                ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
        >
            <span className="flex-1 text-left">{children}</span>
            {Icon && <Icon className="w-5 h-5 ml-2 flex-shrink-0"/>}
        </button>
    );

    const renderLanguageSelection = () => (
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
                    {['English', 'Shona', 'Ndebele'].map((lang) => (
                        <Button
                            key={lang}
                            onClick={() => {
                                setFormData(prev => ({...prev, language: lang}));
                                setStep(2);
                            }}
                            icon={Languages}
                        >
                            {lang}
                        </Button>
                    ))}
                </div>
            </div>
        </StepContainer>
    );

    // Render the application status check UI
    const renderStatusCheck = () => (
        <StepContainer 
            title="Check Your Application Status" 
            subtitle="Enter your reference number to check the status of your application"
        >
            <div className="p-6 md:p-8 space-y-6">
                <div className="space-y-4">
                    <div className="flex flex-col">
                        <label className="text-gray-700 mb-2">Reference Number</label>
                        <input 
                            type="text"
                            value={referenceNumber}
                            onChange={(e) => setReferenceNumber(e.target.value)}
                            placeholder="Enter your reference number"
                            className="w-full p-3 border rounded-lg focus:ring focus:ring-emerald-400 outline-none"
                        />
                    </div>
                    
                    {statusError && (
                        <div className="p-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
                            {statusError}
                        </div>
                    )}
                    
                    <Button
                        onClick={handleCheckStatus}
                        variant="primary"
                        disabled={isCheckingStatus}
                    >
                        {isCheckingStatus ? 'Checking...' : 'Check Status'}
                    </Button>

                    {applicationStatus && (
                        <div className="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <h3 className="text-lg font-semibold mb-4">Application Details</h3>
                            
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-600">Status:</span>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                        applicationStatus.status === 'approved' 
                                            ? 'bg-green-100 text-green-800' 
                                            : applicationStatus.status === 'rejected' 
                                            ? 'bg-red-100 text-red-800' 
                                            : 'bg-yellow-100 text-yellow-800'
                                    }`}>
                                        {applicationStatus.status.toUpperCase()}
                                    </span>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-600">Reference Number:</span>
                                    <span className="font-medium">{applicationStatus.uuid}</span>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-600">Application Date:</span>
                                    <span className="font-medium">{new Date(applicationStatus.created_at).toLocaleDateString()}</span>
                                </div>
                                
                                {applicationStatus.product && (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">Product:</span>
                                            <span className="font-medium">{applicationStatus.product.name}</span>
                                        </div>
                                        
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">Category:</span>
                                            <span className="font-medium">{applicationStatus.product.category}</span>
                                        </div>
                                        
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">Amount:</span>
                                            <span className="font-medium">${applicationStatus.product.final_price}</span>
                                        </div>
                                        
                                        <div className="flex items-center justify-between">
                                            <span className="text-gray-600">Term:</span>
                                            <span className="font-medium">{applicationStatus.product.months} months</span>
                                        </div>
                                    </>
                                )}
                            </div>
                            
                            {applicationStatus.status === 'approved' && (
                                <div className="mt-4 p-3 bg-green-50 text-green-700 rounded-lg border border-green-200">
                                    Your application has been approved! Our representative will contact you soon with next steps.
                                </div>
                            )}
                            
                            {applicationStatus.status === 'rejected' && (
                                <div className="mt-4 p-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
                                    Unfortunately, your application was not approved at this time. Please contact our customer service for more details.
                                </div>
                            )}
                            
                            {applicationStatus.status === 'pending' && (
                                <div className="mt-4 p-3 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                                    Your application is still being processed. Please check back later for updates.
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </StepContainer>
    );

    const renderIntentSelection = () => (
        <StepContainer
            title="What would you like to do?"
            subtitle="Select an option to proceed with your application"
        >
            <div className="p-6 md:p-8 space-y-4">
                {[
                    {
                        title: 'Apply for Hire Purchase Credit',
                        subtitle: 'Personal and Household Products',
                        action: 'hirePurchase',
                        icon: CreditCard
                    },
                    {
                        title: 'Apply for Micro Biz',
                        subtitle: 'Ngwavha, Hustle, Spana Starter Pack',
                        action: 'starterPack',
                        icon: Briefcase
                    },
                    {
                        title: 'Get an update on your application status',
                        subtitle: 'Check your existing application',
                        action: 'checkStatus',
                        icon: Box
                    },
                    {
                        title: 'Track the delivery of product/equipment',
                        subtitle: 'Track your order',
                        action: 'trackDelivery',
                        icon: Truck
                    }
                ].map((option) => (
                    <Button
                        key={option.action}
                        onClick={() => {
                            setFormData(prev => ({...prev, intent: option.action}));
                            // If checking status, go to status check screen
                            if (option.action === 'checkStatus') {
                                setStep('check-status');
                            } 
                            // If hire purchase or starter pack, continue to employer selection
                            else if (option.action === 'starterPack' || option.action === 'hirePurchase') {
                                setStep(3);
                            } 
                            // Otherwise, go to final screen
                            else {
                                setStep('final');
                            }
                        }}
                        icon={option.icon}
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

    const renderEmployerSelection = () => (
        <StepContainer
            title="Select Your Employer"
            subtitle="This helps us tailor the best credit options for you"
        >
            <div className="p-6 md:p-8">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {[
                        {name: 'GOZ (Government of Zimbabwe) - SSB', form: 'ssb'},
                        {name: 'GOZ - ZAPPA', form: 'zappa'},
                        {name: 'GOZ - Pension', form: 'pension'},
                        {name: 'Town Council', form: 'check-account'},
                        {name: 'Parastatal', form: 'check-account'},
                        {name: 'Mission and Private Schools', form: 'check-account'},
                        {name: 'SME (Small & Medium Enterprises)', form: 'sme'}
                    ].map((employer) => (
                        <Button
                            key={employer.name}
                            onClick={() => {
                                setFormData(prev => ({...prev, employer: employer.name}));
                                setStep(4);
                            }}
                            icon={Building2}
                        >
                            {employer.name}
                        </Button>
                    ))}
                </div>
            </div>
        </StepContainer>
    );

    const renderProductSelection = () => (
        <StepContainer
            title={currentCategory ? currentCategory.name : 'Select a Category'}
            subtitle="Browse our available products and credit options"
        >
            {loading ? (
                <div className="p-6 md:p-8 text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading...</p>
                </div>
            ) : error ? (
                <div className="p-6 md:p-8 text-center">
                    <p className="text-red-500">{error}</p>
                    <button
                        onClick={() => currentCategory ? fetchCategories(currentCategory.id) : fetchCategories()}
                        className="mt-4 px-6 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600"
                    >
                        Retry
                    </button>
                </div>
            ) : (
                <div className="p-6 md:p-8 space-y-6">
                    {categoryHistory.length > 0 && (
                        <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                            <button
                                onClick={handleBackClick}
                                className="text-emerald-600 hover:text-emerald-700"
                            >
                                Back
                            </button>
                            {categoryHistory.map((cat, index) => (
                                <React.Fragment key={cat.id}>
                                    <ChevronRight className="w-4 h-4"/>
                                    <span>{cat.name}</span>
                                </React.Fragment>
                            ))}
                        </div>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {categories.length > 0 ? (
                            categories.map((category) => (
                                <Button
                                    key={category.id}
                                    onClick={() => handleCategoryClick(category)}
                                    icon={ChevronRight}
                                >
                                    {category.name}
                                </Button>
                            ))
                        ) : currentCategory?.products?.length ? (
                            currentCategory.products.map((product) => (
                                <div
                                    key={product.id}
                                    className="bg-white rounded-xl overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg"
                                >
                                    <img
                                        src={product.image}
                                        alt={product.name}
                                        className="w-full h-48 object-cover"
                                    />
                                    <div className="p-4 space-y-4">
                                        <h3 className="font-semibold text-lg">{product.name}</h3>

                                        {selectedProductId === product.id ? (
                                            <div className="space-y-3">
                                                <h4 className="font-medium text-gray-700">Select Credit Options:</h4>
                                                <div className="grid grid-cols-1 gap-2">
                                                    {product.credit_options.map((option) => (
                                                        <button
                                                            key={option.months}
                                                            onClick={() => handleCreditOptionSelection(product, option)}
                                                            className="p-3 rounded-xl border transition-all hover:border-emerald-500 hover:bg-emerald-50"
                                                        >
                                                            <div className="flex justify-between items-center">
                                                                <div>
                                                                    <div
                                                                        className="font-medium">{option.months} Months
                                                                    </div>
                                                                    <div
                                                                        className="text-sm text-gray-500">
                                                                        ${option.installment_amount}/month
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    className="text-emerald-600 font-semibold">
                                                                    ${option.final_price} total
                                                                </div>
                                                            </div>
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : (
                                            <Button
                                                onClick={() => setSelectedProductId(product.id)}
                                                variant="primary"
                                            >
                                                Select Instalment/Period
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-full text-center py-12 text-gray-500">
                                No items found in this category
                            </div>
                        )}
                    </div>
                </div>
            )}
        </StepContainer>
    );

    const renderAccountCheck = () => (
        <StepContainer
            title="Do you have a ZB Account?"
            subtitle="This information helps us process your application faster"
        >
            <div className="p-6 md:p-8">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Button
                        onClick={() => {
                            // Check if this is an SME applicant
                            if (formData.employer === 'SME (Small & Medium Enterprises)') {
                                setFormData(prev => ({
                                    ...prev,
                                    hasAccount: 'yes',
                                    accountType: 'SME Transaction Account',
                                    specificFormId: 'smes_business_account_opening'
                                }));
                                setStep('final');
                            } else {
                                // Standard flow for non-SME applicants
                                setFormData(prev => ({
                                    ...prev,
                                    hasAccount: 'yes',
                                    accountType: 'Individual Transaction Account'
                                }));
                                setStep('final');
                            }
                        }}
                        icon={CheckCircle2}
                    >
                        Yes, I have an account
                    </Button>
                    <Button
                        onClick={() => {
                            // Check if this is an SME applicant
                            if (formData.employer === 'SME (Small & Medium Enterprises)') {
                                setFormData(prev => ({
                                    ...prev, 
                                    hasAccount: 'no',
                                    accountType: 'SME Transaction Account',
                                    specificFormId: 'smes_business_account_opening'
                                }));
                                setStep('final');
                            } else {
                                // Standard flow for non-SME applicants
                                setFormData(prev => ({...prev, hasAccount: 'no'}));
                                setStep(6);
                            }
                        }}
                        icon={XCircle}
                    >
                        No, I don't have an account
                    </Button>
                </div>
            </div>
        </StepContainer>
    );

    const renderWantAccount = () => (
        <StepContainer
            title="Would you like to open a ZB Account?"
            subtitle="Having a ZB account gives you access to exclusive benefits"
        >
            <div className="p-6 md:p-8">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Button
                        onClick={() => {
                            setFormData(prev => ({
                                ...prev,
                                wantsAccount: 'yes',
                                accountType: 'Individual Transaction Account' // Default account type as requested
                            }));
                            setStep('final');
                        }}
                        variant="primary"
                    >
                        Yes, I'd like to open an account
                    </Button>
                    <Button
                        onClick={() => {
                            setFormData(prev => ({...prev, wantsAccount: 'no'}));
                            setStep('terminate');
                        }}
                    >
                        No, not at this time
                    </Button>
                </div>
            </div>
        </StepContainer>
    );

    const renderFinal = () => (
        <StepContainer
            title={step === 'terminate' ? "Thank you for your interest" : "Application Summary"}
            subtitle={
                step === 'terminate'
                    ? "Unfortunately, we cannot proceed without a ZB Bank account"
                    : "Please review your selections before proceeding"
            }
        >
            {step !== 'terminate' && (
                <div className="p-6 md:p-8 space-y-6">
                    <div className="bg-gray-50 p-4 rounded-xl space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <h4 className="text-sm text-gray-500">Language</h4>
                                <p className="font-medium">{formData.language}</p>
                            </div>
                            <div>
                                <h4 className="text-sm text-gray-500">Application Type</h4>
                                <p className="font-medium">{formData.intent}</p>
                            </div>
                            {formData.employer && (
                                <div>
                                    <h4 className="text-sm text-gray-500">Employer</h4>
                                    <p className="font-medium">{formData.employer}</p>
                                    {formData.employer === 'GOZ (Government of Zimbabwe) - SSB' && (
                                        <p className="text-xs text-emerald-600">SSB form will be used</p>
                                    )}
                                    {formData.employer === 'GOZ - Pension' && (
                                        <p className="text-xs text-emerald-600">Pensioners form will be used</p>
                                    )}
                                    {formData.employer === 'SME (Small & Medium Enterprises)' && (
                                        <p className="text-xs text-emerald-600">
                                            SME Business form will be used
                                        </p>
                                    )}
                                </div>
                            )}
                            {formData.selectedProduct && (
                                <>
                                    <div className="col-span-2">
                                        <h4 className="text-sm text-gray-500">Selected Product</h4>
                                        <p className="font-medium">{formData.selectedProduct.product.name}</p>
                                        <p className="text-sm text-gray-500">
                                            Category: {formData.selectedProduct.category}
                                        </p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm text-gray-500">Credit Terms</h4>
                                        <p className="font-medium">
                                            {formData.selectedProduct.selectedCreditOption.months} Months
                                        </p>
                                        <p className="text-sm text-gray-500">
                                            Interest: {formData.selectedProduct.selectedCreditOption.interest}%
                                        </p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm text-gray-500">Final Price</h4>
                                        <p className="font-medium text-emerald-600">
                                            ${formData.selectedProduct.selectedCreditOption.final_price}
                                        </p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm text-gray-500">Monthly Installment</h4>
                                        <p className="font-medium text-emerald-600">
                                            ${formData.selectedProduct.selectedCreditOption.installment_amount}/month
                                        </p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm text-gray-500">Loan Period</h4>
                                        <p className="font-medium">
                                            {formData.selectedProduct.loanStartDate} to {formData.selectedProduct.loanEndDate}
                                        </p>
                                    </div>
                                </>
                            )}

                            {formData.hasAccount === 'yes' && (
                                <div>
                                    <h4 className="text-sm text-gray-500">Account Status</h4>
                                    <p className="font-medium">Existing ZB Customer</p>
                                </div>
                            )}

                            {formData.wantsAccount === 'yes' && (
                                <div>
                                    <h4 className="text-sm text-gray-500">Account Type</h4>
                                    <p className="font-medium">{formData.accountType}</p>
                                    <p className="text-sm text-gray-500">USD Account will be opened</p>
                                </div>
                            )}
                        </div>
                    </div>
                    <Button
                        onClick={() => onComplete(formData)}
                        variant="primary"
                    >
                        Click to Confirm Details and Proceed to Application Form
                    </Button>
                </div>
            )}
        </StepContainer>
    );

    // Confirmation dialog component
    const ConfirmationDialog = ({message, onConfirm, onCancel}) => (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-xl max-w-md w-full p-6 space-y-4">
                <h3 className="text-lg font-semibold">Confirmation</h3>
                <p>{message}</p>
                <div className="flex gap-3 justify-end">
                    <button
                        className="px-4 py-2 border rounded-lg hover:bg-gray-100"
                        onClick={onCancel}
                    >
                        Cancel
                    </button>
                    <button
                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                        onClick={onConfirm}
                    >
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    );

    if (loading) {
        return (
            <div
                className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div
                className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex items-center justify-center p-4">
                <div className="w-full max-w-2xl bg-red-50 p-4 rounded-xl border border-red-200 text-red-700">
                    Error: {error}
                </div>
            </div>
        );
    }

    return (
        <div className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex flex-col">
            {step === 1 && renderLanguageSelection()}
            {step === 2 && renderIntentSelection()}
            {step === 3 && renderEmployerSelection()}
            {step === 4 && renderProductSelection()}
            {step === 5 && renderAccountCheck()}
            {step === 6 && renderWantAccount()}
            {step === 'check-status' && renderStatusCheck()}
            {(step === 'final' || step === 'terminate') && renderFinal()}

            {/* Confirmation Dialog for when user doesn't agree to terms */}
            {showConfirmDialog && (
                <ConfirmationDialog
                    message="Are you sure you don't want to proceed with the application?"
                    onConfirm={() => handleConfirmNoTerms(true)}
                    onCancel={() => handleConfirmNoTerms(false)}
                />
            )}
        </div>
    );
};

export default CreditApplicationFlow;
