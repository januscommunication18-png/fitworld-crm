<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SegmentRule extends Model
{
    use HasFactory;

    // Operator constants
    const OP_EQUALS = 'equals';
    const OP_NOT_EQUALS = 'not_equals';
    const OP_GREATER_THAN = 'greater_than';
    const OP_LESS_THAN = 'less_than';
    const OP_GREATER_OR_EQUAL = 'greater_or_equal';
    const OP_LESS_OR_EQUAL = 'less_or_equal';
    const OP_CONTAINS = 'contains';
    const OP_NOT_CONTAINS = 'not_contains';
    const OP_STARTS_WITH = 'starts_with';
    const OP_ENDS_WITH = 'ends_with';
    const OP_IN = 'in';
    const OP_NOT_IN = 'not_in';
    const OP_IS_NULL = 'is_null';
    const OP_IS_NOT_NULL = 'is_not_null';
    const OP_DAYS_AGO_MORE_THAN = 'days_ago_more_than';
    const OP_DAYS_AGO_LESS_THAN = 'days_ago_less_than';
    const OP_IS_TRUE = 'is_true';
    const OP_IS_FALSE = 'is_false';

    protected $fillable = [
        'segment_id',
        'group_index',
        'field',
        'operator',
        'value',
        'relative_unit',
        'relative_value',
    ];

    protected function casts(): array
    {
        return [
            'group_index' => 'integer',
            'relative_value' => 'integer',
        ];
    }

    // Relationships
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    // Available fields for segmentation
    public static function getAvailableFields(): array
    {
        return [
            // Basic Filters
            'status' => ['label' => 'Client Status', 'type' => 'select', 'options' => ['lead', 'client', 'member', 'at_risk']],
            'membership_status' => ['label' => 'Membership Status', 'type' => 'select', 'options' => ['none', 'active', 'paused', 'cancelled']],
            'membership_plan_id' => ['label' => 'Membership Type', 'type' => 'relation', 'model' => 'MembershipPlan'],
            'total_spent' => ['label' => 'Total Spend', 'type' => 'number'],
            'total_classes_attended' => ['label' => 'Total Visits', 'type' => 'number'],
            'last_visit_at' => ['label' => 'Last Visit Date', 'type' => 'date'],
            'lead_source' => ['label' => 'Source', 'type' => 'select', 'options' => ['manual', 'marketing', 'website', 'lead_magnet', 'fitnearyou', 'referral']],
            'created_at' => ['label' => 'Client Since', 'type' => 'date'],

            // Behavior-based Filters (computed)
            'days_since_last_visit' => ['label' => 'Days Since Last Visit', 'type' => 'number', 'computed' => true],
            'classes_last_30_days' => ['label' => 'Classes in Last 30 Days', 'type' => 'number', 'computed' => true],
            'has_unpaid_invoice' => ['label' => 'Has Unpaid Invoice', 'type' => 'boolean', 'computed' => true],
            'waitlist_count' => ['label' => 'Times on Waitlist', 'type' => 'number', 'computed' => true],
            'membership_cancelled_recently' => ['label' => 'Cancelled Membership Recently', 'type' => 'boolean', 'computed' => true],

            // Demographics
            'gender' => ['label' => 'Gender', 'type' => 'select', 'options' => ['male', 'female', 'other', 'prefer_not_to_say']],
            'city' => ['label' => 'City', 'type' => 'text'],
            'state_province' => ['label' => 'State/Province', 'type' => 'text'],
            'country' => ['label' => 'Country', 'type' => 'text'],

            // Engagement
            'email_opt_in' => ['label' => 'Email Opted In', 'type' => 'boolean'],
            'sms_opt_in' => ['label' => 'SMS Opted In', 'type' => 'boolean'],
            'marketing_opt_in' => ['label' => 'Marketing Opted In', 'type' => 'boolean'],
        ];
    }

    public static function getOperators(): array
    {
        return [
            self::OP_EQUALS => 'equals',
            self::OP_NOT_EQUALS => 'does not equal',
            self::OP_GREATER_THAN => 'is greater than',
            self::OP_LESS_THAN => 'is less than',
            self::OP_GREATER_OR_EQUAL => 'is at least',
            self::OP_LESS_OR_EQUAL => 'is at most',
            self::OP_CONTAINS => 'contains',
            self::OP_NOT_CONTAINS => 'does not contain',
            self::OP_STARTS_WITH => 'starts with',
            self::OP_ENDS_WITH => 'ends with',
            self::OP_IN => 'is one of',
            self::OP_NOT_IN => 'is not one of',
            self::OP_IS_NULL => 'is empty',
            self::OP_IS_NOT_NULL => 'is not empty',
            self::OP_DAYS_AGO_MORE_THAN => 'is more than X days ago',
            self::OP_DAYS_AGO_LESS_THAN => 'is less than X days ago',
            self::OP_IS_TRUE => 'is true',
            self::OP_IS_FALSE => 'is false',
        ];
    }

    /**
     * Apply this rule to a query builder
     */
    public function applyToQuery(Builder $query): void
    {
        $field = $this->field;
        $operator = $this->operator;
        $value = $this->value;

        // Handle computed fields
        if ($this->isComputedField($field)) {
            $this->applyComputedFieldQuery($query, $field, $operator, $value);
            return;
        }

        // Handle custom fields (format: custom_field:field_key)
        if (str_starts_with($field, 'custom_field:')) {
            $this->applyCustomFieldQuery($query, $field, $operator, $value);
            return;
        }

        // Standard field queries
        switch ($operator) {
            case self::OP_EQUALS:
                $query->where($field, '=', $value);
                break;
            case self::OP_NOT_EQUALS:
                $query->where($field, '!=', $value);
                break;
            case self::OP_GREATER_THAN:
                $query->where($field, '>', $value);
                break;
            case self::OP_LESS_THAN:
                $query->where($field, '<', $value);
                break;
            case self::OP_GREATER_OR_EQUAL:
                $query->where($field, '>=', $value);
                break;
            case self::OP_LESS_OR_EQUAL:
                $query->where($field, '<=', $value);
                break;
            case self::OP_CONTAINS:
                $query->where($field, 'LIKE', "%{$value}%");
                break;
            case self::OP_NOT_CONTAINS:
                $query->where($field, 'NOT LIKE', "%{$value}%");
                break;
            case self::OP_STARTS_WITH:
                $query->where($field, 'LIKE', "{$value}%");
                break;
            case self::OP_ENDS_WITH:
                $query->where($field, 'LIKE', "%{$value}");
                break;
            case self::OP_IN:
                $values = is_array($value) ? $value : json_decode($value, true);
                $query->whereIn($field, $values ?? []);
                break;
            case self::OP_NOT_IN:
                $values = is_array($value) ? $value : json_decode($value, true);
                $query->whereNotIn($field, $values ?? []);
                break;
            case self::OP_IS_NULL:
                $query->whereNull($field);
                break;
            case self::OP_IS_NOT_NULL:
                $query->whereNotNull($field);
                break;
            case self::OP_DAYS_AGO_MORE_THAN:
                $date = Carbon::now()->subDays((int) $value);
                $query->where($field, '<', $date);
                break;
            case self::OP_DAYS_AGO_LESS_THAN:
                $date = Carbon::now()->subDays((int) $value);
                $query->where($field, '>', $date);
                break;
            case self::OP_IS_TRUE:
                $query->where($field, '=', true);
                break;
            case self::OP_IS_FALSE:
                $query->where($field, '=', false);
                break;
        }
    }

    protected function isComputedField(string $field): bool
    {
        return in_array($field, [
            'days_since_last_visit',
            'classes_last_30_days',
            'has_unpaid_invoice',
            'waitlist_count',
            'membership_cancelled_recently',
        ]);
    }

    protected function applyComputedFieldQuery(Builder $query, string $field, string $operator, $value): void
    {
        switch ($field) {
            case 'days_since_last_visit':
                $date = Carbon::now()->subDays((int) $value);
                if ($operator === self::OP_GREATER_THAN) {
                    $query->where(function ($q) use ($date) {
                        $q->where('last_visit_at', '<', $date)
                            ->orWhereNull('last_visit_at');
                    });
                } elseif ($operator === self::OP_LESS_THAN) {
                    $query->where('last_visit_at', '>', $date);
                }
                break;

            case 'classes_last_30_days':
                $query->whereHas('bookings', function ($q) use ($operator, $value) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30))
                        ->where('status', 'completed');
                }, $this->getOperatorSymbol($operator), (int) $value);
                break;

            case 'has_unpaid_invoice':
                if ($operator === self::OP_IS_TRUE) {
                    $query->whereHas('invoices', function ($q) {
                        $q->whereIn('status', ['sent', 'draft'])
                            ->where('due_date', '<', now());
                    });
                } else {
                    $query->whereDoesntHave('invoices', function ($q) {
                        $q->whereIn('status', ['sent', 'draft'])
                            ->where('due_date', '<', now());
                    });
                }
                break;

            case 'membership_cancelled_recently':
                $recentDays = 30;
                if ($operator === self::OP_IS_TRUE) {
                    $query->where('membership_status', 'cancelled')
                        ->where('updated_at', '>=', Carbon::now()->subDays($recentDays));
                }
                break;
        }
    }

    protected function applyCustomFieldQuery(Builder $query, string $field, string $operator, $value): void
    {
        $fieldKey = str_replace('custom_field:', '', $field);

        $query->whereHas('fieldValues', function ($q) use ($fieldKey, $operator, $value) {
            $q->whereHas('fieldDefinition', function ($defQ) use ($fieldKey) {
                $defQ->where('key', $fieldKey);
            });

            switch ($operator) {
                case self::OP_EQUALS:
                    $q->where('value', $value);
                    break;
                case self::OP_NOT_EQUALS:
                    $q->where('value', '!=', $value);
                    break;
                case self::OP_CONTAINS:
                    $q->where('value', 'LIKE', "%{$value}%");
                    break;
            }
        });
    }

    protected function getOperatorSymbol(string $operator): string
    {
        return match ($operator) {
            self::OP_EQUALS => '=',
            self::OP_NOT_EQUALS => '!=',
            self::OP_GREATER_THAN => '>',
            self::OP_LESS_THAN => '<',
            self::OP_GREATER_OR_EQUAL => '>=',
            self::OP_LESS_OR_EQUAL => '<=',
            default => '=',
        };
    }
}
