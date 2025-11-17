# 🚀 Guide de Migration - Backend Find

## ⚠️ IMPORTANT: Lisez ceci avant de migrer!

Cette migration va **modifier significativement votre base de données**. Elle ajoute:
- 10 nouvelles tables
- 40+ nouvelles colonnes aux tables existantes
- 40+ nouveaux indexes
- Soft deletes sur 3 tables existantes

**Sauvegardez votre base de données avant de continuer!**

---

## 📋 Prérequis

### 1. Vérifier la version de PHP

```bash
php -v
```

**Requis**: PHP >= 8.2.0
**Actuel**: PHP 8.1.33 (⚠️ TROP ANCIEN)

#### Solution: Mettre à jour PHP

**Ubuntu/Debian**:
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip
```

**Ou utiliser Docker** (recommandé pour dev):
```bash
docker run -v $(pwd):/app -w /app php:8.2-cli php artisan migrate
```

### 2. Vérifier Composer

```bash
composer --version
```

---

## 🔄 Étape 1: Sauvegarder la base de données

### Pour SQLite (actuel)

```bash
# Copier le fichier de base de données
cp database/database.sqlite database/database.sqlite.backup
```

### Pour MySQL

```bash
# Dump de la base
mysqldump -u root -p find > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 📦 Étape 2: Vérifier les migrations

```bash
# Lister toutes les migrations
ls -la database/migrations/

# Vérifier le statut
php artisan migrate:status
```

Vous devriez voir 16 migrations au total:
- 6 migrations existantes
- 10 nouvelles migrations (2025_11_17_*)

---

## ✅ Étape 3: Exécuter les migrations

### Option A: Migration complète (Recommandé)

```bash
# Migrer toutes les nouvelles migrations
php artisan migrate

# Vérifier le résultat
php artisan migrate:status
```

### Option B: Migration pas à pas (pour tester)

```bash
# Migrer une par une
php artisan migrate --step

# En cas d'erreur, rollback de la dernière migration
php artisan migrate:rollback --step=1
```

### Option C: Refresh complet (⚠️ DESTRUCTIF - perte de données!)

```bash
# ATTENTION: Cela va supprimer toutes les données!
php artisan migrate:fresh

# Avec seeders (si vous les avez créés)
php artisan migrate:fresh --seed
```

---

## 🧪 Étape 4: Vérifier la migration

### 1. Vérifier les tables créées

```bash
php artisan tinker
```

```php
// Dans tinker:
DB::select('SHOW TABLES'); // MySQL
// ou
DB::select("SELECT name FROM sqlite_master WHERE type='table'"); // SQLite

// Vous devriez voir:
// - categories
// - category_signalement
// - media
// - notifications
// - comments
// - reports
// - favorites
// - activity_logs
```

### 2. Vérifier les colonnes ajoutées

```php
// Dans tinker:
Schema::getColumnListing('signalements');
// Devrait inclure: latitude, longitude, city, views, shares, etc.

Schema::getColumnListing('users');
// Devrait inclure: is_verified, bio, city, reputation_score, etc.
```

### 3. Tester les modèles

```php
// Dans tinker:

// Créer une catégorie
$category = \App\Modules\Category\Models\Category::create([
    'name' => 'Téléphones',
    'description' => 'Téléphones perdus',
    'color' => '#3B82F6',
    'icon' => 'phone'
]);

// Créer un signalement avec géolocalisation
$user = \App\Modules\User\Models\User::first();
$signalement = \App\Modules\Signalement\Models\Signalement::create([
    'type' => 'objet',
    'title' => 'iPhone perdu',
    'description' => 'iPhone 13 Pro noir perdu',
    'date_loss' => now(),
    'location' => 'Dakar',
    'latitude' => 14.6937,
    'longitude' => -17.4441,
    'city' => 'Dakar',
    'status' => 'en_cours',
    'user_id' => $user->id,
]);

// Attacher une catégorie
$signalement->categories()->attach($category->id);

// Tester la relation
$signalement->categories; // Devrait retourner la catégorie

// Créer un commentaire
$comment = \App\Modules\Comment\Models\Comment::create([
    'signalement_id' => $signalement->id,
    'user_id' => $user->id,
    'content' => 'Je l\'ai peut-être vu!',
]);

// Créer une notification
$notification = \App\Modules\Notification\Models\Notification::create([
    'user_id' => $user->id,
    'type' => 'message',
    'title' => 'Test notification',
    'message' => 'Ceci est un test',
]);

// Ajouter aux favoris
$favorite = \App\Modules\Favorite\Models\Favorite::create([
    'user_id' => $user->id,
    'signalement_id' => $signalement->id,
]);

// Tester les accessors
$signalement->is_favorited; // true
$signalement->favorites_count; // 1

// Tester la recherche par proximité
$nearby = \App\Modules\Signalement\Models\Signalement::nearby(14.6937, -17.4441, 10)->get();
```

---

