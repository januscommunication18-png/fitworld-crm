<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectWaitlist extends Model
{
    use HasFactory;

    const STUDIO_TYPES = [
        // Mind-Body Studios
        'yoga' => 'Yoga Studio',
        'pilates' => 'Pilates Studio',
        'barre' => 'Barre Studio',
        'meditation' => 'Meditation / Mindfulness Studio',
        'tai_chi' => 'Tai Chi / Qigong Studio',
        'stretch' => 'Stretch / Flexibility Studio',
        // Cardio Studios
        'hiit' => 'HIIT / Interval Training Studio',
        'cycling' => 'Indoor Cycling / Spin Studio',
        'rowing' => 'Rowing Studio',
        'bootcamp' => 'Boot Camp Studio',
        'running' => 'Running / Treadmill Studio',
        'trampoline' => 'Jump / Trampoline Fitness Studio',
        // Strength Studios
        'crossfit' => 'CrossFit Box',
        'functional' => 'Functional Training Studio',
        'strength' => 'Strength & Conditioning Studio',
        'powerlifting' => 'Powerlifting / Weightlifting Gym',
        'personal_training' => 'Personal Training Studio',
        'ems' => 'EMS (Electro Muscle Stimulation) Studio',
        // Dance Studios
        'dance' => 'Dance / Fitness Dance Studio',
        'zumba' => 'Zumba Studio',
        'pole' => 'Pole Fitness Studio',
        'aerial' => 'Aerial / Silks Studio',
        // Combat Studios
        'martial_arts' => 'Martial Arts Studio',
        'boxing' => 'Boxing / Kickboxing Studio',
        'mma' => 'MMA / Self-Defense Studio',
        // Water Studios
        'aqua' => 'Aqua Fitness Studio',
        'swimming' => 'Swimming Studio',
        'surf' => 'Surf / Paddleboard Fitness Studio',
        // Wellness Studios
        'spa' => 'Spa & Wellness Center',
        'recovery' => 'Recovery / Cryotherapy Studio',
        'rehab' => 'Physical Therapy / Rehab Studio',
        'nutrition' => 'Nutrition & Wellness Coaching Studio',
        'hot_studio' => 'Infrared / Hot Studio (Hot Yoga, etc.)',
        // Specialty Studios
        'climbing' => 'Rock Climbing Gym',
        'gymnastics' => 'Gymnastics Studio',
        'kids' => 'Kids / Youth Fitness Studio',
        'senior' => 'Senior / Active Aging Studio',
        'prenatal' => 'Pre/Postnatal Fitness Studio',
        'adaptive' => 'Adaptive / Inclusive Fitness Studio',
        'boutique' => 'Boutique Fitness Studio (Multi-Discipline)',
        'virtual' => 'Virtual / Online Fitness Studio',
        'corporate' => 'Corporate Wellness Studio',
        // General Gyms
        'traditional_gym' => 'Traditional / Commercial Gym',
        '24_hour_gym' => '24-Hour Gym',
        'women_only' => 'Women-Only Studio',
        'men_only' => 'Men-Only Studio',
        'other' => 'Other',
    ];

    const MEMBER_SIZES = [
        'solo' => 'Just me (solo instructor)',
        '1-50' => '1–50 members',
        '51-150' => '51–150 members',
        '151-500' => '151–500 members',
        '500+' => '500+ members',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'studio_name',
        'studio_type',
        'member_size',
    ];

    protected function casts(): array
    {
        return [
            'studio_type' => 'array',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getStudioTypeLabelAttribute(): string
    {
        $types = $this->studio_type ?? [];
        if (empty($types)) {
            return '-';
        }

        $labels = array_map(function ($type) {
            return self::STUDIO_TYPES[$type] ?? $type;
        }, $types);

        return implode(', ', $labels);
    }

    public function getMemberSizeLabelAttribute(): string
    {
        return self::MEMBER_SIZES[$this->member_size] ?? $this->member_size ?? '-';
    }

    public static function getStudioTypes(): array
    {
        return self::STUDIO_TYPES;
    }

    public static function getMemberSizes(): array
    {
        return self::MEMBER_SIZES;
    }
}
