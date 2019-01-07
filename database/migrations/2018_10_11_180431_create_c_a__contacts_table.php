<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCAContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ca_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ca_company_id');
            $table->string('ca_contact_first_name');
            $table->string('ca_contact_last_name');
            $table->string('ca_contact_email');
            $table->string('ca_contact_mobile_number');
            $table->string('ca_contact_designation');
            $table->string('ca_contact_branch');
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
        Schema::dropIfExists('c_a__contacts');
    }
}
