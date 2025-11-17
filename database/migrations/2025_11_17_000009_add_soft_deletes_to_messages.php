<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->softDeletes();

            // Ajouter des indexes pour améliorer les performances
            $table->index('signalement_id');
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('read');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['signalement_id']);
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['receiver_id']);
            $table->dropIndex(['read']);
            $table->dropIndex(['created_at']);

            $table->dropSoftDeletes();
        });
    }
};
