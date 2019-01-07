<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillOfMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bom_name');
            $table->string('item_name');
            $table->string('item_code');
            $table->integer('quantity');
            $table->integer('bom_number');
            $table->date('bom_date');
            $table->foreign('bom_uom')->references('id')->on('unit_of_measurements');
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
        Schema::dropIfExists('bill_of_materials');
    }
}