## 🔍 Étape 5: Vérifier les indexes

```bash
php artisan tinker
```

```php
// Vérifier les indexes sur signalements
DB::select("PRAGMA index_list('signalements')"); // SQLite
// ou
DB::select("SHOW INDEX FROM signalements"); // MySQL
```

Vous devriez voir des indexes sur:
- latitude, longitude
- city
- status
- type
- is_approved
- created_at
- user_id

---

## 🐛 Résolution de problèmes

### Erreur: "Syntax error or access violation"

**Cause**: Version de PHP trop ancienne ou problème de syntaxe

**Solution**:
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Vérifier PHP
php -v

# Mettre à jour PHP vers 8.2+
```

### Erreur: "Table already exists"

**Cause**: Migration déjà exécutée

**Solution**:
```bash
# Vérifier le statut
php artisan migrate:status

# Rollback si nécessaire
php artisan migrate:rollback

# Re-migrer
php artisan migrate
```

### Erreur: "Class not found"

**Cause**: Autoload pas à jour

**Solution**:
```bash
# Regénérer l'autoload
composer dump-autoload

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Erreur: "Foreign key constraint"

**Cause**: Ordre des migrations

**Solution**:
Les migrations sont numérotées correctement. Si problème:
```bash
# Rollback tout
php artisan migrate:rollback --all

# Re-migrer
php artisan migrate
```

### Base de données corrompue

**Restaurer depuis le backup**:

```bash
# SQLite
cp database/database.sqlite.backup database/database.sqlite

# MySQL
mysql -u root -p find < backup_YYYYMMDD_HHMMSS.sql
```

---

## 📊 Vérification post-migration

### Checklist

- [ ] Toutes les migrations sont exécutées (php artisan migrate:status)
- [ ] 10 nouvelles tables créées
- [ ] Signalements a les nouvelles colonnes (latitude, longitude, etc.)
- [ ] Users a les nouvelles colonnes (is_verified, bio, etc.)
- [ ] Messages a deleted_at (soft delete)
- [ ] Peut créer une catégorie
- [ ] Peut créer un signalement avec géolocalisation
- [ ] Peut créer un commentaire
- [ ] Peut créer une notification
- [ ] Peut créer un favori
- [ ] Les relations fonctionnent
- [ ] Les scopes fonctionnent
- [ ] Les accessors fonctionnent

---

## 🔄 Rollback en cas de problème

### Rollback de toutes les nouvelles migrations

```bash
# Rollback des 10 dernières migrations
php artisan migrate:rollback --step=10

# Vérifier
php artisan migrate:status
```

### Rollback complet

```bash
# ATTENTION: Cela va supprimer toutes les tables!
php artisan migrate:reset

# Restaurer depuis backup
cp database/database.sqlite.backup database/database.sqlite
```

---

## 📁 Configuration du Storage

Pour que les uploads de médias fonctionnent:

```bash
# Créer le lien symbolique
php artisan storage:link

# Vérifier
ls -la public/storage
```

---

## 🎯 Prochaines étapes après migration

1. **Créer un utilisateur admin**
```bash
php artisan tinker
```

```php
$admin = \App\Modules\User\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@find.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'is_verified' => true,
]);
```

2. **Créer des catégories de base**
```php
$categories = [
    ['name' => 'Électronique', 'icon' => 'phone', 'color' => '#3B82F6'],
    ['name' => 'Documents', 'icon' => 'file', 'color' => '#EF4444'],
    ['name' => 'Vêtements', 'icon' => 'shirt', 'color' => '#10B981'],
    ['name' => 'Sacs', 'icon' => 'bag', 'color' => '#F59E0B'],
    ['name' => 'Clés', 'icon' => 'key', 'color' => '#8B5CF6'],
    ['name' => 'Animaux', 'icon' => 'paw', 'color' => '#EC4899'],
    ['name' => 'Personnes', 'icon' => 'user', 'color' => '#6366F1'],
];

foreach ($categories as $cat) {
    \App\Modules\Category\Models\Category::create($cat);
}
```

3. **Tester avec l'API**
- Utiliser Insomnia avec la collection existante
- Tester les nouveaux endpoints (quand les controllers seront créés)

---

## 📚 Ressources

- **Documentation complète**: `BACKEND_COMPLETE_SUMMARY.md`
- **Plan du backend**: `BACKEND_PLAN.md`
- **Progression**: `PROGRESS.md`
- **API docs**: `API_DOCUMENTATION.md`

---

## 🆘 Besoin d'aide?

Si vous rencontrez des problèmes:

1. Vérifiez les logs: `storage/logs/laravel.log`
2. Vérifiez la version PHP: `php -v`
3. Vérifiez les migrations: `php artisan migrate:status`
4. Testez les modèles dans tinker: `php artisan tinker`
5. Consultez la documentation des modèles dans `app/Modules/*/Models/`

---

**Bonne migration! 🚀**

**Date**: 2025-11-17
**Version**: 1.0-beta
