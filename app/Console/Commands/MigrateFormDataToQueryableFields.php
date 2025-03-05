<?php

namespace App\Console\Commands;

use App\Models\Form;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateFormDataToQueryableFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:migrate-data {--force : Run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract data from JSON fields and save to queryable columns for existing form submissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalForms = Form::count();

        if ($totalForms === 0) {
            $this->info('No forms to migrate.');
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("This will process {$totalForms} form submissions. Continue?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->output->progressStart($totalForms);
        $processedForms = 0;
        $errorForms = 0;

        Form::chunk(100, function ($forms) use (&$processedForms, &$errorForms) {
            foreach ($forms as $form) {
                try {
                    // Skip forms that already have data in the queryable fields
                    if ($form->applicant_name && $form->applicant_id_number) {
                        $this->output->progressAdvance();
                        $processedForms++;
                        continue;
                    }

                    $formValues = json_decode($form->form_values, true) ?? [];
                    $questionnaireData = json_decode($form->questionnaire_data, true) ?? [];

                    // Extract applicant details
                    $firstName = $formValues['first-name'] ?? $formValues['forename'] ?? $formValues['forenames'] ?? 
                              $formValues['customerFirstName'] ?? '';
                    $surname = $formValues['surname'] ?? $formValues['last-name'] ?? $formValues['customerSurname'] ?? '';
                    $form->applicant_name = trim($firstName . ' ' . $surname);
                    
                    // Extract ID number
                    $form->applicant_id_number = $formValues['id-number'] ?? $formValues['national-id'] ?? 
                                               $formValues['identity-number'] ?? $formValues['customerIdNumber'] ?? 
                                               $formValues['idNumber'] ?? '';
                    
                    // Extract phone number
                    $form->applicant_phone = $formValues['cell-number'] ?? $formValues['phone-number'] ?? 
                                           $formValues['phone'] ?? $formValues['mobile'] ?? 
                                           $formValues['customerCellNumber'] ?? '';
                    
                    // Extract email
                    $form->applicant_email = $formValues['email-address'] ?? $formValues['email'] ?? 
                                           $formValues['customerEmail'] ?? '';
                    
                    // Extract employer
                    $form->employer = $formValues['employer-name'] ?? $formValues['employer'] ?? 
                                    $formValues['customerEmployer'] ?? $questionnaireData['employer'] ?? '';
                    
                    // Extract loan details if available
                    if (isset($questionnaireData['selectedProduct'])) {
                        $selectedProduct = $questionnaireData['selectedProduct'];
                        
                        if (isset($selectedProduct['selectedCreditOption'])) {
                            $creditOption = $selectedProduct['selectedCreditOption'];
                            
                            // Extract loan amount
                            if (isset($creditOption['final_price'])) {
                                $form->loan_amount = (float) $creditOption['final_price'];
                            } elseif (isset($formValues['loan-amount'])) {
                                $form->loan_amount = (float) $formValues['loan-amount'];
                            } elseif (isset($formValues['applied-amount'])) {
                                $form->loan_amount = (float) $formValues['applied-amount'];
                            }
                            
                            // Extract loan term
                            if (isset($creditOption['months'])) {
                                $form->loan_term_months = (int) $creditOption['months'];
                            }
                            
                            // Extract loan dates
                            if (isset($selectedProduct['loanStartDate'])) {
                                $form->loan_start_date = $selectedProduct['loanStartDate'];
                            }
                            
                            if (isset($selectedProduct['loanEndDate'])) {
                                $form->loan_end_date = $selectedProduct['loanEndDate'];
                            }
                        }
                    }
                    
                    $form->save();
                    $processedForms++;
                } catch (\Exception $e) {
                    Log::error("Error migrating form {$form->id}: " . $e->getMessage());
                    $errorForms++;
                }

                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
        $this->info("Migration complete. Processed {$processedForms} forms with {$errorForms} errors.");

        return 0;
    }
}