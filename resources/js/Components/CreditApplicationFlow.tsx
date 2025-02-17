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
};

type Product = {
    id: number;
    name: string;
    base_price: string;
    image: string;
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
    } | null;
    hasAccount: string;
    wantsAccount: string;
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

    const fetchCategories = async (categoryId?: number) => {
        setLoading(true);
        setError(null);
        try {
            const apiUrl = categoryId
                ? `${import.meta.env.VITE_API_BASE_URL}/api/categories/${categoryId}`
                : `${import.meta.env.VITE_API_BASE_URL}/api/categories`;

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

    const Button = ({children, onClick, variant = 'default', icon: Icon}) => (
        <button
            onClick={onClick}
            className={`w-full p-4 rounded-xl transition-all duration-300 flex items-center justify-between
                ${variant === 'primary'
                ? 'bg-gradient-to-r from-emerald-500 to-orange-400 text-white hover:from-emerald-600 hover:to-orange-500'
                : 'border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50'}`}
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
                            setStep(option.action === 'starterPack' ? 3 : 'final');
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
                        {name: 'Corporate Company', form: 'check-account'},
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
                                        <p className="text-emerald-600 font-medium">
                                            Base Price: ${product.base_price}
                                        </p>
                                        {selectedProductId === product.id ? (
                                            <div className="space-y-3">
                                                <h4 className="font-medium text-gray-700">Credit Options:</h4>
                                                <div className="grid grid-cols-1 gap-2">
                                                    {product.credit_options.map((option) => (
                                                        <button
                                                            key={option.months}
                                                            onClick={() => {
                                                                setFormData(prev => ({
                                                                    ...prev,
                                                                    selectedProduct: {
                                                                        product,
                                                                        selectedCreditOption: option,
                                                                        category: currentCategory?.name || ''
                                                                    }
                                                                }));
                                                                setStep(5);
                                                            }}
                                                            className="p-3 rounded-xl border transition-all hover:border-emerald-500 hover:bg-emerald-50"
                                                        >
                                                            <div className="flex justify-between items-center">
                                                                <div>
                                                                    <div
                                                                        className="font-medium">{option.months} Months
                                                                    </div>
                                                                    <div
                                                                        className="text-sm text-gray-500">Interest: {option.interest}%
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    className="text-emerald-600 font-semibold">${option.final_price}</div>
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
                                                Select Instalment/Period:
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
                            setFormData(prev => ({...prev, hasAccount: 'yes'}));
                            setStep('final');
                        }}
                        icon={CheckCircle2}
                    >
                        Yes, I have an account
                    </Button>
                    <Button
                        onClick={() => {
                            setFormData(prev => ({...prev, hasAccount: 'no'}));
                            setStep(6);
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
                            setFormData(prev => ({...prev, wantsAccount: 'yes'}));
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
                                </>
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
            {(step === 'final' || step === 'terminate') && renderFinal()}
        </div>
    );
};

export default CreditApplicationFlow;
