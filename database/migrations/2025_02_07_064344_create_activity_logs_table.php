<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('table_name');
            $table->bigInteger('record_id');
            $table->enum('action', ['insert', 'update', 'delete', 'approve', 'reject']);
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('activity_logs');
    }
};

