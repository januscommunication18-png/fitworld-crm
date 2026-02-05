<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();
            $table->string('studio_name');
            $table->string('subdomain')->nullable()->unique();
            $table->json('studio_types')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('rooms')->default(1);
            $table->unsignedSmallInteger('default_capacity')->default(20);
            $table->json('room_capacities')->nullable();
            $table->json('amenities')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->boolean('is_live')->default(false);
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};
