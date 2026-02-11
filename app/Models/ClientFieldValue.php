<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'field_definition_id',
        'value',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fieldDefinition(): BelongsTo
    {
        return $this->belongsTo(ClientFieldDefinition::class, 'field_definition_id');
    }

    // Accessors

    public function getFormattedValueAttribute(): mixed
    {
        $definition = $this->fieldDefinition;

        if (!$definition) {
            return $this->value;
        }

        return match ($definition->field_type) {
            ClientFieldDefinition::TYPE_YES_NO => $this->value ? 'Yes' : 'No',
            ClientFieldDefinition::TYPE_CHECKBOX => is_array(json_decode($this->value, true))
                ? implode(', ', json_decode($this->value, true))
                : $this->value,
            ClientFieldDefinition::TYPE_DATE => $this->value
                ? \Carbon\Carbon::parse($this->value)->format('M d, Y')
                : null,
            default => $this->value,
        };
    }
}
