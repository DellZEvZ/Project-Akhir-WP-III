<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permintaan reset password dari app mobile; disetujui admin lewat panel.
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('token_hash', 64)->unique();      // sha256 dari token yang hanya dipegang app pemohon
            $table->string('status', 20)->default('pending'); // pending, approved, delivered, rejected, expired
            $table->text('temp_password_enc')->nullable();    // terenkripsi; dihapus setelah diserahkan ke app
            $table->unsignedBigInteger('decided_by')->nullable(); // id admin (tabel `user`)
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};
