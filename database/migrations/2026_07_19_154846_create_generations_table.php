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

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('task_id')->unique();

        $table->string('original_image');

        $table->string('thumbnail')->nullable();

        $table->text('tripo_url')->nullable();

        $table->string('local_glb')->nullable();

        $table->text('glb_url')->nullable();

        $table->string('status')->default('processing');

        $table->integer('credits_used')->default(30);

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
