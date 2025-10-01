<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restock_item', function (Blueprint $table) {
            $table->dropColumn('item_priority');
        });
    }

    public function down(): void
    {
        Schema::table('restock_item', function (Blueprint $table) {
            // Re-add the column if you roll back
            $table->string('item_priority')->nullable();
        });
    }
};
