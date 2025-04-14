<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostBuildup extends Model
{
    protected $fillable = [
        'product_id',
        'base_cost',
        'variables',
        'final_price'
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'variables' => 'array',
        'final_price' => 'decimal:2'
    ];

    public function calculateFinalPrice(): float
    {
        $total = $this->base_cost;

        foreach ($this->variables as $variable) {
            if (isset($variable['type']) && isset($variable['value'])) {
                switch ($variable['type']) {
                    case 'fixed':
                        $total += $variable['value'];
                        break;
                    case 'percentage':
                        $total += ($this->base_cost * ($variable['value'] / 100));
                        break;
                    case 'multiplier':
                        $total *= $variable['value'];
                        break;
                }
            }
        }

        $this->final_price = $total;
        $this->save();

        return $total;
    }

    public function addVariable(string $name, string $type, float $value, string $description = ''): void
    {
        $variables = $this->variables ?? [];
        $variables[] = [
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'description' => $description
        ];

        $this->variables = $variables;
        $this->calculateFinalPrice();
    }

    public function removeVariable(string $name): void
    {
        $variables = $this->variables ?? [];
        $variables = array_filter($variables, fn($var) => $var['name'] !== $name);

        $this->variables = array_values($variables);
        $this->calculateFinalPrice();
    }

    public function updateVariable(string $name, array $newData): void
    {
        $variables = $this->variables ?? [];

        foreach ($variables as &$variable) {
            if ($variable['name'] === $name) {
                $variable = array_merge($variable, $newData);
                break;
            }
        }

        $this->variables = $variables;
        $this->calculateFinalPrice();
    }

    public function getVariableTotal(string $type = null): float
    {
        $total = 0;
        $variables = $this->variables ?? [];

        foreach ($variables as $variable) {
            if (!$type || $variable['type'] === $type) {
                switch ($variable['type']) {
                    case 'fixed':
                        $total += $variable['value'];
                        break;
                    case 'percentage':
                        $total += ($this->base_cost * ($variable['value'] / 100));
                        break;
                }
            }
        }

        return $total;
    }
}
