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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title',100);
            $table->string('slug',100)->unique();
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('type',['free','premium'])->default('free');
            $table->integer('price')->default(0)->nullable();
            $table->boolean('is_published')->default(false);
            // Jika level dihapus, tabel course tetap masih ada
            $table->foreignId('level_id')->constrained('levels')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
