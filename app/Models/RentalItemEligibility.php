<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItemEligibility extends Model
{
    // Eligibility type constants
    const TYPE_ALL = 'all';
    const TYPE_MEMBERSHIP = 'membership';
    const TYPE_CLASS_PACK = 'class_pack';

    protected $table = 'rental_item_eligibility';

    protected $fillable = [
        'rental_item_id',
        'eligible_type',
        'membership_plan_id',
        'class_pack_id',
        'is_free',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function rentalItem(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function classPack(): BelongsTo
    {
        return $this->belongsTo(ClassPack::class);
    }

    /**
     * Helpers
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ALL => 'Everyone',
            self::TYPE_MEMBERSHIP => 'Membership Holders',
            self::TYPE_CLASS_PACK => 'Class Pack Holders',
        ];
    }
}
