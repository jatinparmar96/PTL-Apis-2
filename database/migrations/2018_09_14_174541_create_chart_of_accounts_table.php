<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('ca_company_name');
            $table->string('ca_company_display_name');
            $table->string('ca_category');
            $table->string('ca_code');
            $table->string('ca_opening_amount');
            $table->string('ca_opening_type');
           
            $table->string('address_id'); 
            $table->string('ca_pan');
            $table->string('ca_gstn');
            $table->string('ca_tan');
            
           
            $table->string('ca_date_opened');
            $table->timestamps();
            $table->integer('created_by_id');
            $table->integer('updated_by_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
}
