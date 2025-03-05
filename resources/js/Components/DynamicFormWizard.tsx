import React, {useState, useEffect, useRef} from 'react';
import {ChevronLeft, ChevronRight, Search, AlertCircle} from 'lucide-react';
import Lottie from 'react-lottie';
import completeAnimation from './complete.json';
import SignaturePad from 'react-signature-canvas';

// Interfaces and types
interface DynamicFormWizardProps {
    formId: string;
    initialData?: any;
    onComplete: (insertId: string) => void;
}

interface Field {
    type: string;
    label: string;
    content?: string;
    required?: boolean;
    options?: string[];
    legend?: string;
    children?: Field[];
    html?: string;
    placeholder?: string;
    default?: string | number | boolean;
    readOnly?: boolean;
    bindTo?: string;
    accept?: string;
    component?: string;
    value?: string;
    id?: string;
    onChange?: {
        action: string;
        dependency?: string;
        values?: any;
        target?: string;
    };
}

interface Section {
    title: string;
    id?: string;
    description?: string;
    fields?: Field[];
    dynamicSection?: boolean;
    variants?: Record<string, { fields?: Field[] }>;
    generateFromCount?: boolean;
    templates?: {
        director?: {
            title: string;
            fields?: Field[];
        }
    };
}

interface FormDataType {
    title: string;
    description?: string;
    sections: Section[];
    fileName: string;
}

// Components
const BranchLocator = ({fieldId, onChange, value, required}) => {
    const [branches, setBranches] = useState([
        "21 Natal Branch", "Avondale Branch", "Beitbridge Branch", "Bindura Branch",
        "Chinhoyi Branch", "Chiredzi Branch", "Chisipite Branch", "Douglas Road Branch",
        "Fife Street Branch", "Graniteside Branch", "Gutu Branch", "Gwanda Branch",
        "Gweru Branch", "Hwange Branch", "Jason Moyo Branch", "Kadoma Branch",
        "Kariba Branch", "Karoi Branch", "Kwekwe Branch", "Long Chen Branch",
        "Masvingo Branch", "Msasa Branch", "Mt Darwin Branch", "Murombedzi Branch",
        "Mutare Branch", "Ngezi Branch", "Nyanga Branch", "Plumtree Branch",
        "Rotten Row Branch", "Rusape Branch", "Shurugwi Branch", "Triangle Branch",
        "Victoria Falls Branch", "Westend Branch", "Zvishavane Branch"
    ]);

    const [query, setQuery] = useState('');
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const [selectedBranch, setSelectedBranch] = useState(value?.name || value || '');
    const [branchCode, setBranchCode] = useState(value?.code || '');
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setDropdownOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const filteredBranches = branches.filter(branch =>
        branch.toLowerCase().includes(query.toLowerCase())
    );

    const handleSelect = (branch) => {
        setSelectedBranch(branch);
        const code = Math.floor(1000 + Math.random() * 9000).toString();
        setBranchCode(code);
        onChange({
            name: branch,
            code: code
        });
        setDropdownOpen(false);
        setQuery('');
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <div
                className="flex items-center border rounded-md shadow-sm focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500">
                <div className="pl-3 text-gray-400">
                    <Search size={18}/>
                </div>
                <input
                    type="text"
                    value={selectedBranch || query}
                    onChange={(e) => {
                        setQuery(e.target.value);
                        if (selectedBranch) {
                            setSelectedBranch('');
                            setBranchCode('');
                            onChange(null);
                        }
                        setDropdownOpen(true);
                    }}
                    onClick={() => setDropdownOpen(true)}
                    placeholder="Search for a branch"
                    className="w-full p-3 outline-none rounded-md"
                    required={required}
                />
            </div>
            {dropdownOpen && (
                <div
                    className="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-64 overflow-y-auto">
                    {filteredBranches.length > 0 ? (
                        filteredBranches.map((branch, index) => (
                            <div
                                key={index}
                                className="px-4 py-2 cursor-pointer hover:bg-emerald-50"
                                onClick={() => handleSelect(branch)}
                            >
                                {branch}
                            </div>
                        ))
                    ) : (
                        <div className="px-4 py-2 text-gray-500">No branches found</div>
                    )}
                </div>
            )}
            {selectedBranch && (
                <div className="text-sm text-gray-600">
                    Branch code: <span className="font-medium">{branchCode}</span>
                </div>
            )}
        </div>
    );
};

