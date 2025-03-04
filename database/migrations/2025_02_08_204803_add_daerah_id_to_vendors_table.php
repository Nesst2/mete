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
        $table->bigInteger('daerah_id')->unsigned()->nullable()->after('wilayah_id');
        // Anda bisa mengubah nullable sesuai kebutuhan
    });
}

public function down()
{
    Schema::table('vendors', function (Blueprint $table) {
        $table->dropColumn('daerah_id');
    });
}

};
