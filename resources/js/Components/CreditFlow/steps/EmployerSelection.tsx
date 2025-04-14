import React from 'react';
import {Building2} from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import {EmployerSelectionProps} from '../types';

const EmployerSelection: React.FC<EmployerSelectionProps> = ({
                                                                 onNext,
                                                                 onBack,
                                                                 selectedEmployer = ''
                                                             }) => {
    const employers = [
        {id: 'GOZ (Government of Zimbabwe) - SSB', form: 'ssb'},
        {id: 'GOZ - ZAPPA', form: 'zappa'},
        {id: 'GOZ - Pension', form: 'pension'},
        {id: 'Town Council', form: 'check-account'},
        {id: 'Parastatal', form: 'check-account'},
        {id: 'Mission and Private Schools', form: 'check-account'},
        {id: 'SME (Small & Medium Enterprises)', form: 'sme'}
    ];

    const handleEmployerSelect = (employer: string) => {
        onNext(employer);
    };

    return (
        <StepContainer
            title="Choose Your Income Source"
            subtitle="This helps us tailor the best credit options for you"
            showBackButton
            onBack={onBack}
        >
            <div className="p-6 md:p-8">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {employers.map((employer) => (
                        <Button
                            key={employer.id}
                            onClick={() => handleEmployerSelect(employer.id)}
                            icon={Building2}
                            variant={selectedEmployer === employer.id ? 'primary' : 'default'}
                            fullWidth
                        >
                            {employer.id}
                        </Button>
                    ))}
                </div>
            </div>
        </StepContainer>
    );
};

export default EmployerSelection;
