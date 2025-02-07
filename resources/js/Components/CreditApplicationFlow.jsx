import React, { useState, useEffect } from 'react';

const CreditApplicationFlow = () => {
    const [step, setStep] = useState(1);
    const [formData, setFormData] = useState({
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

    const [products, setProducts] = useState([]);

    // Simulated products fetch
    useEffect(() => {
        // In real implementation, replace with actual API endpoint
        const fetchProducts = async () => {
            const response = {
                categories: [
                    {
                        id: 1,
                        name: "Micro Biz Starter Packs",
                        products: [
                            {
                                id: 1,
                                name: "Ngwavha Pack",
                                image_url: "/api/placeholder/200/200"
                            },
                            {
                                id: 2,
                                name: "Hustle Pack",
                                image_url: "/api/placeholder/200/200"
                            },
                            {
                                id: 3,
                                name: "Spana Pack",
                                image_url: "/api/placeholder/200/200"
                            }
                        ]
                    }
                ]
            };
            setProducts(response.categories);
        };
        fetchProducts();
    }, []);

    // Step 1: Language Selection
    const renderLanguageSelection = () => (
        <div className="space-y-6">
            <div className="text-center space-y-4">
                <img
                    src="/api/placeholder/80/80"
                    alt="Adala Bot"
                    className="mx-auto rounded-full"
                />
                <h2 className="text-xl font-semibold">Hi there! I am Adala, a smart assistant chatbot.</h2>
                <p className="text-gray-600">Consider me your uncle from a different mother. My mission is to ensure you get the best online service experience possible for your next credit consideration.</p>
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
                <p className="text-gray-600">I would love to help you unlock the maximum benefit of your credit, so please be patient as I ask a few questions.</p>
            </div>

            <div className="grid grid-cols-1 gap-4 max-w-xl mx-auto">
                {[
                    { title: 'Apply for Hire Purchase Credit', subtitle: 'Personal and Household Products', action: 'hirePurchase' },
                    { title: 'Apply for Micro Biz', subtitle: 'Ngwavha, Hustle, Spana Starter Pack', action: 'starterPack' },
                    { title: 'Get an update on your application status', subtitle: 'Check your existing application', action: 'checkStatus' },
                    { title: 'Track the delivery of product/equipment', subtitle: 'Track your order', action: 'trackDelivery' }
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

    // Step 3: Employer Selection (for Micro Biz)
    const renderEmployerSelection = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">Who is your employer?</h2>
                <p className="text-gray-600">Before proceeding with your Micro Biz application, I need to verify your employment details.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                {[
                    { name: 'GOZ (Government of Zimbabwe) - SSB', form: 'ssb' },
                    { name: 'GOZ - ZAPPA', form: 'zappa' },
                    { name: 'GOZ - Pension', form: 'pension' },
                    { name: 'Town Council', form: 'check-account' },
                    { name: 'Parastatal', form: 'check-account' },
                    { name: 'Corporate Company', form: 'check-account' },
                    { name: 'Mission and Private Schools', form: 'check-account' },
                    { name: 'SME (Small & Medium Enterprises)', form: 'sme' }
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

    // Step 4: Product Selection
    const renderProductSelection = () => (
        <div className="space-y-6">
            <div className="text-center">
                <h2 className="text-xl font-semibold">Select Your MicroBiz Start Pack</h2>
                <p className="text-gray-600">Choose the package that best suits your needs</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {products[0]?.products.map((product) => (
                    <button
                        key={product.id}
                        onClick={() => {
                            setFormData(prev => ({...prev, selectedProduct: product}));
                            setStep(5);
                        }}
                        className="p-4 border rounded-lg hover:border-blue-500 transition-colors"
                    >
                        <img src={product.image_url} alt={product.name} className="w-full h-48 object-cover rounded mb-4" />
                        <div className="font-semibold text-center">{product.name}</div>
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
                    onClick={() => console.log('Form Data:', formData)}
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
