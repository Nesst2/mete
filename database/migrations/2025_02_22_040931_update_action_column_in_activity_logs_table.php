<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateActionColumnInActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Ubah enum action untuk menambahkan login dan logout
            $table->enum('action', ['insert', 'update', 'delete', 'approve', 'reject', 'login', 'logout'])
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Kembalikan ke enum semula tanpa login dan logout
            $table->enum('action', ['insert', 'update', 'delete', 'approve', 'reject'])
                  ->change();
        });
    }
}
