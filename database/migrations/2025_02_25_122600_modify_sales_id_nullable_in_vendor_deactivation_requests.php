<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySalesIdNullableInVendorDeactivationRequests extends Migration
{
    public function up()
    {
        Schema::table('vendor_deactivation_requests', function (Blueprint $table) {
            // Hapus constraint foreign key yang sudah ada untuk sales_id
            $table->dropForeign(['sales_id']);

            // Ubah kolom sales_id menjadi nullable
            $table->unsignedBigInteger('sales_id')->nullable()->change();

            // Tambahkan kembali constraint foreign key untuk sales_id
            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('vendor_deactivation_requests', function (Blueprint $table) {
            // Hapus constraint foreign key
            $table->dropForeign(['sales_id']);

            // Ubah kolom sales_id kembali menjadi NOT NULL
            $table->unsignedBigInteger('sales_id')->nullable(false)->change();

            // Tambahkan kembali constraint foreign key
            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
