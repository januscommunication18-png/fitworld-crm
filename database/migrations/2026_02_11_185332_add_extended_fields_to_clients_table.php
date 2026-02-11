<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Basic Information
            $table->string('secondary_phone')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('secondary_phone');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');
            $table->string('profile_photo')->nullable()->after('gender');

            // Contact Details (expanding address)
            $table->string('address_line_1')->nullable()->after('profile_photo');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('state_province')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state_province');
            $table->string('country')->nullable()->after('postal_code');

            // Membership fields
            $table->foreignId('membership_plan_id')->nullable()->after('membership_status');
            $table->date('membership_start_date')->nullable()->after('membership_plan_id');
            $table->date('membership_end_date')->nullable()->after('membership_start_date');
            $table->date('membership_renewal_date')->nullable()->after('membership_end_date');

            // Source & Marketing (some already exist)
            $table->string('referral_source')->nullable()->after('utm_campaign');
            $table->string('utm_term')->nullable()->after('referral_source');
            $table->string('utm_content')->nullable()->after('utm_term');

            // Engagement & Activity
            $table->date('first_visit_date')->nullable()->after('utm_content');
            $table->integer('total_classes_attended')->default(0)->after('first_visit_date');
            $table->integer('total_services_booked')->default(0)->after('total_classes_attended');
            $table->decimal('lifetime_value', 10, 2)->default(0)->after('total_services_booked');
            $table->decimal('total_spent', 10, 2)->default(0)->after('lifetime_value');

            // Communication Preferences
            $table->boolean('email_opt_in')->default(true)->after('total_spent');
            $table->boolean('sms_opt_in')->default(false)->after('email_opt_in');
            $table->boolean('marketing_opt_in')->default(true)->after('sms_opt_in');
            $table->enum('preferred_contact_method', ['email', 'phone', 'sms'])->default('email')->after('marketing_opt_in');

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('preferred_contact_method');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
            $table->string('emergency_contact_email')->nullable()->after('emergency_contact_phone');

            // Health & Fitness
            $table->text('medical_conditions')->nullable()->after('emergency_contact_email');
            $table->text('injuries')->nullable()->after('medical_conditions');
            $table->text('limitations')->nullable()->after('injuries');
            $table->text('fitness_goals')->nullable()->after('limitations');
            $table->enum('experience_level', ['beginner', 'intermediate', 'advanced'])->nullable()->after('fitness_goals');
            $table->boolean('pregnancy_status')->nullable()->after('experience_level');

            // Internal
            $table->foreignId('assigned_staff_id')->nullable()->after('pregnancy_status');
            $table->foreignId('assigned_instructor_id')->nullable()->after('assigned_staff_id');

            // System / Metadata
            $table->foreignId('created_by_user_id')->nullable()->after('assigned_instructor_id');
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'secondary_phone',
                'date_of_birth',
                'gender',
                'profile_photo',
                'address_line_1',
                'address_line_2',
                'city',
                'state_province',
                'postal_code',
                'country',
                'membership_plan_id',
                'membership_start_date',
                'membership_end_date',
                'membership_renewal_date',
                'referral_source',
                'utm_term',
                'utm_content',
                'first_visit_date',
                'total_classes_attended',
                'total_services_booked',
                'lifetime_value',
                'total_spent',
                'email_opt_in',
                'sms_opt_in',
                'marketing_opt_in',
                'preferred_contact_method',
                'emergency_contact_name',
                'emergency_contact_relationship',
                'emergency_contact_phone',
                'emergency_contact_email',
                'medical_conditions',
                'injuries',
                'limitations',
                'fitness_goals',
                'experience_level',
                'pregnancy_status',
                'assigned_staff_id',
                'assigned_instructor_id',
                'created_by_user_id',
                'updated_by_user_id',
            ]);
        });
    }
};
