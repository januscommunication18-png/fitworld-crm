<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // owner, admin, staff, instructor
            $table->json('permissions')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'host_id']);
            $table->index('host_id');
            $table->index('user_id');
        });

        // Migrate existing data from users table to pivot table
        $users = DB::table('users')
            ->whereNotNull('host_id')
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $user->host_id,
                'role' => $user->role,
                'permissions' => $user->permissions,
                'instructor_id' => $user->instructor_id ?? null,
                'is_primary' => true,
                'joined_at' => $user->created_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('host_user');
    }
};
