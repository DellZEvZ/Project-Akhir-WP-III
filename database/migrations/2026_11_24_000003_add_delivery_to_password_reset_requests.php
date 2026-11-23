<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jejak penelusuran: kapan dan dari mana password sementara diambil oleh app.
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivered_ip', 45)->nullable();
            $table->string('delivered_user_agent', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'delivered_ip', 'delivered_user_agent']);
        });
    }
};
