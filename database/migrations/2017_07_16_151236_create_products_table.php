<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('category_id')->unsigned();
            $table->string('product_code');
            $table->string('unit_code');
            $table->string('name');
            $table->string('hsn_code');
            $table->longText('description');
            $table->decimal('price',10,2);
            $table->integer('stock');
            $table->integer('min_stock');
            $table->integer('max_stock');
            $table->integer('validity_period');
            $table->integer('inserted_by_id')->unsigned();
            $table->integer('updated_by_id')->unsigned();
            $table->string('extra_field_1');
            $table->string('extra_field_2');
            $table->string('extra_field_3');
            $table->string('extra_field_4');
            $table->string('extra_field_5');
            $table->string('extra_field_6');

            // $table->foreign('category_id')->references('id')->on('product_categories');
            // $table->foreign('inserted_by_id')->references('id')->on('users');
            // $table->foreign('updated_by_id')->references('id')->on('users');
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
        Schema::dropIfExists('products');
    }
}
