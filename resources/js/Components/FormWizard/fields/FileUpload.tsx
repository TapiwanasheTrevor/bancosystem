import React, { useRef, useState } from 'react';
import { AlertCircle, Upload, Check, X } from 'lucide-react';

interface FileUploadProps {
  fieldId: string;
  label: string;
  required: boolean;
  onChange: (value: File | null) => void;
  value: File | null;
  accept: string;
  isInvalid: boolean;
}

const FileUpload: React.FC<FileUploadProps> = ({
  fieldId,
  label,
  required,
  onChange,
  value,
  accept,
  isInvalid
}) => {
  const [fileName, setFileName] = useState<string>('');
  const [isUploading, setIsUploading] = useState<boolean>(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) {
      onChange(null);
      setFileName('');
      return;
    }

    const file = files[0];
    setFileName(file.name);
    
    // Simulate upload process for better UX
    setIsUploading(true);
    setTimeout(() => {
      setIsUploading(false);
      onChange(file);
    }, 500);
  };

  const clearFile = () => {
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
    onChange(null);
    setFileName('');
  };

  return (
    <div className="mb-4">
      <label className="block text-sm font-medium mb-2 text-gray-700">
        {label} {required && <span className="text-emerald-500">*</span>}
      </label>

      <div className={`border-2 border-dashed rounded-md p-4 ${isInvalid ? 'border-red-300' : 'border-gray-300'}`}>
        {!fileName ? (
          <div 
            className="flex flex-col items-center justify-center py-3 cursor-pointer"
            onClick={() => fileInputRef.current?.click()}
          >
            <Upload className="h-8 w-8 text-gray-400 mb-2" />
            <p className="text-sm text-gray-500">
              <span className="text-emerald-500 font-medium">Click to upload</span> or drag and drop
            </p>
            <p className="text-xs text-gray-500 mt-1">
              {accept ? `Accepted formats: ${accept}` : 'All file types supported'}
            </p>
          </div>
        ) : (
          <div className="flex items-center justify-between p-2">
            <div className="flex items-center">
              {isUploading ? (
                <div className="animate-pulse h-5 w-5 bg-emerald-100 rounded-full mr-3"></div>
              ) : (
                <Check className="h-5 w-5 text-emerald-500 mr-3" />
              )}
              <span className="text-sm truncate max-w-[200px]">{fileName}</span>
            </div>
            <button 
              type="button"
              onClick={clearFile}
              className="p-1 rounded-full hover:bg-gray-100"
            >
              <X className="h-4 w-4 text-gray-400" />
            </button>
          </div>
        )}

        <input
          ref={fileInputRef}
          type="file"
          id={fieldId}
          accept={accept}
          className="hidden"
          onChange={handleFileChange}
          required={required}
        />
      </div>

      {isInvalid && (
        <div className="text-red-500 text-sm mt-1 flex items-center">
          <AlertCircle size={14} className="mr-1" />
          Please upload a file
        </div>
      )}
    </div>
  );
};

export default FileUpload;