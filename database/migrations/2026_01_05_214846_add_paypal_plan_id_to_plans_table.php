<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('paypal_plan_id', 100)
                ->nullable()
                ->after('plan_price');
        });

        // 🔗 Seed existing PayPal plan IDs
        DB::table('plans')->where('plan_id', 1)->update([
            'paypal_plan_id' => 'P-7C785523EJ448962XNEV5C6Q' // Standard
        ]);

        DB::table('plans')->where('plan_id', 2)->update([
            'paypal_plan_id' => 'P-9BL62740BA150960NNEV5EJI' // Premium
        ]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('paypal_plan_id');
        });
    }
};
