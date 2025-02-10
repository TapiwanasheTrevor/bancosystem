import React, {useState, useRef} from 'react';
import SignatureCanvas from 'react-signature-canvas';
import Lottie from 'react-lottie';
import uploadAnimation from './uploadAnimation.json';
import {Upload, FileCheck, AlertCircle, ArrowLeft, PenTool, Save, RefreshCw} from 'lucide-react';

interface KYCUploadProps {
    onComplete: (data: KYCData) => void;
    onBack: () => void;
    insertId: string;
}

interface KYCData {
    idDocument: File | null;
    passportPhoto: File | null;
    payslip: File | null;
    signature: string | null;
}

interface DocumentUpload {
    file: File | null;
    preview: string | null;
    uploading: boolean;
    error: string | null;
}

const KYCUpload: React.FC<KYCUploadProps> = ({onComplete, onBack, insertId}) => {
    const [step, setStep] = useState<'documents' | 'signature'>('documents');
    const [idDocument, setIdDocument] = useState<DocumentUpload>({
        file: null,
        preview: null,
        uploading: false,
        error: null
    });
    const [passportPhoto, setPassportPhoto] = useState<DocumentUpload>({
        file: null,
        preview: null,
        uploading: false,
        error: null
    });
    const [payslip, setPayslip] = useState<DocumentUpload>({
        file: null,
        preview: null,
        uploading: false,
        error: null
    });
    const [signature, setSignature] = useState<string | null>(null);
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const signaturePadRef = useRef<SignatureCanvas>(null);

    const handleFileUpload = async (
        file: File,
        type: 'idDocument' | 'passportPhoto' | 'payslip',
        setState: React.Dispatch<React.SetStateAction<DocumentUpload>>
    ) => {
        setState(prev => ({...prev, uploading: true, error: null}));

        // Create file preview
        const reader = new FileReader();
        reader.onloadend = () => {
            setState(prev => ({...prev, preview: reader.result as string}));
        };
        reader.readAsDataURL(file);

        try {
            // Simulate file upload - replace with your actual upload logic
            await new Promise(resolve => setTimeout(resolve, 1000));

            setState(prev => ({
                ...prev,
                file,
                uploading: false
            }));
        } catch (error) {
            setState(prev => ({
                ...prev,
                error: 'Failed to upload file. Please try again.',
                uploading: false
            }));
        }
    };

    const handleSubmit = async () => {
        if (step === 'documents') {
            if (!idDocument.file || !passportPhoto.file || !payslip.file) {
                return;
            }
            setStep('signature');
        } else {
            if (!signature) {
                return;
            }

            setUploading(true);
            setUploadError(null);

            try {
                const formData = new FormData();
                formData.append('insertId', insertId);
                if (idDocument.file) formData.append('idDocument', idDocument.file);
                if (passportPhoto.file) formData.append('passportPhoto', passportPhoto.file);
                if (payslip.file) formData.append('payslip', payslip.file);
                if (signature) formData.append('signature', signature);

                const response = await fetch('/api/upload-kyc', {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) throw new Error('Failed to upload KYC documents');

                onComplete({
                    idDocument: idDocument.file,
                    passportPhoto: passportPhoto.file,
                    payslip: payslip.file,
                    signature
                });
            } catch (error) {
                console.error('Error uploading KYC documents:', error);
                setUploadError('Failed to upload KYC documents. Please try again.');
            } finally {
                setUploading(false);
            }
        }
    };

    const clearSignature = () => {
        if (signaturePadRef.current) {
            signaturePadRef.current.clear();
            setSignature(null);
        }
    };

    const saveSignature = () => {
        if (signaturePadRef.current) {
            const signatureData = signaturePadRef.current.toDataURL();
            setSignature(signatureData);
        }
    };

    const renderStepIndicator = () => (
        <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
            <div
                className="h-full bg-gradient-to-r from-emerald-500 to-orange-400 transition-all duration-500"
                style={{width: step === 'documents' ? '50%' : '100%'}}
            />
        </div>
    );

    const DocumentUploadButton = ({
                                      label,
                                      accept,
                                      state,
                                      onChange
                                  }: {
        label: string;
        accept: string;
        state: DocumentUpload;
        onChange: (file: File) => void;
    }) => (
        <div className="space-y-2">
            <p className="text-sm font-medium text-gray-700">{label}</p>
            {!state.file ? (
                <label className="block w-full">
                    <div
                        className="w-full h-32 border-2 border-dashed border-gray-300 rounded-xl hover:border-emerald-500 transition-colors cursor-pointer flex flex-col items-center justify-center p-4 hover:bg-emerald-50">
                        <Upload className="w-8 h-8 text-gray-400 mb-2"/>
                        <span className="text-sm text-gray-500">Click to upload</span>
                    </div>
                    <input
                        type="file"
                        className="hidden"
                        accept={accept}
                        onChange={(e) => {
                            const file = e.target.files?.[0];
                            if (file) {
                                onChange(file);
                            }
                        }}
                    />
                </label>
            ) : (
                <div className="relative w-full h-32 rounded-xl overflow-hidden group">
                    {state.preview && (
                        <img
                            src={state.preview}
                            alt="Preview"
                            className="w-full h-full object-cover"
                        />
                    )}
                    <div
                        className="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button
                            onClick={() => onChange(state.file as File)}
                            className="bg-white text-gray-800 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-100"
                        >
                            Replace
                        </button>
                    </div>
                    <div className="absolute top-2 right-2">
                        <FileCheck className="w-6 h-6 text-emerald-500"/>
                    </div>
                </div>
            )}
            {state.error && (
                <div className="flex items-center text-red-500 text-sm mt-1">
                    <AlertCircle className="w-4 h-4 mr-1"/>
                    {state.error}
                </div>
            )}
        </div>
    );

    const defaultOptions = {
        loop: true,
        autoplay: true,
        animationData: uploadAnimation,
        rendererSettings: {
            preserveAspectRatio: 'xMidYMid slice'
        }
    };

    return (
        <div
            className="fixed inset-0 bg-gradient-to-b from-emerald-50 to-orange-50 flex items-center justify-center p-4 md:p-6">
            <div className="max-w-4xl w-full space-y-6">
                <button
                    onClick={onBack}
                    className="flex items-center text-emerald-600 hover:text-emerald-700 transition-colors"
                >
                    <ArrowLeft className="w-4 h-4 mr-2"/>
                    Back
                </button>

                {renderStepIndicator()}

                <div className="bg-white rounded-2xl shadow-lg p-6 md:p-8 space-y-6">
                    {uploading ? (
                        <div className="flex flex-col items-center justify-center">
                            <Lottie options={defaultOptions} height={200} width={200}/>
                            <p className="text-gray-600">Uploading documents...</p>
                        </div>
                    ) : (
                        <>
                            <div className="text-center space-y-2">
                                <h2 className="text-2xl font-semibold text-gray-800">
                                    {step === 'documents' ? 'Upload Required Documents' : 'Sign Your Application'}
                                </h2>
                                <p className="text-gray-600">
                                    {step === 'documents'
                                        ? 'Please upload clear, readable copies of the following documents'
                                        : 'Please sign below using your mouse or finger'}
                                </p>
                            </div>

                            {step === 'documents' ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="md:col-span-2">
                                        <DocumentUploadButton
                                            label="National ID / Passport"
                                            accept="image/*,.pdf"
                                            state={idDocument}
                                            onChange={(file) => handleFileUpload(file, 'idDocument', setIdDocument)}
                                        />
                                    </div>
                                    <DocumentUploadButton
                                        label="Passport Sized Photo"
                                        accept="image/*"
                                        state={passportPhoto}
                                        onChange={(file) => handleFileUpload(file, 'passportPhoto', setPassportPhoto)}
                                    />
                                    <DocumentUploadButton
                                        label="Latest Payslip"
                                        accept="image/*,.pdf"
                                        state={payslip}
                                        onChange={(file) => handleFileUpload(file, 'payslip', setPayslip)}
                                    />
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    <div className="border-2 border-gray-200 rounded-xl p-4">
                                        <SignatureCanvas
                                            ref={signaturePadRef}
                                            canvasProps={{
                                                className: 'w-full h-64 border rounded-lg cursor-crosshair',
                                                style: {width: '100%', height: '256px'}
                                            }}
                                            onEnd={saveSignature}
                                        />
                                    </div>
                                    <div className="flex justify-center gap-4">
                                        <button
                                            onClick={clearSignature}
                                            className="flex items-center px-4 py-2 text-gray-600 hover:text-gray-800"
                                        >
                                            <RefreshCw className="w-4 h-4 mr-2"/>
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            )}

                            {uploadError && (
                                <div className="text-red-500 text-sm mt-2 text-center">
                                    {uploadError}
                                </div>
                            )}

                            <div className="flex justify-end pt-6">
                                <button
                                    onClick={handleSubmit}
                                    disabled={step === 'documents' ? (!idDocument.file || !passportPhoto.file || !payslip.file) : !signature}
                                    className={`flex items-center px-6 py-3 rounded-xl text-white transition-all
                    ${step === 'documents' && (!idDocument.file || !passportPhoto.file || !payslip.file) ||
                                    (step === 'signature' && !signature)
                                        ? 'bg-gray-300 cursor-not-allowed'
                                        : 'bg-gradient-to-r from-emerald-500 to-orange-400 hover:from-emerald-600 hover:to-orange-500'
                                    }`}
                                >
                                    {step === 'documents' ? (
                                        <>
                                            Next
                                            <PenTool className="w-4 h-4 ml-2"/>
                                        </>
                                    ) : (
                                        <>
                                            Complete Application
                                            <Save className="w-4 h-4 ml-2"/>
                                        </>
                                    )}
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default KYCUpload;
