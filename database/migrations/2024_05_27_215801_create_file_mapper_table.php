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
        Schema::create('file_mapper', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('ref_id'); // General reference ID
            $table->string('ref_type'); // Type of reference, e.g., 'page_config'
            $table->unsignedBigInteger('file_id'); 
            $table->timestamps();
            $table->unsignedBigInteger('updated_by'); 

            // Foreign keys
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_mapper');
    }
};
