import React, {useState, useEffect} from 'react';
import {ChevronLeft, ChevronRight} from 'lucide-react';

const DynamicFormWizard = ({formId, initialData}) => {
    const [formData, setFormData] = useState(null);
    const [currentSection, setCurrentSection] = useState(0);
    const [formValues, setFormValues] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFormData();
        // Pre-populate form values with initial data if available
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
                    'selected-product': initialData.selectedProduct?.name || '',
                }
            };
            setFormValues(prepopulatedData);
        }
    }, [formId, initialData]);

    const fetchFormData = async () => {
        try {
            // In production, this would be an API call to fetch the form structure
            const response = await fetch(`/api/forms/${formId}`);
            if (!response.ok) throw new Error('Failed to fetch form data');
            const data = await response.json();
            setFormData(data.form);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const handleInputChange = (fieldName, value) => {
        setFormValues(prev => ({
            ...prev,
            [fieldName]: value
        }));
    };

    const renderField = (field) => {
        const fieldId = `${field.label.toLowerCase().replace(/\s+/g, '-')}`;

        switch (field.type) {
            case 'text':
            case 'email':
            case 'number':
                return (
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-red-500">*</span>}
                        </label>
                        <input
                            type={field.type}
                            id={fieldId}
                            className="w-full p-2 border rounded-md"
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                        />
                    </div>
                );

            case 'select':
                return (
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-red-500">*</span>}
                        </label>
                        <select
                            id={fieldId}
                            className="w-full p-2 border rounded-md"
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                        >
                            <option value="">Select {field.label}</option>
                            {field.options.map((option, idx) => (
                                <option key={idx} value={option}>{option}</option>
                            ))}
                        </select>
                    </div>
                );

            case 'radio':
                return (
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1">
                            {field.label} {field.required && <span className="text-red-500">*</span>}
                        </label>
                        <div className="space-y-2">
                            {field.options.map((option, idx) => (
                                <div key={idx} className="flex items-center">
                                    <input
                                        type="radio"
                                        id={`${fieldId}-${idx}`}
                                        name={fieldId}
                                        value={option}
                                        required={field.required}
                                        onChange={(e) => handleInputChange(fieldId, e.target.value)}
                                        checked={formValues[fieldId] === option}
                                        className="mr-2"
                                    />
                                    <label htmlFor={`${fieldId}-${idx}`}>{option}</label>
                                </div>
                            ))}
                        </div>
                    </div>
                );

            case 'checkbox_list':
                return (
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1">
                            {field.label} {field.required && <span className="text-red-500">*</span>}
                        </label>
                        <div className="space-y-2">
                            {field.options.map((option, idx) => (
                                <div key={idx} className="flex items-center">
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
                                        className="mr-2"
                                    />
                                    <label htmlFor={`${fieldId}-${idx}`}>{option}</label>
                                </div>
                            ))}
                        </div>
                    </div>
                );

            case 'textarea':
                return (
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1" htmlFor={fieldId}>
                            {field.label} {field.required && <span className="text-red-500">*</span>}
                        </label>
                        <textarea
                            id={fieldId}
                            className="w-full p-2 border rounded-md"
                            required={field.required}
                            onChange={(e) => handleInputChange(fieldId, e.target.value)}
                            value={formValues[fieldId] || ''}
                            rows={4}
                        />
                    </div>
                );

            default:
                return null;
        }
    };

    const handleSubmit = async () => {
        try {
            // In production, this would be an API call to submit the form data
            const response = await fetch('/api/submit-form', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    formId,
                    formValues,
                    questionnaireData: initialData
                }),
            });

            if (!response.ok) throw new Error('Failed to submit form');

            // Handle successful submission
            console.log('Form submitted successfully');
            // You might want to redirect or show a success message here
        } catch (err) {
            console.error('Error submitting form:', err);
            setError('Failed to submit form. Please try again.');
        }
    };

    if (loading) return <div className="text-center p-4">Loading form...</div>;
    if (error) return <div className="text-red-500 p-4">Error: {error}</div>;
    if (!formData) return null;

    const currentSectionData = formData.sections[currentSection];

    return (
        <div className="w-full max-w-4xl mx-auto p-6">
            <div className="bg-white rounded-lg shadow-md">
                <div className="p-6 border-b">
                    <h1 className="text-2xl font-semibold">{formData.title}</h1>
                    <p className="text-gray-600 mt-2">{formData.description}</p>
                </div>

                <div className="p-6">
                    {currentSectionData.fields.map((field, index) => (
                        <div key={index}>
                            {renderField(field)}
                        </div>
                    ))}
                </div>

                <div className="p-6 border-t bg-gray-50 flex justify-between items-center">
                    <button
                        onClick={() => setCurrentSection(prev => prev - 1)}
                        disabled={currentSection === 0}
                        className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
                    >
                        <ChevronLeft className="w-4 h-4 mr-2 inline"/>
                        Previous
                    </button>

                    <div className="text-sm text-gray-500">
                        Step {currentSection + 1} of {formData.sections.length}
                    </div>

                    {currentSection < formData.sections.length - 1 ? (
                        <button
                            onClick={() => setCurrentSection(prev => prev + 1)}
                            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                        >
                            Next
                            <ChevronRight className="w-4 h-4 ml-2 inline"/>
                        </button>
                    ) : (
                        <button
                            onClick={handleSubmit}
                            className="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
                        >
                            Submit Application
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default DynamicFormWizard;
