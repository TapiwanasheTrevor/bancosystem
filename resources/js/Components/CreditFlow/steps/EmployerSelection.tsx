import React, { useState, useEffect } from 'react';
import { Building2, X, Search, Briefcase } from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { EmployerSelectionProps } from '../types';
import parastatalsData from '../data/parastatals.json';
import corporationsData from '../data/corporations.json';

// Types for selection options
type Parastatal = {
  name: string;
  acronym: string;
};

type Corporation = {
  name: string;
  listed: boolean;
  exchange: string | null;
};

// Type for modal type
type ModalType = 'parastatal' | 'corporate' | null;

const EmployerSelection: React.FC<EmployerSelectionProps> = ({
  onNext,
  onBack,
  selectedEmployer = ''
}) => {
  const [activeModal, setActiveModal] = useState<ModalType>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedParastatal, setSelectedParastatal] = useState<Parastatal | null>(null);
  const [selectedCorporation, setSelectedCorporation] = useState<Corporation | null>(null);
  
  // Sort parastatals alphabetically by name
  const sortedParastatals = [...parastatalsData.zimbabwean_parastatals].sort((a, b) => 
    a.name.localeCompare(b.name)
  );
  
  // Sort corporations alphabetically by name
  const sortedCorporations = [...corporationsData.zimbabwean_large_corporations].sort((a, b) => 
    a.name.localeCompare(b.name)
  );
  
  // Filter parastatals based on search query
  const filteredParastatals = sortedParastatals.filter(parastatal => 
    parastatal.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
    parastatal.acronym.toLowerCase().includes(searchQuery.toLowerCase())
  );

  // Filter corporations based on search query
  const filteredCorporations = sortedCorporations.filter(corporation => 
    corporation.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const employers = [
    {id: 'Government of Zimbabwe', form: 'ssb'},
    {id: 'GOZ - ZAPPA', form: 'zappa'},
    {id: 'GOZ - Pension', form: 'pension'},
    {id: 'Town Council', form: 'check-account'},
    {id: 'Parastatal', form: 'check-account'},
    {id: 'Mission and Private Schools', form: 'check-account'},
    {id: 'I am an Entrepreneur', form: 'sme'},
    {id: 'Large Corporate', form: 'corporate'},
    {id: 'Other', form: 'other'}
  ];

  // Reset search query when modal changes
  useEffect(() => {
    setSearchQuery('');
  }, [activeModal]);

  // Handle selection of a predefined employer
  const handleEmployerSelect = (employer: { id: string; form: string }) => {
    if (employer.id === 'Parastatal') {
      setActiveModal('parastatal');
    } else if (employer.id === 'Large Corporate') {
      setActiveModal('corporate');
    } else {
      // For other predefined employers, pass the full object
      onNext(employer);
    }
  };

  // Handle selection of a specific parastatal from the modal
  const handleParastatalSelect = (parastatal: Parastatal) => {
    setSelectedParastatal(parastatal);
    setActiveModal(null);
    // Format: "Parastatal - Full Name (Acronym)"
    const parastatalEmployerId = `Parastatal - ${parastatal.name} (${parastatal.acronym})`;
    // Pass an object with id and form properties
    onNext({ id: parastatalEmployerId, form: 'check-account' });
  };

  // Handle selection of a specific corporation from the modal
  const handleCorporationSelect = (corporation: Corporation) => {
    setSelectedCorporation(corporation);
    setActiveModal(null);
    // Format: "Corporate - Full Name (Listed: Yes/No)"
    const listedStatus = corporation.listed ? "Listed" : "Unlisted";
    const exchangeInfo = corporation.exchange ? ` on ${corporation.exchange}` : "";
    const corporateEmployerId = `Corporate - ${corporation.name} (${listedStatus}${exchangeInfo})`;
    // Pass an object with id and form properties
    onNext({ id: corporateEmployerId, form: 'check-account' });
  };

  // Render selection modal
  const renderModal = () => {
    if (!activeModal) return null;

    return (
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
          <div className="p-4 border-b flex justify-between items-center">
            <h2 className="text-xl font-bold text-gray-800">
              {activeModal === 'parastatal' ? 'Select Your Parastatal' : 'Select Your Corporation'}
            </h2>
            <button 
              onClick={() => setActiveModal(null)}
              className="text-gray-500 hover:text-gray-700"
            >
              <X size={24} />
            </button>
          </div>
          
          <div className="p-4 border-b">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search size={18} className="text-gray-400" />
              </div>
              <input
                type="text"
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder={activeModal === 'parastatal' ? "Search parastatals..." : "Search corporations..."}
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
          
          <div className="overflow-y-auto flex-grow">
            <ul className="divide-y divide-gray-200">
              {activeModal === 'parastatal' && filteredParastatals.map((parastatal, index) => (
                <li key={index}>
                  <button
                    onClick={() => handleParastatalSelect(parastatal)}
                    className="w-full text-left px-4 py-3 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors"
                  >
                    <div className="font-medium text-gray-900">{parastatal.name}</div>
                    <div className="text-sm text-gray-500">{parastatal.acronym}</div>
                  </button>
                </li>
              ))}
              
              {activeModal === 'corporate' && filteredCorporations.map((corporation, index) => (
                <li key={index}>
                  <button
                    onClick={() => handleCorporationSelect(corporation)}
                    className="w-full text-left px-4 py-3 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors"
                  >
                    <div className="font-medium text-gray-900">{corporation.name}</div>
                    <div className="text-sm text-gray-500">
                      {corporation.listed 
                        ? `Listed on ${corporation.exchange}`
                        : "Unlisted"
                      }
                    </div>
                  </button>
                </li>
              ))}
              
              {((activeModal === 'parastatal' && filteredParastatals.length === 0) ||
                (activeModal === 'corporate' && filteredCorporations.length === 0)) && (
                <li className="px-4 py-3 text-gray-500 text-center">
                  No {activeModal === 'parastatal' ? 'parastatals' : 'corporations'} match your search
                </li>
              )}
            </ul>
          </div>
          
          <div className="p-4 border-t">
            <div className="flex justify-end">
              <button
                onClick={() => setActiveModal(null)}
                className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  };

  // Check if selected employer is a parastatal or corporate
  const isParastatalEmployer = selectedEmployer?.startsWith('Parastatal - ');
  const isCorporateEmployer = selectedEmployer?.startsWith('Corporate - ');

  return (
    <StepContainer
      title="Choose Your Income Source"
      subtitle="This helps us tailor the best credit options for you"
      showBackButton
      onBack={onBack}
    >
      <div className="p-6 md:p-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {employers.map((employer) => {
            // Check employer type
            if (employer.id === 'Parastatal') {
              // Use the selected parastatal value if available, otherwise use generic "Parastatal"
              const displayText = isParastatalEmployer ? selectedEmployer : employer.id;
              
              return (
                <Button
                  key={employer.id}
                  onClick={() => handleEmployerSelect(employer)}
                  icon={Building2}
                  variant={isParastatalEmployer ? 'primary' : 'default'}
                  fullWidth
                >
                  {displayText}
                </Button>
              );
            } else if (employer.id === 'Large Corporate') {
              // Use the selected corporation value if available, otherwise use generic "Large Corporate"
              const displayText = isCorporateEmployer ? selectedEmployer : employer.id;
              
              return (
                <Button
                  key={employer.id}
                  onClick={() => handleEmployerSelect(employer)}
                  icon={Briefcase}
                  variant={isCorporateEmployer ? 'primary' : 'default'}
                  fullWidth
                >
                  {displayText}
                </Button>
              );
            } else {
              // Normal buttons for other employers
              return (
                <Button
                  key={employer.id}
                  onClick={() => handleEmployerSelect(employer)}
                  icon={Building2}
                  variant={selectedEmployer === employer.id ? 'primary' : 'default'}
                  fullWidth
                >
                  {employer.id}
                </Button>
              );
            }
          })}
        </div>
      </div>
      
      {renderModal()}
    </StepContainer>
  );
};

export default EmployerSelection;