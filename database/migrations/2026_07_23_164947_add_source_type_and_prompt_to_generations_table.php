<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('generations', function (Blueprint $table) {
        $table->string('source_type')->default('image')->after('user_id');
        $table->text('prompt')->nullable()->after('source_type');
    });
}

public function down()
{
    Schema::table('generations', function (Blueprint $table) {
        $table->dropColumn(['source_type', 'prompt']);
    });
}
};
