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
            $table->dropColumn('updated_by');
        });

         Schema::table('page_config', function (Blueprint $table) {
           $table->unsignedBigInteger('updated_by');
    
           $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_config', function (Blueprint $table) {
           $table->dropForeign(['updated_by']);  
           $table->dropColumn('updated_by');  
        });

         Schema::table('page_config', function (Blueprint $table) {
           $table->string('updated_by')->nullable();  
        });
    }
};
