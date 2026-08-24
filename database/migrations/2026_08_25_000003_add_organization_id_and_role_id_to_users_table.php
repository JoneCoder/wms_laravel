<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('role_id')->after('organization_id')->nullable()->constrained('roles')->nullOnDelete();
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role'); // Remove old string role
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['role_id']);
                $table->dropForeign(['organization_id']);
            }
            $table->dropColumn('role_id');
            $table->dropColumn('organization_id');
            $table->string('role')->default('warehouse_operator');
        });
    }
};
