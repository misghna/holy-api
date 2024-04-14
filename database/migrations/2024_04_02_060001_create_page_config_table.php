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
        Schema::create('page_config', function (Blueprint $table) {
            $table->id();
            $table->enum('page_type', ['Text','Image','Video','Donate'])->default('Text');
            $table->string('name');
            $table->text('description'); // A text column (nullable)
            $table->string('img_link');
            $table->string('parent');
            $table->string('header_img');
            $table->string('header_text');
            $table->string('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_config');
    }
};
