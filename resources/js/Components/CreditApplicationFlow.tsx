import React, {useState, useEffect} from 'react';

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

const CreditApplicationFlow = ({onComplete}) => {
    const [step, setStep] = useState(1);
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

    // Fetch initial categories or category details
    const fetchCategories = async (categoryId?: number) => {
        setLoading(true);
        setError(null);
        try {
            const apiUrl = categoryId
                ? `${import.meta.env.VITE_API_BASE_URL}/api/categories/${categoryId}`
                : `${import.meta.env.VITE_API_BASE_URL}/api/categories`;

            const response = await fetch(apiUrl);
            if (!response.ok) {
                throw new Error('Failed to fetch categories');
            }

            const responseData = await response.json();

            if (categoryId) {
                // Single category response
                const categoryData = responseData as CategoryResponse;
                setCurrentCategory(categoryData.data);
                if (categoryData.data.products?.length) {
                    setCategories([]); // Clear categories when showing products
                } else if (categoryData.data.subcategories?.length) {
                    setCategories(categoryData.data.subcategories);
                }
            } else {
                // Multiple categories response
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

    const renderCreditOptions = (product: Product) => (
        <div className="mt-4 space-y-3">
            <h4 className="font-medium text-gray-700">Credit Options:</h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
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
                        className={`p-3 rounded-lg border transition-all ${
                            formData.selectedProduct?.selectedCreditOption === option
                                ? 'border-blue-500 bg-blue-50'
                                : 'border-gray-200 hover:border-blue-300'
                        }`}
                    >
                        <div className="text-sm font-medium">{option.months} Months</div>
                        <div className="text-xs text-gray-500">Interest: {option.interest}%</div>
                        <div className="text-sm font-semibold">${option.final_price}</div>
                    </button>
                ))}
            </div>
        </div>
    );

    // Modified Product Selection Step
    const renderProductSelection = () => (
        <div className="space-y-8">
            {loading ? (
                <div className="text-center py-12">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading...</p>
                </div>
            ) : error ? (
                <div className="text-center py-12">
                    <p className="text-red-500">{error}</p>
                    <button
                        onClick={() => currentCategory ? fetchCategories(currentCategory.id) : fetchCategories()}
                        className="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                    >
                        Retry
                    </button>
                </div>
            ) : (
                <div className="space-y-6">
                    {/* Breadcrumb Navigation */}
                    {categoryHistory.length > 0 && (
                        <div className="flex items-center space-x-2 text-sm text-gray-600">
                            <button
                                onClick={handleBackClick}
                                className="hover:text-blue-600"
                            >
                                ← Back
                            </button>
                            <span className="mx-2">|</span>
                            {categoryHistory.map((cat, index) => (
                                <React.Fragment key={cat.id}>
                                    <span>{cat.name}</span>
                                    {index < categoryHistory.length - 1 && (
                                        <span className="mx-2">/</span>
                                    )}
                                </React.Fragment>
                            ))}
                        </div>
                    )}

                    <div className="text-center">
                        <h2 className="text-2xl font-semibold">
                            {currentCategory ? currentCategory.name : 'Select a Category'}
                        </h2>
                    </div>

                    {/* Display Categories or Products */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {categories.length > 0 ? (
                            // Render Categories
                            categories.map((category) => (
                                <button
                                    key={category.id}
                                    onClick={() => handleCategoryClick(category)}
                                    className="group relative bg-white p-4 border rounded-lg hover:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md"
                                >
                                    <div className="text-center">
                                        <h3 className="font-semibold text-lg group-hover:text-blue-600 transition-colors">
                                            {category.name}
                                        </h3>
                                    </div>
                                </button>
                            ))
                        ) : currentCategory?.products?.length ? (
                            // Render Products
                            currentCategory.products.map((product) => (
                                <div
                                    key={product.id}
                                    className="relative bg-white p-4 border rounded-lg transition-all duration-300 shadow-sm hover:shadow-md"
                                >
                                    <div className="aspect-w-16 aspect-h-9 mb-4">
                                        <img
                                            src={product.image}
                                            alt={product.name}
                                            className="w-full h-48 object-cover rounded-md"
                                        />
                                    </div>
                                    <div className="text-center">
                                        <h3 className="font-semibold text-lg">
                                            {product.name}
                                        </h3>
                                        <p className="text-md font-semibold text-gray-700 mt-2">
                                            Base Price: ${product.base_price}
                                        </p>
                                        {selectedProductId === product.id ? (
                                            renderCreditOptions(product)
                                        ) : (
                                            <button
                                                onClick={() => setSelectedProductId(product.id)}
                                                className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                            >
                                                Select Credit Option
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-3 text-center py-12 text-gray-500">
                                No items found
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );

    // Step 1: Language Selection
    const renderLanguageSelection = () => (
        <div className="space-y-6">
            <div className="text-center space-y-4">
                <img
                    src="https://images.unsplash.com/photo-1531379410502-63bfe8cdaf6f?w=500&auto=format"
                    alt="Adala Bot"
                    className="mx-auto rounded-full w-20 h-20 object-cover"
                />
                <h2 className="text-xl font-semibold">Hi there! I am Adala, a smart assistant chatbot.</h2>
                <p className="text-gray-600">Consider me your uncle from a different mother. My mission is to ensure you
                    get the best online service experience possible for your next credit consideration.</p>
            </div>

            <div className="space-y-4">
                <h3 className="text-lg font-medium text-center">Select a Language to Proceed</h3>
                <div className="flex justify-center gap-4">
                    {['English', 'Shona', 'Ndebele'].map((lang) => (
                        <button
                            key={lang}
                            onClick={() => {
                                setFormData(prev => ({...prev, language: lang}));
                                setStep(2);
                            }}
                            className="px-6 py-3 rounded-md border hover:bg-blue-50 transition-colors"
                        >
                            {lang}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );

    // Step 2: Intent Selection
    const renderIntentSelection = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">What would you like to do?</h2>
                <p className="text-gray-600">I would love to help you unlock the maximum benefit of your credit, so
                    please be patient as I ask a few questions.</p>
            </div>

            <div className="grid grid-cols-1 gap-4 max-w-xl mx-auto">
                {[
                    {
                        title: 'Apply for Hire Purchase Credit',
                        subtitle: 'Personal and Household Products',
                        action: 'hirePurchase'
                    },
                    {
                        title: 'Apply for Micro Biz',
                        subtitle: 'Ngwavha, Hustle, Spana Starter Pack',
                        action: 'starterPack'
                    },
                    {
                        title: 'Get an update on your application status',
                        subtitle: 'Check your existing application',
                        action: 'checkStatus'
                    },
                    {
                        title: 'Track the delivery of product/equipment',
                        subtitle: 'Track your order',
                        action: 'trackDelivery'
                    }
                ].map((option) => (
                    <button
                        key={option.action}
                        onClick={() => {
                            setFormData(prev => ({...prev, intent: option.action}));
                            setStep(option.action === 'starterPack' ? 3 : 'final');
                        }}
                        className="p-4 border rounded-lg text-left hover:bg-blue-50 transition-colors"
                    >
                        <div className="font-semibold">{option.title}</div>
                        <div className="text-sm text-gray-600">{option.subtitle}</div>
                    </button>
                ))}
            </div>
        </div>
    );

    // Step 3: Employer Selection
    const renderEmployerSelection = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">Who is your employer?</h2>
                <p className="text-gray-600">Before proceeding with your Micro Biz application, I need to verify your
                    employment details.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
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
                    <button
                        key={employer.name}
                        onClick={() => {
                            setFormData(prev => ({...prev, employer: employer.name}));
                            setStep(4);
                        }}
                        className="p-4 border rounded-lg text-left hover:bg-blue-50 transition-colors"
                    >
                        {employer.name}
                    </button>
                ))}
            </div>
        </div>
    );

    // Step 5: Account Check
    const renderAccountCheck = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">Do you have a ZB Account?</h2>
            </div>

            <div className="flex justify-center gap-4">
                <button
                    onClick={() => {
                        setFormData(prev => ({...prev, hasAccount: 'yes'}));
                        setStep('final');
                    }}
                    className="px-6 py-3 rounded-md border hover:bg-blue-50 transition-colors"
                >
                    Yes
                </button>
                <button
                    onClick={() => {
                        setFormData(prev => ({...prev, hasAccount: 'no'}));
                        setStep(6);
                    }}
                    className="px-6 py-3 rounded-md border hover:bg-blue-50 transition-colors"
                >
                    No
                </button>
            </div>
        </div>
    );

    // Step 6: Want Account
    const renderWantAccount = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">Would you like to open an account with ZB?</h2>
            </div>

            <div className="flex justify-center gap-4">
                <button
                    onClick={() => {
                        setFormData(prev => ({...prev, wantsAccount: 'yes'}));
                        setStep('final');
                    }}
                    className="px-6 py-3 rounded-md border hover:bg-blue-50 transition-colors"
                >
                    Yes
                </button>
                <button
                    onClick={() => {
                        setFormData(prev => ({...prev, wantsAccount: 'no'}));
                        setStep('terminate');
                    }}
                    className="px-6 py-3 rounded-md border hover:bg-blue-50 transition-colors"
                >
                    No
                </button>
            </div>
        </div>
    );

    // Final Step / Termination
    const renderFinal = () => (
        <div className="space-y-6 text-center">
            <h2 className="text-xl font-semibold">
                {step === 'terminate'
                    ? "Thank you for your interest"
                    : "Great! Let's proceed with your application"}
            </h2>
            <p className="text-gray-600">
                {step === 'terminate'
                    ? "Unfortunately, we cannot proceed without a ZB Bank account. We hope to serve you in the future."
                    : "We'll now redirect you to the appropriate application form based on your selections."}
            </p>
            {step !== 'terminate' && (
                <button
                    onClick={() => onComplete(formData)}
                    className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Continue to Application
                </button>
            )}
        </div>
    );

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-8">
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
