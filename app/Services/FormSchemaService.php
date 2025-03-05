<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FormSchemaService
{
    /**
     * Get a form schema by type with caching for performance
     *
     * @param string $formType
     * @return array
     * @throws Exception
     */
    public function getFormSchema(string $formType): array
    {
        return Cache::remember("form_schema_{$formType}", 3600, function () use ($formType) {
            $path = resource_path("forms/{$formType}.json");
            
            if (!file_exists($path)) {
                Log::error("Form schema not found: {$formType}");
                throw new Exception("Form schema not found: {$formType}");
            }
            
            $schema = json_decode(file_get_contents($path), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Invalid JSON in form schema: {$formType}", ['error' => json_last_error_msg()]);
                throw new Exception("Invalid JSON in form schema: {$formType}");
            }
            
            return $schema;
        });
    }
    
    /**
     * Get all available form types
     * 
     * @return array
     */
    public function getAllFormTypes(): array
    {
        return Cache::remember('available_form_types', 3600, function () {
            $formFiles = glob(resource_path('forms/*.json'));
            return array_map(function($file) {
                return pathinfo($file, PATHINFO_FILENAME);
            }, $formFiles);
        });
    }
    
    /**
     * Get form metadata (title, description) for all forms
     * 
     * @return array
     */
    public function getFormMetadata(): array
    {
        return Cache::remember('form_metadata', 3600, function () {
            $forms = $this->getAllFormTypes();
            $metadata = [];
            
            foreach ($forms as $formType) {
                try {
                    $schema = $this->getFormSchema($formType);
                    $metadata[$formType] = [
                        'title' => $schema['form']['title'] ?? ucfirst(str_replace('_', ' ', $formType)),
                        'description' => $schema['form']['description'] ?? '',
                        'type' => $schema['form']['type'] ?? 'general'
                    ];
                } catch (Exception $e) {
                    Log::warning("Could not get metadata for form: {$formType}", ['error' => $e->getMessage()]);
                }
            }
            
            return $metadata;
        });
    }
    
    /**
     * Clear the form schema cache
     * 
     * @param string|null $formType
     * @return void
     */
    public function clearCache(?string $formType = null): void
    {
        if ($formType) {
            Cache::forget("form_schema_{$formType}");
        } else {
            Cache::forget('available_form_types');
            Cache::forget('form_metadata');
            
            foreach ($this->getAllFormTypes() as $type) {
                Cache::forget("form_schema_{$type}");
            }
        }
    }
}