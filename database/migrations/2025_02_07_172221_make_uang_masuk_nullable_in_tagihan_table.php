<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUangMasukNullableInTagihanTable extends Migration
{
    public function up()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->decimal('uang_masuk', 15, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->decimal('uang_masuk', 15, 2)->nullable()->change();
        });
    }
}

