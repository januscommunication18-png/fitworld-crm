<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['rental_booking_id', 'created_at']);
        });

        // Add request_id to rental_bookings for easy reference
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->string('request_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropColumn('request_id');
        });

        Schema::dropIfExists('rental_booking_status_logs');
    }
};
