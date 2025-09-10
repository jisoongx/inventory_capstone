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
        Schema::table('owners', function (Blueprint $table) {
            // Add the remember_token column after owner_pass
            $table->string('remember_token', 100)->nullable()->after('owner_pass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            // Remove the remember_token column if the migration is rolled back
            $table->dropColumn('remember_token');
        });
    }
};
