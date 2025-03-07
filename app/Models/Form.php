<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'form_values' => 'array',
        'questionnaire_data' => 'array',
        'uploaded_files' => 'array',
        'loan_amount' => 'decimal:2',
        'loan_term_months' => 'integer',
        'loan_start_date' => 'date',
        'loan_end_date' => 'date',
    ];
    protected $fillable = [
        'form_values',
        'questionnaire_data',
        'uploaded_files',
        'uuid',
        'status',
        'form_name',
        'agent_id',
        'user_id',
        'referred_by',
        'applicant_name',
        'applicant_id_number',
        'applicant_phone',
        'applicant_email',
        'employer',
        'loan_amount',
        'loan_term_months',
        'loan_start_date',
        'loan_end_date'
    ];
    
    /**
     * Get the user who submitted this form
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the agent who is assigned to this form
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    /**
     * Get the agent who referred this form
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
    
    /**
     * Get the documents associated with this form
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    
    /**
     * Get the product deliveries associated with this form
     */
    public function productDeliveries(): HasMany
    {
        return $this->hasMany(ProductDelivery::class);
    }
    
    /**
     * Get the purchase orders associated with this form
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
    
    /**
     * Get the commissions associated with this form
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
    
    /**
     * Get the status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'processing' => 'blue',
            'active' => 'green',
            'completed' => 'purple',
            default => 'gray'
        };
    }
    
    /**
     * Check if all required documents are uploaded
     */
    public function hasRequiredDocuments(): bool
    {
        // This needs to be customized based on your form types and required docs
        $requiredDocTypes = $this->getRequiredDocumentTypes();
        $uploadedDocTypes = $this->documents()->pluck('document_type')->toArray();
        
        return count(array_diff($requiredDocTypes, $uploadedDocTypes)) === 0;
    }
    
    /**
     * Get required document types based on form type
     */
    private function getRequiredDocumentTypes(): array
    {
        // This logic should be customized based on your form types
        return match($this->form_name) {
            'individual_account_opening' => ['id_document', 'passport_photo', 'proof_of_residence'],
            'smes_business_account_opening' => ['business_registration', 'id_document', 'proof_of_address'],
            'account_holder_loan_application' => ['id_document', 'payslip', 'bank_statement'],
            'pensioners_loan_account' => ['id_document', 'pension_certificate', 'proof_of_address'],
            'ssb_account_opening_form' => ['id_document', 'proof_of_residence'],
            default => ['id_document']
        };
    }
}
