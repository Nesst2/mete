<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('vendors', function (Blueprint $table) {
        $table->unsignedBigInteger('wilayah_id')->nullable();
        $table->foreign('wilayah_id')->references('id')->on('wilayah')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('vendors', function (Blueprint $table) {
        $table->dropForeign(['wilayah_id']);
        $table->dropColumn('wilayah_id');
    });
}

};
