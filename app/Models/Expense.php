<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    // Expense categories
    public const CATEGORIES = [
        'server' => 'Server',
        'email_server' => 'Email Server',
        'sms_server' => 'SMS Server',
        'security_server' => 'Security Server',
        'marketing' => 'Marketing',
        'development' => 'Development',
        'data_mining' => 'Data Mining',
        'data_cleaning' => 'Data Cleaning',
        'salary_marketing' => 'Salary - Marketing',
        'salary_seo' => 'Salary - SEO',
        'salary_upwork' => 'Salary - Upwork',
        'salary_server' => 'Salary - Server',
        'salary_developer' => 'Salary - Developer',
        'salary_designer' => 'Salary - Designer',
    ];

    protected $fillable = [
        'name',
        'category',
        'service_name',
        'amount',
        'currency',
        'is_recurring',
        'service_date',
        'invoice_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'service_date' => 'date',
    ];

    /**
     * Get the category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for recurring expenses
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope for one-time expenses
     */
    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', false);
    }

    /**
     * Scope for expenses in a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    /**
     * Scope for expenses this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('service_date', now()->month)
                     ->whereYear('service_date', now()->year);
    }

    /**
     * Get total expenses for a category
     */
    public static function getTotalByCategory(string $category): float
    {
        return static::where('category', $category)->sum('amount');
    }

    /**
     * Get all categories with totals
     */
    public static function getCategoryTotals(): array
    {
        $totals = [];
        foreach (self::CATEGORIES as $key => $label) {
            $totals[$key] = [
                'label' => $label,
                'total' => static::where('category', $key)->sum('amount'),
                'count' => static::where('category', $key)->count(),
            ];
        }
        return $totals;
    }
}
