<?php

namespace Database\Seeders;

use App\Models\ProgressTemplate;
use App\Models\ProgressTemplateSection;
use App\Models\ProgressTemplateMetric;
use Illuminate\Database\Seeder;

class ProgressTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->getTemplates();

        foreach ($templates as $templateData) {
            $sections = $templateData['sections'] ?? [];
            unset($templateData['sections']);

            $template = ProgressTemplate::firstOrCreate(
                ['slug' => $templateData['slug']],
                $templateData
            );

            foreach ($sections as $sectionIndex => $sectionData) {
                $metrics = $sectionData['metrics'] ?? [];
                unset($sectionData['metrics']);

                $sectionData['progress_template_id'] = $template->id;
                $sectionData['sort_order'] = $sectionIndex;

                $section = ProgressTemplateSection::firstOrCreate(
                    [
                        'progress_template_id' => $template->id,
                        'name' => $sectionData['name'],
                    ],
                    $sectionData
                );

                foreach ($metrics as $metricIndex => $metricData) {
                    $metricData['progress_template_section_id'] = $section->id;
                    $metricData['sort_order'] = $metricIndex;

                    ProgressTemplateMetric::firstOrCreate(
                        [
                            'progress_template_section_id' => $section->id,
                            'metric_key' => $metricData['metric_key'],
                        ],
                        $metricData
                    );
                }
            }
        }

        $this->command->info('Progress templates seeded successfully!');
    }

    private function getTemplates(): array
    {
        return [
            // Template 1: Yoga Growth & Alignment Tracker
            [
                'name' => 'Yoga Growth & Alignment Tracker',
                'slug' => 'yoga-growth-alignment',
                'description' => 'Comprehensive yoga practice assessment tracking flexibility, balance, strength, pose mastery, and wellness indicators.',
                'icon' => 'yoga',
                'studio_types' => ['yoga'],
                'is_active' => true,
                'sort_order' => 1,
                'scoring_model' => ['flexibility' => 25, 'balance' => 20, 'strength' => 25, 'wellness' => 30],
                'sections' => [
                    [
                        'name' => 'Physical Metrics',
                        'description' => 'Core physical assessments for yoga practice',
                        'icon' => 'activity',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Flexibility Score', 'metric_key' => 'flexibility_score', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'is_required' => true, 'weight' => 1.5, 'chart_color' => '#6366f1'],
                            ['name' => 'Balance Control', 'metric_key' => 'balance_control', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'is_required' => true, 'weight' => 1.2, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Core Strength', 'metric_key' => 'core_strength', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'is_required' => true, 'weight' => 1.0, 'chart_color' => '#ec4899'],
                            ['name' => 'Posture Alignment', 'metric_key' => 'posture_alignment', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'], 'weight' => 1.0, 'chart_color' => '#14b8a6'],
                            ['name' => 'Breath Control', 'metric_key' => 'breath_control', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 1.0, 'chart_color' => '#f59e0b'],
                        ],
                    ],
                    [
                        'name' => 'Pose Mastery',
                        'description' => 'Track progress on key yoga poses',
                        'icon' => 'stretching',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Sun Salutation', 'metric_key' => 'sun_salutation', 'metric_type' => 'select', 'options' => ['Attempted', 'Assisted', 'Independent', 'Mastered'], 'show_on_summary' => false],
                            ['name' => 'Warrior Series', 'metric_key' => 'warrior_series', 'metric_type' => 'select', 'options' => ['Attempted', 'Assisted', 'Independent', 'Mastered'], 'show_on_summary' => false],
                            ['name' => 'Balance Poses', 'metric_key' => 'balance_poses', 'metric_type' => 'select', 'options' => ['Attempted', 'Assisted', 'Independent', 'Mastered'], 'show_on_summary' => false],
                            ['name' => 'Inversions', 'metric_key' => 'inversions', 'metric_type' => 'select', 'options' => ['Not Attempted', 'Wall Support', 'Assisted', 'Independent'], 'show_on_summary' => false],
                        ],
                    ],
                    [
                        'name' => 'Wellness & Mindfulness',
                        'description' => 'Track mental and wellness progress',
                        'icon' => 'brain',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Stress Level', 'metric_key' => 'stress_level', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'description' => 'Lower is better', 'weight' => 0.8, 'chart_color' => '#ef4444'],
                            ['name' => 'Energy Level', 'metric_key' => 'energy_level', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 0.8, 'chart_color' => '#22c55e'],
                            ['name' => 'Sleep Quality', 'metric_key' => 'sleep_quality', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'], 'weight' => 0.5],
                            ['name' => 'Mindfulness Score', 'metric_key' => 'mindfulness_score', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 0.8, 'chart_color' => '#06b6d4'],
                        ],
                    ],
                    [
                        'name' => 'Trainer Notes',
                        'description' => 'Observations and recommendations',
                        'icon' => 'notes',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Instructor Observations', 'metric_key' => 'instructor_observations', 'metric_type' => 'text', 'show_on_summary' => false],
                            ['name' => 'Areas for Improvement', 'metric_key' => 'areas_improvement', 'metric_type' => 'text', 'show_on_summary' => false],
                            ['name' => 'Goals for Next Session', 'metric_key' => 'goals_next', 'metric_type' => 'text', 'show_on_summary' => false],
                        ],
                    ],
                ],
            ],

            // Template 2: Pilates Strength & Posture Tracker
            [
                'name' => 'Pilates Strength & Posture Tracker',
                'slug' => 'pilates-strength-posture',
                'description' => 'Track core strength, postural alignment, reformer progression, and rehabilitation progress.',
                'icon' => 'stretching',
                'studio_types' => ['pilates'],
                'is_active' => true,
                'sort_order' => 2,
                'sections' => [
                    [
                        'name' => 'Core Assessment',
                        'description' => 'Evaluate core strength and stability',
                        'icon' => 'target',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Core Stability', 'metric_key' => 'core_stability', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'is_required' => true, 'weight' => 1.5, 'chart_color' => '#6366f1'],
                            ['name' => 'Abdominal Strength', 'metric_key' => 'abdominal_strength', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 1.2, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Lower Back Strength', 'metric_key' => 'lower_back_strength', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 1.2, 'chart_color' => '#ec4899'],
                            ['name' => 'Hip Stability', 'metric_key' => 'hip_stability', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'weight' => 1.0, 'chart_color' => '#14b8a6'],
                            ['name' => 'Pelvic Alignment', 'metric_key' => 'pelvic_alignment', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Poor', 'Needs Work', 'Neutral', 'Good', 'Excellent']],
                        ],
                    ],
                    [
                        'name' => 'Posture Analysis',
                        'description' => 'Assess postural alignment and quality',
                        'icon' => 'user',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Spine Neutral Position', 'metric_key' => 'spine_neutral', 'metric_type' => 'select', 'options' => ['Rarely', 'Sometimes', 'Usually', 'Always']],
                            ['name' => 'Shoulder Alignment', 'metric_key' => 'shoulder_alignment', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent']],
                            ['name' => 'Movement Quality', 'metric_key' => 'movement_quality', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#f59e0b'],
                        ],
                    ],
                    [
                        'name' => 'Equipment Progression',
                        'description' => 'Track progress on Pilates equipment',
                        'icon' => 'barbell',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Mat Level', 'metric_key' => 'mat_level', 'metric_type' => 'select', 'options' => ['Beginner', 'Intermediate', 'Advanced', 'Master'], 'show_on_summary' => true],
                            ['name' => 'Reformer Level', 'metric_key' => 'reformer_level', 'metric_type' => 'select', 'options' => ['Beginner', 'Intermediate', 'Advanced', 'Master'], 'show_on_summary' => true],
                            ['name' => 'Hundred Exercise', 'metric_key' => 'hundred_exercise', 'metric_type' => 'select', 'options' => ['Level 1', 'Level 2', 'Level 3', 'Full']],
                            ['name' => 'Plank Hold Duration', 'metric_key' => 'plank_duration', 'metric_type' => 'number', 'unit' => 'seconds', 'min_value' => 0, 'max_value' => 300],
                        ],
                    ],
                    [
                        'name' => 'Injury & Rehab',
                        'description' => 'Track rehabilitation progress',
                        'icon' => 'heart-plus',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Pain Level', 'metric_key' => 'pain_level', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 10, 'description' => '0 = No pain, 10 = Severe', 'chart_color' => '#ef4444'],
                            ['name' => 'Mobility Improvement', 'metric_key' => 'mobility_improvement', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#22c55e'],
                            ['name' => 'Existing Conditions', 'metric_key' => 'existing_conditions', 'metric_type' => 'text', 'show_on_summary' => false],
                        ],
                    ],
                ],
            ],

            // Template 3: Performance & Body Transformation Tracker
            [
                'name' => 'Performance & Body Transformation Tracker',
                'slug' => 'performance-body-transformation',
                'description' => 'Complete fitness assessment for personal training: strength benchmarks, cardio metrics, body composition, and lifestyle compliance.',
                'icon' => 'barbell',
                'studio_types' => ['pt', 'gym', 'crossfit', 'fitness', 'strength'],
                'is_active' => true,
                'sort_order' => 3,
                'sections' => [
                    [
                        'name' => 'Fitness Goals',
                        'description' => 'Define and track fitness objectives',
                        'icon' => 'target',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Primary Goal', 'metric_key' => 'primary_goal', 'metric_type' => 'select', 'options' => ['Fat Loss', 'Muscle Gain', 'Endurance', 'Strength', 'General Fitness'], 'is_required' => true],
                            ['name' => 'Goal Progress', 'metric_key' => 'goal_progress', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%', 'chart_color' => '#22c55e'],
                        ],
                    ],
                    [
                        'name' => 'Strength Benchmarks',
                        'description' => 'Track key lifts and strength progress',
                        'icon' => 'barbell',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Squat', 'metric_key' => 'squat', 'metric_type' => 'number', 'unit' => 'kg', 'min_value' => 0, 'max_value' => 500, 'chart_color' => '#6366f1'],
                            ['name' => 'Bench Press', 'metric_key' => 'bench_press', 'metric_type' => 'number', 'unit' => 'kg', 'min_value' => 0, 'max_value' => 300, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Deadlift', 'metric_key' => 'deadlift', 'metric_type' => 'number', 'unit' => 'kg', 'min_value' => 0, 'max_value' => 500, 'chart_color' => '#ec4899'],
                            ['name' => 'Overhead Press', 'metric_key' => 'overhead_press', 'metric_type' => 'number', 'unit' => 'kg', 'min_value' => 0, 'max_value' => 200, 'chart_color' => '#14b8a6'],
                            ['name' => 'Pull-ups', 'metric_key' => 'pullups', 'metric_type' => 'number', 'unit' => 'reps', 'min_value' => 0, 'max_value' => 50, 'chart_color' => '#f59e0b'],
                        ],
                    ],
                    [
                        'name' => 'Cardio Performance',
                        'description' => 'Cardiovascular fitness metrics',
                        'icon' => 'heartbeat',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Resting Heart Rate', 'metric_key' => 'resting_hr', 'metric_type' => 'number', 'unit' => 'bpm', 'min_value' => 40, 'max_value' => 120, 'chart_color' => '#ef4444'],
                            ['name' => '1 Mile Time', 'metric_key' => 'mile_time', 'metric_type' => 'number', 'unit' => 'minutes', 'min_value' => 4, 'max_value' => 20, 'step' => 0.1],
                            ['name' => 'VO2 Max Estimate', 'metric_key' => 'vo2_estimate', 'metric_type' => 'number', 'unit' => 'ml/kg/min', 'min_value' => 20, 'max_value' => 80],
                            ['name' => 'Recovery Rate', 'metric_key' => 'recovery_rate', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Very Slow', 'Slow', 'Average', 'Fast', 'Excellent']],
                        ],
                    ],
                    [
                        'name' => 'Lifestyle Compliance',
                        'description' => 'Track adherence to fitness lifestyle',
                        'icon' => 'checklist',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Nutrition Score', 'metric_key' => 'nutrition_score', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#22c55e'],
                            ['name' => 'Water Intake', 'metric_key' => 'water_intake', 'metric_type' => 'number', 'unit' => 'liters', 'min_value' => 0, 'max_value' => 10, 'step' => 0.5],
                            ['name' => 'Sleep Hours', 'metric_key' => 'sleep_hours', 'metric_type' => 'number', 'unit' => 'hours', 'min_value' => 0, 'max_value' => 12, 'step' => 0.5],
                            ['name' => 'Workout Consistency', 'metric_key' => 'workout_consistency', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%', 'chart_color' => '#6366f1'],
                        ],
                    ],
                ],
            ],

            // Template 4: Control, Stability & Flexibility Tracker
            [
                'name' => 'Control, Stability & Flexibility Tracker',
                'slug' => 'control-stability-flexibility',
                'description' => 'For barre, stretch, gymnastics, seniors, and prenatal - focusing on flexibility, stability, balance, and safe movement patterns.',
                'icon' => 'stretching',
                'studio_types' => ['barre', 'stretch', 'gymnastics', 'seniors', 'prenatal', 'adaptive'],
                'is_active' => true,
                'sort_order' => 4,
                'sections' => [
                    [
                        'name' => 'Flexibility Assessment',
                        'description' => 'Measure range of motion and flexibility',
                        'icon' => 'arrows-maximize',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Hamstring Flexibility', 'metric_key' => 'hamstring_flex', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#6366f1'],
                            ['name' => 'Hip Flexibility', 'metric_key' => 'hip_flex', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Shoulder Mobility', 'metric_key' => 'shoulder_mobility', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#ec4899'],
                            ['name' => 'Spine Mobility', 'metric_key' => 'spine_mobility', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#14b8a6'],
                        ],
                    ],
                    [
                        'name' => 'Stability Metrics',
                        'description' => 'Assess balance and joint stability',
                        'icon' => 'scale',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Single-Leg Balance', 'metric_key' => 'single_leg_balance', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#f59e0b'],
                            ['name' => 'Core Stability', 'metric_key' => 'core_stability', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#22c55e'],
                            ['name' => 'Joint Stability', 'metric_key' => 'joint_stability', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#06b6d4'],
                            ['name' => 'Stability Hold Duration', 'metric_key' => 'stability_hold', 'metric_type' => 'number', 'unit' => 'seconds', 'min_value' => 0, 'max_value' => 120],
                        ],
                    ],
                    [
                        'name' => 'Movement Control',
                        'description' => 'Precision and coordination assessment',
                        'icon' => 'target',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Movement Precision', 'metric_key' => 'movement_precision', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Coordination', 'metric_key' => 'coordination', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Breath Control', 'metric_key' => 'breath_control', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Assisted vs Independent', 'metric_key' => 'independence_level', 'metric_type' => 'select', 'options' => ['Fully Assisted', 'Mostly Assisted', 'Minimal Assist', 'Independent']],
                        ],
                    ],
                    [
                        'name' => 'Safety Considerations',
                        'description' => 'Track any limitations or concerns',
                        'icon' => 'shield-check',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Injury Risk Level', 'metric_key' => 'injury_risk', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Low', 'Low-Medium', 'Medium', 'Medium-High', 'High']],
                            ['name' => 'Modifications Required', 'metric_key' => 'modifications', 'metric_type' => 'checkbox_list', 'options' => ['Balance Support', 'Reduced Range', 'Seated Options', 'Lower Intensity', 'Extra Rest']],
                            ['name' => 'Special Notes', 'metric_key' => 'special_notes', 'metric_type' => 'text', 'show_on_summary' => false],
                        ],
                    ],
                ],
            ],

            // Template 5: Cardio & Performance Intensity Tracker
            [
                'name' => 'Cardio & Performance Intensity Tracker',
                'slug' => 'cardio-performance-intensity',
                'description' => 'High-intensity workout tracking for HIIT, spin, rowing, bootcamp, and running studios.',
                'icon' => 'flame',
                'studio_types' => ['hiit', 'spin', 'rowing', 'bootcamp', 'running', 'trampoline'],
                'is_active' => true,
                'sort_order' => 5,
                'sections' => [
                    [
                        'name' => 'Heart Rate Metrics',
                        'description' => 'Monitor cardiovascular performance',
                        'icon' => 'heartbeat',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Max Heart Rate Reached', 'metric_key' => 'max_hr', 'metric_type' => 'number', 'unit' => 'bpm', 'min_value' => 100, 'max_value' => 220, 'chart_color' => '#ef4444'],
                            ['name' => 'Average Heart Rate', 'metric_key' => 'avg_hr', 'metric_type' => 'number', 'unit' => 'bpm', 'min_value' => 60, 'max_value' => 200, 'chart_color' => '#f59e0b'],
                            ['name' => 'Time in Zone 4-5', 'metric_key' => 'time_high_zone', 'metric_type' => 'number', 'unit' => 'minutes', 'min_value' => 0, 'max_value' => 60],
                            ['name' => 'Recovery Time', 'metric_key' => 'recovery_time', 'metric_type' => 'number', 'unit' => 'seconds', 'min_value' => 0, 'max_value' => 300, 'description' => 'Time to return to resting HR'],
                        ],
                    ],
                    [
                        'name' => 'Performance Metrics',
                        'description' => 'Track output and intensity',
                        'icon' => 'chart-line',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Calories Burned', 'metric_key' => 'calories', 'metric_type' => 'number', 'unit' => 'kcal', 'min_value' => 0, 'max_value' => 2000, 'chart_color' => '#22c55e'],
                            ['name' => 'Distance', 'metric_key' => 'distance', 'metric_type' => 'number', 'unit' => 'km', 'min_value' => 0, 'max_value' => 50, 'step' => 0.1],
                            ['name' => 'Average Speed/RPM', 'metric_key' => 'avg_speed', 'metric_type' => 'number', 'unit' => 'RPM', 'min_value' => 0, 'max_value' => 150],
                            ['name' => 'Power Output', 'metric_key' => 'power_output', 'metric_type' => 'number', 'unit' => 'watts', 'min_value' => 0, 'max_value' => 1000],
                        ],
                    ],
                    [
                        'name' => 'Endurance Assessment',
                        'description' => 'Evaluate stamina and endurance',
                        'icon' => 'run',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Stamina Level', 'metric_key' => 'stamina_level', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#6366f1'],
                            ['name' => 'Interval Capacity', 'metric_key' => 'interval_capacity', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Perceived Exertion', 'metric_key' => 'perceived_exertion', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'description' => 'RPE Scale'],
                        ],
                    ],
                    [
                        'name' => 'Personal Records',
                        'description' => 'Track personal bests',
                        'icon' => 'trophy',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => '500m Row Time', 'metric_key' => 'row_500m', 'metric_type' => 'number', 'unit' => 'seconds', 'min_value' => 60, 'max_value' => 300],
                            ['name' => '1 Mile Run Time', 'metric_key' => 'run_mile', 'metric_type' => 'number', 'unit' => 'minutes', 'min_value' => 4, 'max_value' => 20, 'step' => 0.1],
                            ['name' => 'Max Sprint Speed', 'metric_key' => 'max_sprint', 'metric_type' => 'number', 'unit' => 'km/h', 'min_value' => 0, 'max_value' => 40],
                        ],
                    ],
                ],
            ],

            // Template 6: Movement Skill & Performance Tracker
            [
                'name' => 'Movement Skill & Performance Tracker',
                'slug' => 'movement-skill-performance',
                'description' => 'Track dance, pole, aerial, and performance skills including technique, choreography, and artistic expression.',
                'icon' => 'music',
                'studio_types' => ['dance', 'zumba', 'pole', 'aerial'],
                'is_active' => true,
                'sort_order' => 6,
                'sections' => [
                    [
                        'name' => 'Skill Assessment',
                        'description' => 'Core movement and technique skills',
                        'icon' => 'star',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Technique Score', 'metric_key' => 'technique_score', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#6366f1'],
                            ['name' => 'Choreography Retention', 'metric_key' => 'choreo_retention', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Rhythm & Musicality', 'metric_key' => 'musicality', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#ec4899'],
                            ['name' => 'Coordination', 'metric_key' => 'coordination', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#14b8a6'],
                        ],
                    ],
                    [
                        'name' => 'Physical Attributes',
                        'description' => 'Supporting physical capabilities',
                        'icon' => 'activity',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Flexibility', 'metric_key' => 'flexibility', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Strength', 'metric_key' => 'strength', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Endurance', 'metric_key' => 'endurance', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Grip Strength', 'metric_key' => 'grip_strength', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'description' => 'For pole/aerial'],
                        ],
                    ],
                    [
                        'name' => 'Performance Readiness',
                        'description' => 'Stage presence and confidence',
                        'icon' => 'microphone-2',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Stage Presence', 'metric_key' => 'stage_presence', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Expression & Emotion', 'metric_key' => 'expression', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Performance Confidence', 'metric_key' => 'confidence', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                        ],
                    ],
                    [
                        'name' => 'Trick/Move Progression',
                        'description' => 'Track skill mastery',
                        'icon' => 'list-check',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Current Level', 'metric_key' => 'current_level', 'metric_type' => 'select', 'options' => ['Beginner', 'Intermediate', 'Advanced', 'Professional']],
                            ['name' => 'Moves Mastered', 'metric_key' => 'moves_mastered', 'metric_type' => 'checkbox_list', 'options' => ['Basic Spins', 'Climbs', 'Inversions', 'Combos', 'Advanced Tricks']],
                            ['name' => 'Working On', 'metric_key' => 'working_on', 'metric_type' => 'text', 'show_on_summary' => false],
                        ],
                    ],
                ],
            ],

            // Template 7: Combat Skill & Conditioning Tracker
            [
                'name' => 'Combat Skill & Conditioning Tracker',
                'slug' => 'combat-skill-conditioning',
                'description' => 'Comprehensive tracking for martial arts, boxing, and MMA including technical skills, conditioning, and belt progression.',
                'icon' => 'karate',
                'studio_types' => ['martial_arts', 'boxing', 'mma', 'kickboxing'],
                'is_active' => true,
                'sort_order' => 7,
                'sections' => [
                    [
                        'name' => 'Technical Skills',
                        'description' => 'Combat technique assessment',
                        'icon' => 'target',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Striking Technique', 'metric_key' => 'striking', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#ef4444'],
                            ['name' => 'Defense Skill', 'metric_key' => 'defense', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#3b82f6'],
                            ['name' => 'Footwork', 'metric_key' => 'footwork', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#8b5cf6'],
                            ['name' => 'Grappling/Clinch', 'metric_key' => 'grappling', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#14b8a6'],
                        ],
                    ],
                    [
                        'name' => 'Conditioning',
                        'description' => 'Physical conditioning for combat',
                        'icon' => 'flame',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Speed', 'metric_key' => 'speed', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Power', 'metric_key' => 'power', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Stamina', 'metric_key' => 'stamina', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Reaction Time', 'metric_key' => 'reaction_time', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Round Endurance', 'metric_key' => 'round_endurance', 'metric_type' => 'number', 'unit' => 'rounds', 'min_value' => 1, 'max_value' => 12],
                        ],
                    ],
                    [
                        'name' => 'Belt/Level Progression',
                        'description' => 'Track rank advancement',
                        'icon' => 'award',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Current Rank', 'metric_key' => 'current_rank', 'metric_type' => 'text', 'description' => 'e.g., Blue Belt, Level 3'],
                            ['name' => 'Time at Current Rank', 'metric_key' => 'time_at_rank', 'metric_type' => 'number', 'unit' => 'months', 'min_value' => 0, 'max_value' => 60],
                            ['name' => 'Ready for Grading', 'metric_key' => 'grading_ready', 'metric_type' => 'select', 'options' => ['Not Yet', 'Almost', 'Ready', 'Overdue']],
                        ],
                    ],
                    [
                        'name' => 'Sparring Assessment',
                        'description' => 'Live practice evaluation',
                        'icon' => 'users',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Sparring Score', 'metric_key' => 'sparring_score', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Composure Under Pressure', 'metric_key' => 'composure', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10],
                            ['name' => 'Competition Readiness', 'metric_key' => 'competition_ready', 'metric_type' => 'select', 'options' => ['Not Ready', 'Beginner Level', 'Intermediate', 'Advanced', 'Elite']],
                        ],
                    ],
                ],
            ],

            // Template 8: Recovery & Wellness Progress Tracker
            [
                'name' => 'Recovery & Wellness Progress Tracker',
                'slug' => 'recovery-wellness-progress',
                'description' => 'Track rehabilitation, pain management, mobility improvement, and overall wellness for spa, therapy, and wellness studios.',
                'icon' => 'heart-plus',
                'studio_types' => ['spa', 'cryotherapy', 'physical_therapy', 'nutrition', 'massage', 'wellness'],
                'is_active' => true,
                'sort_order' => 8,
                'sections' => [
                    [
                        'name' => 'Pain Assessment',
                        'description' => 'Monitor pain and discomfort levels',
                        'icon' => 'medical-cross',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Overall Pain Level', 'metric_key' => 'pain_level', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 10, 'description' => '0 = No pain, 10 = Severe', 'chart_color' => '#ef4444'],
                            ['name' => 'Pain Frequency', 'metric_key' => 'pain_frequency', 'metric_type' => 'select', 'options' => ['Constant', 'Frequent', 'Occasional', 'Rare', 'None']],
                            ['name' => 'Pain Location', 'metric_key' => 'pain_location', 'metric_type' => 'checkbox_list', 'options' => ['Neck', 'Upper Back', 'Lower Back', 'Shoulders', 'Hips', 'Knees', 'Other']],
                        ],
                    ],
                    [
                        'name' => 'Mobility Metrics',
                        'description' => 'Track movement and range of motion',
                        'icon' => 'arrows-maximize',
                        'is_required' => true,
                        'metrics' => [
                            ['name' => 'Range of Motion', 'metric_key' => 'range_of_motion', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%', 'chart_color' => '#22c55e'],
                            ['name' => 'Functional Movement', 'metric_key' => 'functional_movement', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#6366f1'],
                            ['name' => 'Mobility Improvement', 'metric_key' => 'mobility_improvement', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%'],
                        ],
                    ],
                    [
                        'name' => 'Wellness Indicators',
                        'description' => 'Overall wellness and lifestyle factors',
                        'icon' => 'heart',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Sleep Quality', 'metric_key' => 'sleep_quality', 'metric_type' => 'rating', 'min_value' => 1, 'max_value' => 5, 'rating_labels' => ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent']],
                            ['name' => 'Stress Level', 'metric_key' => 'stress_level', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'description' => 'Lower is better', 'chart_color' => '#f59e0b'],
                            ['name' => 'Energy Level', 'metric_key' => 'energy_level', 'metric_type' => 'slider', 'min_value' => 1, 'max_value' => 10, 'chart_color' => '#06b6d4'],
                            ['name' => 'Inflammation Level', 'metric_key' => 'inflammation', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 10, 'description' => 'Lower is better'],
                        ],
                    ],
                    [
                        'name' => 'Treatment Progress',
                        'description' => 'Track recovery milestones',
                        'icon' => 'trending-up',
                        'is_required' => false,
                        'metrics' => [
                            ['name' => 'Recovery Progress', 'metric_key' => 'recovery_progress', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%', 'chart_color' => '#22c55e'],
                            ['name' => 'Treatment Response', 'metric_key' => 'treatment_response', 'metric_type' => 'select', 'options' => ['No Change', 'Slight Improvement', 'Moderate Improvement', 'Significant Improvement', 'Fully Recovered']],
                            ['name' => 'Nutrition Compliance', 'metric_key' => 'nutrition_compliance', 'metric_type' => 'slider', 'min_value' => 0, 'max_value' => 100, 'unit' => '%'],
                            ['name' => 'Session Outcomes', 'metric_key' => 'session_outcomes', 'metric_type' => 'text', 'show_on_summary' => false],
                        ],
                    ],
                ],
            ],
        ];
    }
}
