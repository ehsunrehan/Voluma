<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('generations', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->string('original_image');

        $table->string('removed_background')->nullable();

        $table->string('glb_file')->nullable();

        $table->string('preview_image')->nullable();

        $table->string('tripo_task_id')->nullable();

        $table->enum('status',[
            'uploaded',
            'processing',
            'completed',
            'failed'
        ])->default('uploaded');

        $table->integer('credits_used')->default(10);

        $table->integer('downloads')->default(0);

        $table->integer('renew_count')->default(0);

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