const Select2 = ({fieldId, options, required, onChange, value, placeholder}) => {
    const [query, setQuery] = useState('');
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setDropdownOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const filteredOptions = options.filter(option =>
        option.toLowerCase().includes(query.toLowerCase())
    );

    const handleSelect = (option) => {
        onChange(option);
        setDropdownOpen(false);
        setQuery('');
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <div
                className="flex items-center border rounded-md shadow-sm focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500">
                <div className="pl-3 text-gray-400">
                    <Search size={18}/>
                </div>
                <input
                    type="text"
                    value={value || query}
                    onChange={(e) => {
                        setQuery(e.target.value);
                        if (value) onChange('');
                        setDropdownOpen(true);
                    }}
                    onClick={() => setDropdownOpen(true)}
                    placeholder={placeholder}
                    className="w-full p-2 outline-none"
                    required={required}
                />
            </div>
            {dropdownOpen && (
                <div
                    className="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-64 overflow-y-auto">
                    {filteredOptions.length > 0 ? (
                        filteredOptions.map((option, index) => (
                            <div
                                key={index}
                                className="px-4 py-2 cursor-pointer hover:bg-emerald-50"
                                onClick={() => handleSelect(option)}
                            >
                                {option}
                            </div>
                        ))
                    ) : (
                        <div className="px-4 py-2 text-gray-500">No options found</div>
                    )}
                </div>
            )}
        </div>
    );
};

