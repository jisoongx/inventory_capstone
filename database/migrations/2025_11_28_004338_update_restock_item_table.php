<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRestockItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restock_item', function (Blueprint $table) {
            // Add the 'prod_code' column
            $table->integer('prod_code')->after('restock_id');  // Adjust position as needed

            // Remove the 'inven_code' column
            $table->dropColumn('inven_code');

            // Add foreign key constraint to 'prod_code'
            $table->foreign('prod_code')          // The column we want to add the foreign key to
                ->references('prod_code')      // The column in the referenced table
                ->on('products')               // The referenced table
                ->onDelete('cascade');         // This ensures that if a product is deleted, the related restock items are also deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restock_item', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['prod_code']);

            // Remove 'prod_code' and add 'inven_code' back
            $table->dropColumn('prod_code');
            $table->integer('inven_code')->after('restock_id');  // Adjust position as needed
        });
    }
}
