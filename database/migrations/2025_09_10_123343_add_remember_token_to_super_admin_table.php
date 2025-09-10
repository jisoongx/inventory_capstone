<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_admin', function (Blueprint $table) {
            $table->string('remember_token', 100)->nullable()->after('super_pass');
        });
    }

    public function down(): void
    {
        Schema::table('super_admin', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
