<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_on_one_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('sent_at');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained('one_on_one_bookings')->nullOnDelete();
            $table->timestamps();

            $table->index(['host_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_on_one_invites');
    }
};
