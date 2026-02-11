<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ClientFieldDefinition extends Model
{
    use HasFactory;

    // Field type constants
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_DATE = 'date';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_YES_NO = 'yes_no';

    protected $fillable = [
        'host_id',
        'section_id',
        'field_key',
        'field_label',
        'field_type',
        'options',
        'is_required',
        'help_text',
        'default_value',
        'show_on_add',
        'show_on_edit',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'show_on_add' => 'boolean',
            'show_on_edit' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (empty($field->field_key)) {
                $field->field_key = Str::slug($field->field_label, '_');
            }
        });
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ClientFieldSection::class, 'section_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class, 'field_definition_id');
    }

    // Scopes

    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    public function scopeShowOnAdd(Builder $query): Builder
    {
        return $query->where('show_on_add', true);
    }

    public function scopeShowOnEdit(Builder $query): Builder
    {
        return $query->where('show_on_edit', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function scopeInSection(Builder $query, $sectionId): Builder
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeWithoutSection(Builder $query): Builder
    {
        return $query->whereNull('section_id');
    }

    // Methods

    public function canBeDeleted(): bool
    {
        return !$this->is_system;
    }

    // Static helpers

    public static function getFieldTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Short Text',
            self::TYPE_TEXTAREA => 'Long Text',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_DATE => 'Date',
            self::TYPE_DROPDOWN => 'Dropdown',
            self::TYPE_CHECKBOX => 'Checkboxes',
            self::TYPE_YES_NO => 'Yes/No Toggle',
        ];
    }

    public static function getFieldTypeIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_TEXT => 'icon-[tabler--text-size]',
            self::TYPE_TEXTAREA => 'icon-[tabler--align-left]',
            self::TYPE_NUMBER => 'icon-[tabler--123]',
            self::TYPE_DATE => 'icon-[tabler--calendar]',
            self::TYPE_DROPDOWN => 'icon-[tabler--selector]',
            self::TYPE_CHECKBOX => 'icon-[tabler--checkbox]',
            self::TYPE_YES_NO => 'icon-[tabler--toggle-left]',
            default => 'icon-[tabler--forms]',
        };
    }
}
