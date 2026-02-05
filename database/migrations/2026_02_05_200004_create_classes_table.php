<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('hosts')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('capacity')->default(20);
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('host_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