const DynamicFormWizard = ({formId, initialData, onComplete}: DynamicFormWizardProps) => {
    const [formData, setFormData] = useState<FormDataType | null>(null);
    const [currentSection, setCurrentSection] = useState(0);
    const [formValues, setFormValues] = useState<Record<string, any>>({});
    const [sectionVariants, setSectionVariants] = useState<Record<string, string>>({});
    const [directorCount, setDirectorCount] = useState<number>(1);
    const [fieldValidation, setFieldValidation] = useState<Record<string, boolean>>({});
    const [attemptedValidation, setAttemptedValidation] = useState<boolean>(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [confirmDialogMessage, setConfirmDialogMessage] = useState('');
    const [confirmDialogAction, setConfirmDialogAction] = useState<() => void>(() => {
    });
    const [agentId, setAgentId] = useState<string | null>(null);
    const signaturePadRef = useRef<SignaturePad>(null);
    const fileInputRefs = useRef<Record<string, HTMLInputElement | null>>({});

    useEffect(() => {
        fetchFormData();

        const urlParams = new URLSearchParams(window.location.search);
        const referral = urlParams.get('referral');
        if (referral) {
            setAgentId(referral);
        }
    }, [formId]);

    useEffect(() => {
        if (formData && initialData) {
            initializeFormWithProductData();
        }
    }, [formData, initialData]);
    
    // Clear validation state when changing sections to only validate on Next button click
    useEffect(() => {
        setFieldValidation({});
        setAttemptedValidation(false);
        
        // Pre-validate read-only fields for the current section
        if (formData && formData.sections[currentSection]) {
            const section = formData.sections[currentSection];
            const preValidation: Record<string, boolean> = {};
            
            const preValidateReadOnly = (fields: Field[]) => {
                if (!fields || !Array.isArray(fields)) return;
                
                fields.forEach(field => {
                    if (field.readOnly) {
                        const fieldId = field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`;
                        preValidation[fieldId] = true;
                    }
                    
                    if (field.type === 'fieldset' && field.children) {
                        preValidateReadOnly(field.children);
                    }
                });
            };
            
            if (section.fields) {
                preValidateReadOnly(section.fields);
            }
            
            if (Object.keys(preValidation).length > 0) {
                setFieldValidation(preValidation);
            }
        }
    }, [currentSection, formData]);

    const initializeFormWithProductData = () => {
        try {
            const initialFormValues: Record<string, any> = {};

            if (initialData?.applicationDetails) {
                const {name, phone, email, idNumber} = initialData.applicationDetails;
                if (name) {
                    const nameParts = name.split(' ');
                    let firstName, surname;
                    if (nameParts.length >= 2) {
                        firstName = nameParts[0];
                        surname = nameParts.slice(1).join(' ');
                        initialFormValues['first-name'] = firstName;
                        initialFormValues['surname'] = surname;
                        initialFormValues['forename'] = firstName; // For account_holder_loan_application
                        initialFormValues['forenames'] = firstName; // For variations
                    } else {
                        firstName = name;
                        initialFormValues['first-name'] = firstName;
                        initialFormValues['forename'] = firstName;
                        initialFormValues['forenames'] = firstName;
                    }
                    
                    // Populate customer name fields for ALL forms
                    initialFormValues['customerFirstName'] = firstName;
                    initialFormValues['customerSurname'] = surname || '';
                    initialFormValues['customerFullName'] = name;
                    // For account_holder_loan_application form
                    initialFormValues['customerForename'] = firstName;
                    initialFormValues['customerForenames'] = firstName;
                }
                
                if (phone) {
                    initialFormValues['cell-number'] = phone;
                    initialFormValues['phone'] = phone;
                    initialFormValues['phone-number'] = phone;
                    initialFormValues['customerCellNumber'] = phone;
                }
                
                if (email) {
                    initialFormValues['email-address'] = email;
                    initialFormValues['email'] = email;
                    initialFormValues['customerEmail'] = email;
                }
                
                if (idNumber) {
                    initialFormValues['id-number'] = idNumber;
                    initialFormValues['national-id'] = idNumber;
                    initialFormValues['customerIdNumber'] = idNumber;
                }
            }

            if (initialData?.employer) {
                initialFormValues['employer-name'] = initialData.employer;
                initialFormValues['employer'] = initialData.employer;
                initialFormValues['customerEmployer'] = initialData.employer;
                
                // Populate ministry field for SSB form if the employer is GOZ SSB
                if (initialData.employer === 'GOZ (Government of Zimbabwe) - SSB') {
                    // Extract ministry data from the form if available
                    if (formData?.sections) {
                        const customerSection = formData.sections.find(section => 
                            section.title === 'Customer Personal Details');
                        
                        if (customerSection && customerSection.fields) {
                            const ministryField = customerSection.fields.find(field => 
                                field.label === 'Name of Responsible Ministry');
                            
                            if (ministryField && ministryField.type === 'select' && 
                                Array.isArray(ministryField.options) && ministryField.options.length > 0) {
                                // Default to first ministry or set empty to require selection
                                initialFormValues['customerMinistry'] = '';
                            }
                        }
                    }
                }
            }

            if (initialData?.selectedProduct) {
                const {product, selectedCreditOption} = initialData.selectedProduct;

                if (product) {
                    initialFormValues['purpose/asset-applied-for'] = product.name || '';
                    initialFormValues['productDescription'] = product.description || product.name || '';
                }

                if (selectedCreditOption) {
                    const startDate = calculateLoanStartDate();
                    const endDate = calculateLoanEndDate(startDate, selectedCreditOption.months);

                    initialFormValues['productInstallment'] = selectedCreditOption.installment_amount ||
                        (parseFloat(selectedCreditOption.final_price) / selectedCreditOption.months).toFixed(2);
                    initialFormValues['productLoanPeriod'] = `${selectedCreditOption.months} months`;
                    initialFormValues['productLoanPeriodMonths'] = selectedCreditOption.months;
                    initialFormValues['autoLoanStartDate'] = startDate;
                    initialFormValues['autoLoanEndDate'] = endDate;
                    initialFormValues['autoLoanStartDateText'] = formatDateForDisplay(startDate);
                    initialFormValues['autoLoanEndDateText'] = formatDateForDisplay(endDate);

                    if (selectedCreditOption.final_price) {
                        initialFormValues['loan-amount'] = selectedCreditOption.final_price;
                        initialFormValues['applied-amount'] = selectedCreditOption.final_price;
                    }
                }
            }

            initialFormValues['account-type'] = 'USD Account';
            initialFormValues['currency-of-account'] = 'USD';

            setFormValues(prev => ({...prev, ...initialFormValues}));
        } catch (err) {
            console.error("Error initializing form with product data:", err);
        }
    };

    const calculateLoanStartDate = () => {
        try {
            const today = new Date();
            return new Date(today.getFullYear(), today.getMonth() + 1, 1).toISOString().split('T')[0];
        } catch (error) {
            console.error("Error calculating loan start date:", error);
            return new Date().toISOString().split('T')[0];
        }
    };

    const calculateLoanEndDate = (startDateStr: string, loanPeriodMonths: number) => {
        try {
            const startDate = new Date(startDateStr);
            // To get the last day of a month: create a date for the first day of the next month, then subtract one day
            const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + loanPeriodMonths + 1, 0);
            return endDate.toISOString().split('T')[0];
        } catch (error) {
            console.error("Error calculating loan end date:", error);
            // Fallback: create a date 3 months from now and get the last day of that month
            const today = new Date();
            const fallbackEndDate = new Date(today.getFullYear(), today.getMonth() + 4, 0);
            return fallbackEndDate.toISOString().split('T')[0];
        }
    };

    const formatDateForDisplay = (dateStr: string) => {
        try {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'});
        } catch (error) {
            console.error("Error formatting date:", error);
            return dateStr;
        }
    };

    const fetchFormData = async () => {
        setLoading(true);
        setError(null);
        try {
            let data = null;
            
            // Always fetch from API endpoint for consistency
            const response = await fetch(`/api/forms/${formId}`);
            if (!response.ok) throw new Error('Failed to fetch form data');
            data = await response.json();

            if (!data?.form || !Array.isArray(data.form.sections)) {
                throw new Error('Invalid form structure received from server');
            }

            console.log(`Form data loaded for ${formId}:`, data.form);
            setFormData(data.form);

            const defaultValues: Record<string, any> = {};

            data.form.sections.forEach(section => {
                if (!section || !Array.isArray(section.fields)) return;

                section.fields.forEach(field => {
                    if (!field) return;

                    if (field.default !== undefined) {
                        const fieldId = field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`;
                        defaultValues[fieldId] = field.default;
                    }
                });
            });

            setFormValues(prevValues => ({
                ...defaultValues,
                ...prevValues
            }));
        } catch (err) {
            console.error("Error fetching form data:", err);
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (fieldId: string, value: any, field?: Field) => {
        if (!fieldId) return;

        setFormValues(prev => {
            const newValues = {...prev, [fieldId]: value};

            // Handle bindTo - direct binding from this field to another field
            if (field?.bindTo && field.bindTo in prev) {
                newValues[field.bindTo] = value;
            }
            
            // Special case for all forms to sync customer data across sections
            
            // Handle first name variants (field IDs are converted to lowercase with hyphens)
            if (['first-name', 'forename', 'forenames'].includes(fieldId.toLowerCase()) || 
                ['First Name', 'Forename', 'Forenames'].includes(fieldId)) {
                newValues['customerFirstName'] = value;
                newValues['customerForename'] = value;
                newValues['customerForenames'] = value;
                
                // Update full name in all possible fields
                const surname = prev['surname'] || prev['customerSurname'] || '';
                const fullName = `${value} ${surname}`.trim();
                newValues['customerFullName'] = fullName;
                newValues['full-name'] = fullName;
            }
            
            // Handle surname variants
            if (['surname', 'last-name'].includes(fieldId.toLowerCase()) || 
                ['Surname', 'Last Name'].includes(fieldId)) {
                newValues['customerSurname'] = value;
                
                // Update full name in all possible fields
                const firstName = prev['first-name'] || prev['forename'] || prev['customerFirstName'] || '';
                const fullName = `${firstName} ${value}`.trim();
                newValues['customerFullName'] = fullName;
                newValues['full-name'] = fullName;
            }
            
            // Handle ID Number variants
            if (['id-number', 'national-id', 'identity-number'].includes(fieldId.toLowerCase()) || 
                ['ID Number', 'National ID', 'Identity Number'].includes(fieldId)) {
                newValues['customerIdNumber'] = value;
                newValues['national-id'] = value;
                newValues['id-number'] = value;
            }
            
            // Handle phone number variants
            if (['cell-number', 'phone-number', 'phone', 'mobile'].includes(fieldId.toLowerCase()) || 
                ['Cell Number', 'Phone Number', 'Phone', 'Mobile'].includes(fieldId)) {
                newValues['customerCellNumber'] = value;
                newValues['cell-number'] = value;
                newValues['phone'] = value;
            }
            
            // Handle email variants
            if (['email-address', 'email'].includes(fieldId.toLowerCase()) || 
                ['Email Address', 'Email'].includes(fieldId)) {
                newValues['customerEmail'] = value;
                newValues['email'] = value;
                newValues['email-address'] = value; 
            }
            
            // Handle ministry/employer specific fields
            if (fieldId === 'Name of Responsible Ministry' || fieldId === 'name-of-responsible-ministry') {
                newValues['customerMinistry'] = value;
            }
            
            if (['employer-name', 'employer'].includes(fieldId.toLowerCase()) || 
                ['Employer Name', 'Employer'].includes(fieldId)) {
                newValues['customerEmployer'] = value;
            }
            
            // Handle address fields
            if (['address', 'residential-address'].includes(fieldId.toLowerCase()) || 
                ['Address', 'Residential Address'].includes(fieldId)) {
                newValues['customerAddress'] = value;
            }

            if (field?.onChange) {
                handleFieldOnChange(field, value, newValues);
            }

            return newValues;
        });
    };

    const handleFieldOnChange = (field: Field, value: any, currentValues: Record<string, any>) => {
        try {
            const {action, dependency, values, target} = field.onChange || {};

            if (action === 'updateNextOfKin' && dependency && values) {
                const dependencyField = dependency.toLowerCase().replace(/\s+/g, '-');
                const dependencyValue = currentValues[dependencyField];

                if (dependencyValue && values[dependencyValue] && field.label.toLowerCase() === 'gender') {
                    const gender = value;
                    const relationship = values[dependencyValue][gender];

                    if (relationship && target) {
                        setFormValues(prev => ({
                            ...prev,
                            [target]: relationship
                        }));
                    } else if (relationship) {
                        setFormValues(prev => ({
                            ...prev,
                            'spouseRelationship': relationship
                        }));
                    }
                }
            }

            if (action === 'updateNextOfKinSection' && values) {
                const selectedVariant = values[value] || values.default;
                const sectionId = 'nextOfKinSection';

                setSectionVariants(prev => ({
                    ...prev,
                    [sectionId]: selectedVariant
                }));
            }

            if (action === 'generateDirectorSections' && field.label.includes('Directors')) {
                const count = parseInt(value) || 1;
                setDirectorCount(count);
            }

            if (action === 'confirmProceed' && values && value in values) {
                if (values[value] === 'showConfirmation') {
                    setConfirmDialogMessage('Are you sure you don\'t want to proceed with the application?');
                    setConfirmDialogAction(() => () => {
                        window.location.href = '/';
                    });
                    setShowConfirmDialog(true);
                }
            }
        } catch (error) {
            console.error("Error handling field onChange:", error);
        }
    };

    const bindFieldValue = (field: Field) => {
        if (!field.bindTo || !field.bindTo.trim()) return '';

        return formValues[field.bindTo] || '';
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
                Step {currentSection + 1} of {formData?.sections?.length || 0}
            </div>
        </div>
    );

    const renderField = (field: Field, sectionId: string = '') => {
        if (!field) return null;

        // The KYC Checklist section has been removed from the SME form
        // No need to check for bank statements anymore

        if (field.type === 'subtitle') {
            return (
                <div className="mb-4 mt-6">
                    <h3 className="text-lg font-medium text-gray-800 border-b pb-2">{field.label}</h3>
                </div>
            );
        }

        const fieldId = field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`;
        const baseInputStyles = "w-full p-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 text-gray-800 transition-all duration-300";

        const bindedValue = field.bindTo ? bindFieldValue(field) : undefined;
        const fieldValue = bindedValue !== undefined ? bindedValue : formValues[fieldId];

        const isReadOnly = field.readOnly || false;

        try {
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
                                className={`${baseInputStyles} ${isReadOnly ? 'bg-gray-100' : 'hover:border-emerald-300'} ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                required={field.required}
                                onChange={(e) => handleInputChange(fieldId, e.target.value, field)}
                                value={fieldValue || ''}
                                placeholder={field.placeholder || `Enter ${field.label.toLowerCase()}`}
                                readOnly={isReadOnly}
                                disabled={isReadOnly}
                                min={field.type === 'number' ? 0 : undefined}
                                step={field.type === 'number' ? 'any' : undefined}
                            />
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
                        </div>
                    );

                case 'fieldset':
                    return (
                        <fieldset className="mb-6 border rounded-xl p-6 bg-gray-50">
                            {field.legend && (
                                <legend className="text-lg font-medium text-gray-800 px-2">
                                    {field.legend}
                                </legend>
                            )}
                            <div className="space-y-4">
                                {field.children?.map((childField, idx) => (
                                    <div key={idx}>
                                        {renderField(childField, sectionId)}
                                    </div>
                                ))}
                            </div>
                        </fieldset>
                    );

                case 'html':
                    return (
                        <div className="mb-6 prose prose-sm max-w-none leading-relaxed text-gray-700"
                             dangerouslySetInnerHTML={{__html: field.html || ''}}/>
                    );

                case 'paragraph':
                    return (
                        <div className="mb-6">
                            <label className="block text-sm font-medium mb-2 text-gray-700">
                                {field.label}
                            </label>
                            <div className="p-4 bg-gray-50 border rounded-xl text-gray-700 text-sm">
                                {field.content}
                            </div>
                        </div>
                    );

                case 'checkbox':
                    return (
                        <div className="mb-6">
                            <label className="flex items-start space-x-3">
                                <input
                                    type="checkbox"
                                    className="mt-1 text-emerald-500 focus:ring-emerald-400 h-4 w-4 rounded"
                                    required={field.required}
                                    checked={!!fieldValue}
                                    onChange={(e) => handleInputChange(fieldId, e.target.checked, field)}
                                />
                                <span className="text-sm text-gray-700">{field.label}</span>
                            </label>
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
                        </div>
                    );

                case 'file':
                    return (
                        <div className="mb-6">
                            <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                                {field.label} {field.required && <span className="text-emerald-500">*</span>}
                            </label>
                            <div
                                className="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-emerald-300 transition-colors">
                                <div className="space-y-1 text-center">
                                    <svg
                                        className="mx-auto h-12 w-12 text-gray-400"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 48 48"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            strokeWidth={2}
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                    <div className="flex text-sm text-gray-600">
                                        <label
                                            htmlFor={fieldId}
                                            className="relative cursor-pointer bg-white rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none"
                                        >
                                            <span>Upload a file</span>
                                            <input
                                                id={fieldId}
                                                ref={el => fileInputRefs.current[fieldId] = el}
                                                name={fieldId}
                                                type="file"
                                                className="sr-only"
                                                accept={field.accept || '*/*'}
                                                onChange={(e) => {
                                                    if (e.target.files && e.target.files[0]) {
                                                        handleInputChange(fieldId, e.target.files[0], field);
                                                    }
                                                }}
                                                required={field.required}
                                            />
                                        </label>
                                        <p className="pl-1">or drag and drop</p>
                                    </div>
                                    <p className="text-xs text-gray-500">
                                        {field.accept?.includes('image')
                                            ? 'PNG, JPG, GIF up to 10MB'
                                            : 'PDF, DOC, DOCX up to 10MB'}
                                    </p>
                                    {formValues[fieldId] && (
                                        <p className="mt-2 text-sm text-emerald-600">
                                            File selected: {formValues[fieldId].name || formValues[fieldId]}
                                        </p>
                                    )}
                                </div>
                            </div>
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This file is required
                                </p>
                            )}
                        </div>
                    );

                case 'signature':
                    return (
                        <div className="mb-6">
                            <label className="block text-sm font-medium mb-2 text-gray-700">
                                {field.label} {field.required && <span className="text-emerald-500">*</span>}
                            </label>
                            <div className="border rounded-xl overflow-hidden">
                                <div className="bg-gray-50 p-2 flex justify-between items-center">
                                    <span className="text-sm text-gray-500">Sign below</span>
                                    <button
                                        type="button"
                                        className="text-xs text-emerald-600 hover:text-emerald-700"
                                        onClick={() => {
                                            if (signaturePadRef.current) {
                                                signaturePadRef.current.clear();
                                                handleInputChange(fieldId, null, field);
                                            }
                                        }}
                                    >
                                        Clear
                                    </button>
                                </div>
                                <div className="bg-white p-2">
                                    <SignaturePad
                                        ref={signaturePadRef}
                                        canvasProps={{
                                            className: 'w-full h-40 border border-gray-200',
                                        }}
                                        onEnd={() => {
                                            if (signaturePadRef.current) {
                                                const signatureData = signaturePadRef.current.toDataURL();
                                                handleInputChange(fieldId, signatureData, field);
                                            }
                                        }}
                                    />
                                </div>
                                {field.generateLink && (
                                    <div className="bg-gray-50 p-2">
                                        <button
                                            type="button"
                                            className="text-sm text-emerald-600 hover:text-emerald-700 w-full text-center"
                                            onClick={() => {
                                                const linkCode = Math.random().toString(36).substring(2, 10);
                                                alert(`A link has been generated for other directors to sign: https://example.com/sign/${linkCode}`);
                                            }}
                                        >
                                            Generate link for other directors
                                        </button>
                                    </div>
                                )}
                            </div>
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    Signature is required
                                </p>
                            )}
                        </div>
                    );

                case 'component':
                    if (field.component === 'BranchLocator') {
                        return (
                            <div className="mb-6">
                                <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                                    {field.label} {field.required && <span className="text-emerald-500">*</span>}
                                </label>
                                <BranchLocator
                                    fieldId={fieldId}
                                    onChange={(value) => handleInputChange(fieldId, value, field)}
                                    value={fieldValue}
                                    required={field.required}
                                />
                                {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                    <p className="mt-1 text-sm text-red-500 flex items-center">
                                        <AlertCircle className="w-4 h-4 mr-1"/>
                                        This field is required
                                    </p>
                                )}
                            </div>
                        );
                    }
                    return null;

                case 'date':
                    return (
                        <div className="mb-6">
                            <label className="block text-sm font-medium mb-2 text-gray-700" htmlFor={fieldId}>
                                {field.label} {field.required && <span className="text-emerald-500">*</span>}
                            </label>
                            <input
                                type="date"
                                id={fieldId}
                                className={`${baseInputStyles} ${isReadOnly ? 'bg-gray-100' : 'hover:border-emerald-300'} ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                required={field.required}
                                onChange={(e) => handleInputChange(fieldId, e.target.value, field)}
                                value={fieldValue || ''}
                                readOnly={isReadOnly}
                                disabled={isReadOnly}
                            />
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
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
                                className={`${baseInputStyles} ${isReadOnly ? 'bg-gray-100' : 'hover:border-emerald-300'} ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                required={field.required}
                                onChange={(e) => handleInputChange(fieldId, e.target.value, field)}
                                value={fieldValue || ''}
                                disabled={isReadOnly}
                            >
                                <option value="">Select {field.label}</option>
                                {field.options?.map((option, idx) => (
                                    <option key={idx} value={option}>{option}</option>
                                ))}
                            </select>
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
                        </div>
                    );

                case 'select2':
                    return (
                        <div className="mb-6">
                            <label className="block text-sm font-medium mb-2 text-gray-700">
                                {field.label} {field.required && <span className="text-emerald-500">*</span>}
                            </label>
                            <Select2
                                fieldId={fieldId}
                                options={field.options || []}
                                required={field.required}
                                onChange={(value) => handleInputChange(fieldId, value, field)}
                                value={fieldValue || ''}
                                placeholder={field.placeholder || `Select ${field.label}`}
                            />
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
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
                                        className={`flex items-center p-3 border rounded-xl transition-all duration-300 bg-white
                                            ${isReadOnly ? 'bg-gray-100' : 'hover:border-emerald-300'}
                                            ${fieldValue === option ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'}
                                            ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                    >
                                        <input
                                            type="radio"
                                            id={`${fieldId}-${idx}`}
                                            name={fieldId}
                                            value={option}
                                            required={field.required}
                                            onChange={(e) => handleInputChange(fieldId, e.target.value, field)}
                                            checked={fieldValue === option}
                                            className="mr-3 text-emerald-500 focus:ring-emerald-400 h-4 w-4"
                                            disabled={isReadOnly}
                                        />
                                        <label
                                            htmlFor={`${fieldId}-${idx}`}
                                            className={`text-gray-700 text-sm flex-1 cursor-pointer truncate ${isReadOnly ? 'cursor-not-allowed' : ''}`}
                                            title={option}
                                        >
                                            {option}
                                        </label>
                                    </div>
                                ))}
                            </div>
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
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
                                        className={`flex items-center p-3 border rounded-xl hover:border-emerald-300 transition-all duration-300 bg-white ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                    >
                                        <input
                                            type="checkbox"
                                            id={`${fieldId}-${idx}`}
                                            value={option}
                                            onChange={(e) => {
                                                const currentValues = Array.isArray(formValues[fieldId]) ? formValues[fieldId] : [];
                                                const newValues = e.target.checked
                                                    ? [...currentValues, option]
                                                    : currentValues.filter(val => val !== option);
                                                handleInputChange(fieldId, newValues, field);
                                            }}
                                            checked={Array.isArray(formValues[fieldId]) && formValues[fieldId].includes(option)}
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
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
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
                                className={`${baseInputStyles} ${isReadOnly ? 'bg-gray-100' : 'hover:border-emerald-300'} ${attemptedValidation && !fieldValidation[fieldId] && field.required ? 'border-red-300' : ''}`}
                                required={field.required}
                                onChange={(e) => handleInputChange(fieldId, e.target.value, field)}
                                value={fieldValue || ''}
                                rows={4}
                                placeholder={field.placeholder || `Enter ${field.label.toLowerCase()}`}
                                readOnly={isReadOnly}
                                disabled={isReadOnly}
                            />
                            {attemptedValidation && !fieldValidation[fieldId] && field.required && (
                                <p className="mt-1 text-sm text-red-500 flex items-center">
                                    <AlertCircle className="w-4 h-4 mr-1"/>
                                    This field is required
                                </p>
                            )}
                        </div>
                    );

                default:
                    return null;
            }
        } catch (error) {
            console.error("Error rendering field:", field.label, error);
            return (
                <div className="mb-6">
                    <p className="text-red-500">Error rendering field: {field.label}</p>
                </div>
            );
        }
    };

    const renderCurrentSection = () => {
        if (!formData) return null;

        const section = formData.sections[currentSection];
        if (!section) {
            console.error(`Section at index ${currentSection} doesn't exist`);
            return (
                <div className="p-6 text-center">
                    <p className="text-red-500">Error: Section not found</p>
                </div>
            );
        }

        if (section.dynamicSection && section.variants) {
            const variantKey = sectionVariants[section.id || ''] || Object.keys(section.variants)[0];
            const variant = section.variants[variantKey];

            if (!variant) {
                console.error(`Variant "${variantKey}" doesn't exist in section`);
                return (
                    <div className="p-6 text-center">
                        <p className="text-red-500">Error: Invalid section variant</p>
                    </div>
                );
            }

            const fields = variant.fields || [];
            return (
                <div className="space-y-4">
                    {fields.map((field, index) => (
                        <div key={`${index}-${field?.label || 'field'}`}>
                            {renderField(field, section.id || '')}
                        </div>
                    ))}
                </div>
            );
        }

        if (section.generateFromCount && section.templates?.director) {
            const directorTemplate = section.templates.director;
            if (!directorTemplate) {
                console.error("Director template not found");
                return (
                    <div className="p-6 text-center">
                        <p className="text-red-500">Error: Director template not found</p>
                    </div>
                );
            }

            const directorFields = directorTemplate.fields || [];
            const directorSections = [];

            for (let i = 0; i < directorCount; i++) {
                const fieldsWithReplacedIds = directorFields.map(field => {
                    if (!field) return null;
                    const newField = {...field};
                    if (newField.id) {
                        newField.id = newField.id.replace('{index}', (i + 1).toString());
                    }
                    return newField;
                }).filter(Boolean);

                directorSections.push(
                    <div key={`director-${i + 1}`} className="mt-8 bg-white rounded-lg p-6 border">
                        <h3 className="text-xl font-semibold mb-6 text-center">
                            {(directorTemplate.title || "Director").replace('{index}', (i + 1).toString())}
                        </h3>
                        <div className="space-y-4">
                            {fieldsWithReplacedIds.map((field, index) => (
                                <div key={`dir-${i}-field-${index}`}>
                                    {renderField(field, section.id || '')}
                                </div>
                            ))}
                        </div>
                    </div>
                );
            }

            return <div className="space-y-8">{directorSections}</div>;
        }

        const fields = section.fields || [];
        return (
            <div className="space-y-4">
                {fields.map((field, index) => (
                    <div key={index}>
                        {renderField(field, section.id || '')}
                    </div>
                ))}
            </div>
        );
    };

    const validateSection = () => {
        if (!formData) return false;

        const section = formData.sections[currentSection];
        if (!section) return false;

        const newValidation: Record<string, boolean> = {};
        let isValid = true;

        const validateField = (field: Field) => {
            // If field is not required, consider it valid
            if (!field.required) {
                newValidation[field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`] = true;
                return true;
            }
            
            // If field is read-only, consider it valid regardless of value
            if (field.readOnly) {
                newValidation[field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`] = true;
                return true;
            }
            
            // The KYC Checklist section has been removed from the SME form
            // No need to check for bank statements anymore

            const fieldId = field.id || `${field.label.toLowerCase().replace(/\s+/g, '-')}`;
            
            // Check for both direct value and bindTo value
            let value;
            if (field.bindTo && formValues[field.bindTo]) {
                value = formValues[field.bindTo];
            } else {
                value = formValues[fieldId];
            }

            let fieldValid = false;

            if (field.type === 'checkbox') {
                fieldValid = !!value;
            } else if (field.type === 'checkbox_list') {
                fieldValid = Array.isArray(value) && value.length > 0;
            } else if (field.type === 'file') {
                fieldValid = !!value;
            } else if (field.type === 'signature') {
                fieldValid = !!value;
            } else if (field.type === 'component') {
                fieldValid = !!value;
            } else {
                fieldValid = value !== undefined && value !== null && value !== '';
            }

            newValidation[fieldId] = fieldValid;
            if (!fieldValid) isValid = false;
            return fieldValid;
        };

        const validateFields = (fields: Field[]) => {
            if (!fields || !Array.isArray(fields)) return;

            fields.forEach(field => {
                if (field.type === 'fieldset' && field.children) {
                    validateFields(field.children);
                } else {
                    validateField(field);
                }
            });
        };

        if (section.dynamicSection && section.variants) {
            const variantKey = sectionVariants[section.id || ''] || Object.keys(section.variants)[0];
            const variant = section.variants[variantKey];

            if (variant && variant.fields) {
                validateFields(variant.fields);
            }
        } else if (section.generateFromCount && section.templates?.director) {
            const directorTemplate = section.templates.director;

            if (directorTemplate && directorTemplate.fields) {
                for (let i = 0; i < directorCount; i++) {
                    const directorFields = directorTemplate.fields.map(field => {
                        const newField = {...field};
                        if (newField.id) {
                            newField.id = newField.id.replace('{index}', (i + 1).toString());
                        }
                        return newField;
                    });

                    validateFields(directorFields);
                }
            }
        } else if (section.fields) {
            validateFields(section.fields);
        } else {
            isValid = true;
        }

        setFieldValidation(newValidation);
        
        // If validation fails, scroll to the first invalid field
        if (!isValid) {
            setTimeout(() => {
                const firstInvalidField = document.querySelector('.border-red-300');
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        }
        
        return isValid;
    };

    const handleNextClick = () => {
        setAttemptedValidation(true);
        const isValid = validateSection();

        if (isValid) {
            if (currentSection < (formData?.sections.length || 0) - 1) {
                setCurrentSection(prev => prev + 1);
                window.scrollTo(0, 0);
            }
        }
    };

    const handleSubmit = async () => {
        setAttemptedValidation(true);
        const isValid = validateSection();

        if (!isValid) {
            return;
        }

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
                    referral_code: localStorage.getItem('referralCode'), // Include referral code from localStorage
                }),
            });

            if (!response.ok) throw new Error('Failed to submit form');
            const {insertId} = await response.json();
            setSuccess(true);
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
                                        {currentSectionData.description || formData.description || ''}
                                    </p>
                                </div>

                                {renderCurrentSection()}
                            </div>

                            <div className="sticky bottom-0 p-6 md:p-8 bg-gray-50 border-t border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-500">
                                        Section {currentSection + 1} of {formData.sections.length}
                                    </div>

                                    {currentSection < formData.sections.length - 1 ? (
                                        <button
                                            onClick={handleNextClick}
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

            {/* Confirmation Dialog */}
            {showConfirmDialog && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-xl max-w-md w-full p-6 space-y-4">
                        <h3 className="text-lg font-semibold">Confirmation</h3>
                        <p>{confirmDialogMessage}</p>
                        <div className="flex gap-3 justify-end">
                            <button
                                className="px-4 py-2 border rounded-lg hover:bg-gray-100"
                                onClick={() => setShowConfirmDialog(false)}
                            >
                                Cancel
                            </button>
                            <button
                                className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                                onClick={() => {
                                    setShowConfirmDialog(false);
                                    confirmDialogAction();
                                }}
                            >
                                Proceed
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DynamicFormWizard;
