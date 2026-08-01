<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->string('original_image')->nullable()->change();
            $table->string('thumbnail')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->string('original_image')->nullable(false)->change();
            $table->string('thumbnail')->nullable(false)->change();
        });
    }
};