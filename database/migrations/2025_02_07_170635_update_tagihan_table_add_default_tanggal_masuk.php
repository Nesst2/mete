<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan ini ada!

class UpdateTagihanTableAddDefaultTanggalMasuk extends Migration
{
    public function up()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // Set default value to current timestamp
            $table->timestamp('tanggal_masuk')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
        });
    }

    public function down()
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // Revert back to the previous state if rollback
            $table->timestamp('tanggal_masuk')->nullable()->change();
        });
    }
}
