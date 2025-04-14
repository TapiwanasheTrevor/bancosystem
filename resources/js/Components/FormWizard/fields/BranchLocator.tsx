import React, { useState, useRef, useEffect } from 'react';
import { Search } from 'lucide-react';
import { BranchInfo } from '../types';

interface BranchLocatorProps {
  fieldId: string;
  onChange: (value: BranchInfo | null) => void;
  value: BranchInfo | string | null;
  required?: boolean;
  placeholder?: string;
}

/**
 * Branch locator field for selecting bank branches
 */
const BranchLocator: React.FC<BranchLocatorProps> = ({
  fieldId,
  onChange,
  value,
  required = false,
  placeholder = 'Search for a branch'
}) => {
  // List of available branches
  const branches = [
    "21 Natal Branch", "Avondale Branch", "Beitbridge Branch", "Bindura Branch",
    "Chinhoyi Branch", "Chiredzi Branch", "Chisipite Branch", "Douglas Road Branch",
    "Fife Street Branch", "Graniteside Branch", "Gutu Branch", "Gwanda Branch",
    "Gweru Branch", "Hwange Branch", "Jason Moyo Branch", "Kadoma Branch",
    "Kariba Branch", "Karoi Branch", "Kwekwe Branch", "Long Chen Branch",
    "Masvingo Branch", "Msasa Branch", "Mt Darwin Branch", "Murombedzi Branch",
    "Mutare Branch", "Ngezi Branch", "Nyanga Branch", "Plumtree Branch",
    "Rotten Row Branch", "Rusape Branch", "Shurugwi Branch", "Triangle Branch",
    "Victoria Falls Branch", "Westend Branch", "Zvishavane Branch"
  ];

  // Parse the initial value
  const initialValue = typeof value === 'string' ? { name: value, code: '' } : value;
  
  // State
  const [query, setQuery] = useState('');
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const [selectedBranch, setSelectedBranch] = useState<string>(initialValue?.name || '');
  const [branchCode, setBranchCode] = useState<string>(initialValue?.code || '');
  
  // Refs
  const dropdownRef = useRef<HTMLDivElement>(null);
  
  // Handle outside clicks to close dropdown
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setDropdownOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);
  
  // Filter branches based on search query
  const filteredBranches = branches.filter(branch =>
    branch.toLowerCase().includes(query.toLowerCase())
  );
  
  // Handle branch selection
  const handleSelect = (branch: string) => {
    setSelectedBranch(branch);
    // Generate a random branch code
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
      <div className="flex items-center border rounded-md shadow-sm focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-emerald-500">
        <div className="pl-3 text-gray-400">
          <Search size={18} />
        </div>
        <input
          type="text"
          id={fieldId}
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
          placeholder={placeholder}
          className="w-full p-3 outline-none rounded-md"
          required={required}
        />
      </div>
      
      {dropdownOpen && (
        <div className="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-64 overflow-y-auto">
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
        <div className="text-sm text-gray-600 mt-1">
          Branch code: <span className="font-medium">{branchCode}</span>
        </div>
      )}
    </div>
  );
};

export default BranchLocator;