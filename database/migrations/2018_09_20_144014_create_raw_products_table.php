<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('product_name');
            $table->string('product_display_name');
            $table->string('product_code');
            $table->integer('product_uom');
            $table->integer('product_category');
            $table->string('product_trade_name');
            $table->integer('product_conv_uom');
            $table->double('product_conv_factor',8,2);
            $table->boolean('product_batch_type');
            $table->boolean('product_stock_ledger');
            $table->string('product_store_location');
            $table->integer('product_opening_stock');
            $table->double('opening_amount',8,2);
            $table->string('product_product_rate_pick');
            $table->double('product_purchase_rate',8,2);
            $table->double('product_mrp_rate',8,2);
            $table->double('product_sales_rate',8,2);
            $table->double('product_gst_rate');
            $table->integer('product_max_level');
            $table->integer('product_min_level');
            $table->integer('product_reorder_level');
            $table->string('product_hsn');
            $table->text('product_description');      
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('raw_products');
    }
}
