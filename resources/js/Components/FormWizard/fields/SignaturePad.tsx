import React, { useRef, useState, useEffect } from 'react';
import SignatureCanvas from 'react-signature-canvas';
import { RefreshCw } from 'lucide-react';

interface SignaturePadProps {
  fieldId: string;
  onChange: (dataUrl: string) => void;
  onClear: () => void;
  value?: string;
  required?: boolean;
  label?: string;
  error?: string;
}

/**
 * Signature pad component for capturing signatures
 */
const SignaturePad: React.FC<SignaturePadProps> = ({
  fieldId,
  onChange,
  onClear,
  value,
  required = false,
  label,
  error
}) => {
  const signaturePadRef = useRef<SignatureCanvas>(null);
  const [isEmpty, setIsEmpty] = useState(true);
  
  // When value changes externally, update the signature pad
  useEffect(() => {
    if (signaturePadRef.current && value) {
      // Clear first to avoid overlapping
      signaturePadRef.current.clear();
      
      // Create a temporary image to draw on the canvas
      const img = new Image();
      img.onload = () => {
        const canvas = signaturePadRef.current?.getCanvas();
        if (canvas) {
          const ctx = canvas.getContext('2d');
          if (ctx) {
            ctx.drawImage(img, 0, 0);
            setIsEmpty(false);
          }
        }
      };
      img.src = value;
    }
  }, [value]);
  
  // Handle signature end (when user stops drawing)
  const handleSignatureEnd = () => {
    if (signaturePadRef.current) {
      const isEmpty = signaturePadRef.current.isEmpty();
      setIsEmpty(isEmpty);
      
      if (!isEmpty) {
        const dataUrl = signaturePadRef.current.toDataURL();
        onChange(dataUrl);
      }
    }
  };
  
  // Handle clearing the signature
  const handleClear = () => {
    if (signaturePadRef.current) {
      signaturePadRef.current.clear();
      setIsEmpty(true);
      onClear();
    }
  };
  
  return (
    <div className="w-full">
      {label && (
        <label htmlFor={fieldId} className="block text-sm font-medium text-gray-700 mb-2">
          {label} {required && <span className="text-red-500">*</span>}
        </label>
      )}
      
      <div 
        className={`border-2 rounded-lg overflow-hidden
          ${error ? 'border-red-300' : 'border-gray-200'}`
        }
      >
        <div className="bg-gray-50 py-2 px-4 border-b border-gray-200 flex justify-between items-center">
          <div className="text-sm text-gray-500">
            {isEmpty ? 'Sign here' : 'Signature captured'}
          </div>
          <button
            type="button"
            onClick={handleClear}
            className="text-gray-500 hover:text-gray-700 flex items-center text-sm"
            disabled={isEmpty}
          >
            <RefreshCw className="w-4 h-4 mr-1" />
            Clear
          </button>
        </div>
        
        <div className="bg-white">
          <SignatureCanvas
            ref={signaturePadRef}
            canvasProps={{
              id: fieldId,
              className: 'w-full h-48',
              style: { width: '100%', height: '192px' }
            }}
            onEnd={handleSignatureEnd}
          />
        </div>
      </div>
      
      {error && (
        <p className="mt-1 text-sm text-red-600">{error}</p>
      )}
    </div>
  );
};

export default SignaturePad;