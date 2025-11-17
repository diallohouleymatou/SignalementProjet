<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signalement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_approved')->default(true);
            $table->integer('likes')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('signalement_id');
            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
