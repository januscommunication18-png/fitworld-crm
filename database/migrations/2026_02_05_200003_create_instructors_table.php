<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('invite_status')->default('pending');
            $table->timestamps();

            $table->index('host_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
