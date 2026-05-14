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
        Schema::create('us_cities', function (Blueprint $table) {
            $table->id();
            $table->string('city')->index();
            $table->string('city_ascii')->nullable()->index();
            $table->string('city_alt')->nullable();
            $table->string('state_id', 10)->nullable()->index();
            $table->string('state_name')->nullable()->index();
            $table->string('county_fips', 20)->nullable();
            $table->string('county_name')->nullable();
            $table->text('county_fips_all')->nullable();
            $table->text('county_name_all')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->unsignedBigInteger('population_proper')->nullable();
            $table->decimal('density', 12, 2)->nullable();
            $table->string('source', 50)->nullable();
            $table->boolean('military')->default(false);
            $table->boolean('incorporated')->default(false);
            $table->boolean('cdp')->default(false);
            $table->string('timezone', 100)->nullable();
            $table->unsignedTinyInteger('ranking')->nullable();
            $table->text('zips')->nullable();
            $table->string('simplemaps_id', 50)->nullable()->index();
            $table->decimal('age_median', 5, 2)->nullable();
            $table->decimal('male', 5, 2)->nullable();
            $table->decimal('female', 5, 2)->nullable();
            $table->decimal('married', 5, 2)->nullable();
            $table->decimal('family_size', 5, 2)->nullable();
            $table->unsignedInteger('income_household_median')->nullable();
            $table->decimal('income_household_six_figure', 5, 2)->nullable();
            $table->decimal('home_ownership', 5, 2)->nullable();
            $table->unsignedInteger('home_value')->nullable();
            $table->unsignedInteger('rent_median')->nullable();
            $table->decimal('education_college_or_above', 5, 2)->nullable();
            $table->decimal('labor_force_participation', 5, 2)->nullable();
            $table->decimal('unemployment_rate', 5, 2)->nullable();
            $table->decimal('race_white', 5, 2)->nullable();
            $table->decimal('race_black', 5, 2)->nullable();
            $table->decimal('race_asian', 5, 2)->nullable();
            $table->decimal('race_native', 5, 2)->nullable();
            $table->decimal('race_pacific', 5, 2)->nullable();
            $table->decimal('race_other', 5, 2)->nullable();
            $table->decimal('race_multiple', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['state_id', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('us_cities');
    }
};
