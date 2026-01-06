<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->renameColumn(
                'payment_acc_number',
                'paypal_subscription_id'
            );
        });
    }

    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->renameColumn(
                'paypal_subscription_id',
                'payment_acc_number'
            );
        });
    }
};
