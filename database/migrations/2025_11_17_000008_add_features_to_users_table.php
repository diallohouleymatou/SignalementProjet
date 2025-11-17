<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Vérification
            $table->boolean('is_verified')->default(false)->after('email_verified_at');
            $table->boolean('phone_verified')->default(false)->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verified');

            // Profil
            $table->text('bio')->nullable()->after('profile_photo');
            $table->string('city')->nullable()->after('bio');
            $table->string('country')->default('Senegal')->after('city');

            // Statistiques
            $table->integer('reputation_score')->default(0)->after('country');
            $table->integer('total_signalements')->default(0)->after('reputation_score');
            $table->integer('successful_finds')->default(0)->after('total_signalements');

            // Paramètres
            $table->json('notification_settings')->nullable()->after('successful_finds');
            $table->json('privacy_settings')->nullable()->after('notification_settings');

            // Modération
            $table->boolean('is_banned')->default(false)->after('privacy_settings');
            $table->timestamp('banned_at')->nullable()->after('is_banned');
            $table->text('ban_reason')->nullable()->after('banned_at');

            // Dernière activité
            $table->timestamp('last_seen_at')->nullable()->after('ban_reason');

            // Soft deletes
            $table->softDeletes();

            // Indexes
            $table->index('city');
            $table->index('is_verified');
            $table->index('is_banned');
            $table->index('reputation_score');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['is_banned']);
            $table->dropIndex(['reputation_score']);

            $table->dropSoftDeletes();

            $table->dropColumn([
                'is_verified', 'phone_verified', 'phone_verified_at',
                'bio', 'city', 'country',
                'reputation_score', 'total_signalements', 'successful_finds',
                'notification_settings', 'privacy_settings',
                'is_banned', 'banned_at', 'ban_reason',
                'last_seen_at'
            ]);
        });
    }
};
