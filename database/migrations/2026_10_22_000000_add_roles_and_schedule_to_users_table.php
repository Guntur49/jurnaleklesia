<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('email', 'username');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->default('Staff');
            $table->string('role')->default('staff')->index();
            $table->time('journal_start_time')->nullable();
            $table->time('journal_end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['position', 'role', 'journal_start_time', 'journal_end_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
        });
    }
};
