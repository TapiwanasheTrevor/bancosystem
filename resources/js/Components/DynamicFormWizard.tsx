import React, {useState, useEffect} from 'react';
import {ChevronLeft, ChevronRight} from 'lucide-react';
import Lottie from 'react-lottie';
import completeAnimation from './complete.json';

interface DynamicFormWizardProps {
    formId: string;
    initialData?: any;
    onComplete: (insertId: string) => void;
}

interface Field {
    type: 'text' | 'email' | 'number' | 'select' | 'radio' | 'checkbox_list' | 'textarea';
    label: string;
    required: boolean;
    options?: string[];
}

interface Section {
    title: string;
    description: string;
    fields: Field[];
}

interface FormDataType {
    title: string;
    description: string;
    sections: Section[];
    fileName: string;
}

const DynamicFormWizard = ({formId, initialData, onComplete}: DynamicFormWizardProps) => {
    const [formData, setFormData] = useState<FormDataType | null>(null);
    const [currentSection, setCurrentSection] = useState(0);
    const [formValues, setFormValues] = useState<Record<string, any>>({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);  // Ensure success state is defined
    const [agentId, setAgentId] = useState<string | null>(null);

    useEffect(() => {
        fetchFormData();

        if (initialData) {
            const prepopulatedData = {
                'personal-details': {
                    'full-name': initialData.applicationDetails?.name || '',
                    'email': initialData.applicationDetails?.email || '',
                    'phone': initialData.applicationDetails?.phone || '',
                    'id-number': initialData.applicationDetails?.idNumber || '',
                },
                'employment-details': {
                    'employer': initialData.employer || '',
                },
                'product-details': {
                    'selected-product': initialData.selectedProduct?.product.name || '',
                    'credit-option': initialData.selectedProduct?.selectedCreditOption || ''
                }
            };
            setFormValues(prepopulatedData);
        }

        const urlParams = new URLSearchParams(window.location.search);
        const referral = urlParams.get('referral');
        if (referral) {
            setAgentId(referral);
        }
    }, [formId, initialData]);

    const fetchFormData = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetch(`/api/forms/${formId}`);
            if (!response.ok) throw new Error('Failed to fetch form data');
            const data = await response.json();
            setFormData(data.form);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (fieldName: string, value: any) => {
        setFormValues(prev => ({
            ...prev,
            [fieldName]: value
        }));
    };

    const renderStepIndicator = () => (
        <div className="space-y-2">
            <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                <div
                    className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
                    style={{width: `${((currentSection + 1) / (formData?.sections?.length || 1)) * 100}%`}}
                />
            </div>
            <div className="text-sm text-gray-600 text-right">
                Step {currentSection + 1} of {formData?.sections?.length}
            </div>
        </div>
    );

    const renderField = (field: Field) => {
        const fieldId = `${field.label.toLowerCase().replace(/\s+/g, '-')}`;
        const baseInputStyles = "w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300";

        switch (field.type) {
            case 'text':
            case 'email':
            case 'number':
                return (
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-emerald-500">*</span>}
                        </label>
                        <input
                            type={field.type}
                            id={fieldId}
                            className={`${baseInputStyles} hover:border-emerald-300`}
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                            placeholder={`Enter ${field.label.toLowerCase()}`}
                        />
                    </div>
                );

            case 'select':
                return (
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-emerald-500">*</span>}
                        </label>
                        <select
                            id={fieldId}
                            className={`${baseInputStyles} hover:border-emerald-300`}
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                        >
                            <option value="">Select {field.label}</option>
                            {field.options?.map((option, idx) => (
                                <option key={idx} value={option}>{option}</option>
                            ))}
                        </select>
                    </div>
                );

            case 'radio':
                return (
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-2 text-gray-700">
                            {field.label} {field.required && <span className="text-emerald-500">*</span>}
                        </label>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            {field.options?.map((option, idx) => (
                                <div
                                    key={idx}
                                    className="flex items-center p-3 border rounded-xl hover:border-emerald-300 transition-all duration-300 bg-white"
                                >
                                    <input
                                        type="radio"
                                        id={`${fieldId}-${idx}`}
                                        name={fieldId}
                                        value={option}
                                        required={field.required}
                                        onChange={(e) => handleInputChange(fieldId, e.target.value)}
                                        checked={formValues[fieldId] === option}
                                        className="mr-3 text-emerald-500 focus:ring-emerald-400 h-4 w-4"
                                    />
                                    <label
                                        htmlFor={`${fieldId}-${idx}`}
                                        className="text-gray-700 text-sm flex-1 cursor-pointer truncate"
                                        title={option}
                                    >
                                        {option}
                                    </label>
                                </div>
                            ))}
                        </div>
                    </div>
                );

            case 'checkbox_list':
                return (
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-2 text-gray-700">
                            {field.label} {field.required && <span className="text-emerald-500">*</span>}
                        </label>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            {field.options?.map((option, idx) => (
                                <div
                                    key={idx}
                                    className="flex items-center p-3 border rounded-xl hover:border-emerald-300 transition-all duration-300 bg-white"
                                >
                                    <input
                                        type="checkbox"
                                        id={`${fieldId}-${idx}`}
                                        value={option}
                                        onChange={(e) => {
                                            const currentValues = formValues[fieldId] || [];
                                            const newValues = e.target.checked
                                                ? [...currentValues, option]
                                                : currentValues.filter(val => val !== option);
                                            handleInputChange(fieldId, newValues);
                                        }}
                                        checked={(formValues[fieldId] || []).includes(option)}
                                        className="mr-3 text-emerald-500 focus:ring-emerald-400 h-4 w-4 rounded"
                                    />
                                    <label
                                        htmlFor={`${fieldId}-${idx}`}
                                        className="text-gray-700 text-sm flex-1 cursor-pointer truncate"
                                        title={option}
                                    >
                                        {option}
                                    </label>
                                </div>
                            ))}
                        </div>
                    </div>
                );

            case 'textarea':
                return (
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-emerald-500">*</span>}
                        </label>
                        <textarea
                            id={fieldId}
                            className={`${baseInputStyles} hover:border-emerald-300`}
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                            rows={4}
                            placeholder={`Enter ${field.label.toLowerCase()}`}
                        />
                    </div>
                );

            default:
                return null;
        }
    };

    const handleSubmit = async () => {
        try {
            const response = await fetch('/api/submit-form', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    formId,
                    formValues,
                    questionnaireData: initialData,
                    agent_id: agentId,
                    formName: formData?.fileName,
                }),
            });

            if (!response.ok) throw new Error('Failed to submit form');
            const {insertId} = await response.json();
            setSuccess(true);  // Set success state to true
            onComplete(insertId);
        } catch (err) {
            console.error('Error submitting form:', err);
            setError('Failed to submit form. Please try again.');
        }
    };

    const defaultOptions = {
        loop: true,
        autoplay: true,
        animationData: completeAnimation,
        rendererSettings: {
            preserveAspectRatio: 'xMidYMid slice'
        }
    };

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

    if (success) {
        return (
            <div
                className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex items-center justify-center">
                <Lottie options={defaultOptions} height={400} width={400}/>
            </div>
        );
    }

    if (!formData) return null;

    const currentSectionData = formData.sections[currentSection];

    return (
        <div className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex flex-col">
            <div className="flex-1 overflow-auto">
                <div className="min-h-full p-4 md:p-6">
                    <div className="max-w-6xl mx-auto space-y-6">
                        {currentSection > 0 && (
                            <button
                                onClick={() => setCurrentSection(prev => prev - 1)}
                                className="flex items-center text-emerald-600 hover:text-emerald-700 transition-colors"
                            >
                                <ChevronLeft className="w-4 h-4 mr-2"/>
                                Back
                            </button>
                        )}

                        {renderStepIndicator()}

                        <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div className="p-6 md:p-8">
                                <div className="text-center space-y-2 mb-8">
                                    <h2 className="text-2xl font-semibold text-gray-800">
                                        {currentSectionData.title || formData.title}
                                    </h2>
                                    <p className="text-gray-600">
                                        {currentSectionData.description || formData.description}
                                    </p>
                                </div>

                                <div className="space-y-4">
                                    {currentSectionData.fields.map((field, index) => (
                                        <div key={index}>
                                            {renderField(field)}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="sticky bottom-0 p-6 md:p-8 bg-gray-50 border-t border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-500">
                                        Section {currentSection + 1} of {formData.sections.length}
                                    </div>

                                    {currentSection < formData.sections.length - 1 ? (
                                        <button
                                            onClick={() => setCurrentSection(prev => prev + 1)}
                                            className="px-6 py-3 text-white bg-gradient-to-r from-emerald-500 to-orange-400 rounded-xl hover:from-emerald-600 hover:to-orange-500 transition-all duration-300 flex items-center"
                                        >
                                            Next
                                            <ChevronRight className="w-4 h-4 ml-2"/>
                                        </button>
                                    ) : (
                                        <button
                                            onClick={handleSubmit}
                                            className="px-6 py-3 text-white bg-gradient-to-r from-emerald-500 to-orange-400 rounded-xl hover:from-emerald-600 hover:to-orange-500 transition-all duration-300"
                                        >
                                            Submit Application
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DynamicFormWizard;
