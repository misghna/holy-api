<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('page_config', function (Blueprint $table) {
            $table->dropColumn('header_img');
            $table->dropColumn('img_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_config', function (Blueprint $table) {
            $table->string('header_img')->nullable();
            $table->string('img_link')->nullable();
        });
    }
};
