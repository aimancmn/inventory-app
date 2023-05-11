<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specification_type_id');
            $table->text('description')->comment('Description of the specification type. Ex: 3in AMOLED glass screen');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('specification_type_id')->references('id')->on('specification_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('specifications');
    }
}
