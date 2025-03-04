<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->date('tanggal_lahir')->nullable();
            $table->string('nomor_hp', 20)->unique();
            $table->text('alamat')->nullable();
            $table->enum('role', ['admin', 'sales']);
            $table->foreignId('daerah_id')->nullable()->constrained('daerah')->onDelete('set null');
            $table->string('username', 100)->unique();
            $table->string('password');
            
            // Menambahkan kolom email
            $table->string('email')->unique()->nullable(false); // Kolom email yang tidak nullable
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('users');
    }
};


