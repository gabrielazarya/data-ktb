<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('follow_up_tasks')) {
            return;
        }

        Schema::create('follow_up_tasks', function (Blueprint $table): void {
            $table->id('follow_up_id');
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('laporan_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('title', 255);
            $table->string('reason', 64)->nullable();
            $table->text('description')->nullable();
            $table->date('due_at')->nullable();
            $table->string('status', 24)->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('kelompok_id')
                ->references('kelompok_id')
                ->on('kelompok_pemuridan')
                ->nullOnDelete();
            $table->foreign('laporan_id')
                ->references('laporan_id')
                ->on('laporan_pertemuan_kelompok')
                ->nullOnDelete();
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('assigned_to')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('completed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['kelompok_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['due_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_tasks');
    }
};
