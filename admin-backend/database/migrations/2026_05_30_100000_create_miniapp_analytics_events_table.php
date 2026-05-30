<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('miniapp_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page', 32)->index();
            $table->string('category', 32)->index();
            $table->string('event_name', 64)->index();
            $table->string('event_value', 255)->nullable();
            $table->json('meta')->nullable();
            $table->string('client_session_id', 64)->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['page', 'event_name', 'created_at']);
            $table->index(['event_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniapp_analytics_events');
    }
};
