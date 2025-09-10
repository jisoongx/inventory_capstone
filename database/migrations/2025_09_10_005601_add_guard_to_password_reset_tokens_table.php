<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Add a guard column so we know if it's super_admin, owner, or staff
            $table->enum('guard', ['super_admin', 'owner', 'staff'])->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('guard');
        });
    }
};
