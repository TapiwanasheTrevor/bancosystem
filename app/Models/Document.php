<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'name',
        'path',
        'file_type',
        'size',
        'agent_id',
        'form_id',
        'user_id',
        'status',
        'processed_at',
        'processed_by',
        'notes'
    ];
    
    protected $casts = [
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'size' => 'integer'
    ];
    
    /**
     * Get the agent who uploaded this document.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    /**
     * Get the user this document belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the form this document is associated with.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
    
    /**
     * Check if this document has been processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }
}
