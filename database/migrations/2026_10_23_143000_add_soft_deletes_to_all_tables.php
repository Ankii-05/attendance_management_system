<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Add soft deletes column to all application tables
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('attendance') && !Schema::hasColumn('attendance', 'deleted_at')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'deleted_at')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    // Rollback soft deletes from all application tables
    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'deleted_at')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('attendance') && Schema::hasColumn('attendance', 'deleted_at')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
