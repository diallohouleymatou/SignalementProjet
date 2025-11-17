<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            // Géolocalisation
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('city')->nullable()->after('longitude');
            $table->string('country')->default('Senegal')->after('city');

            // Statistiques
            $table->integer('views')->default(0)->after('status');
            $table->integer('shares')->default(0)->after('views');

            // Récompense
            $table->decimal('reward_amount', 10, 2)->nullable()->after('shares');
            $table->string('reward_currency')->default('XOF')->after('reward_amount');

            // Contact
            $table->string('contact_phone')->nullable()->after('reward_currency');
            $table->string('contact_email')->nullable()->after('contact_phone');

            // Modération
            $table->boolean('is_approved')->default(true)->after('contact_email');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_at');

            // Soft deletes
            $table->softDeletes();

            // Indexes pour améliorer les performances
            $table->index(['latitude', 'longitude']);
            $table->index('city');
            $table->index('status');
            $table->index('type');
            $table->index('is_approved');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropIndex(['city']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['created_at']);

            $table->dropSoftDeletes();
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'latitude', 'longitude', 'city', 'country',
                'views', 'shares',
                'reward_amount', 'reward_currency',
                'contact_phone', 'contact_email',
                'is_approved', 'approved_at', 'approved_by'
            ]);
        });
    }
};
