# Guide d'utilisation - Collection Insomnia

## 🎯 Import de la collection

### Étape 1: Ouvrir Insomnia
Lancez l'application Insomnia Desktop.

### Étape 2: Importer la collection
1. Cliquer sur **Application** (en haut à gauche)
2. Sélectionner **Preferences** ou **Import/Export**
3. Cliquer sur **Import Data**
4. Choisir **From File**
5. Sélectionner le fichier `insomnia_collection.json` à la racine du projet

### Étape 3: Vérification
La collection "Find - Signalement API" devrait apparaître dans votre workspace avec 4 dossiers:
- 1. Authentication (5 endpoints)
- 2. Users (5 endpoints)
- 3. Signalements (9 endpoints)
- 4. Messages (7 endpoints)

**Total: 26 endpoints**

## ⚙️ Configuration de l'environnement

### Variables d'environnement
Après l'import, configurer les variables:

1. Cliquer sur l'icône d'environnement (en haut à gauche)
2. Sélectionner **Base Environment**
3. Modifier les valeurs:

```json
{
  "base_url": "http://localhost:8000",
  "jwt_token": ""
}
```

**Variables:**
- `base_url`: URL de base de votre API Laravel (défaut: http://localhost:8000)
- `jwt_token`: Sera rempli automatiquement après login

## 🚀 Workflow de test

### 1. Démarrer le serveur Laravel

```bash
cd /home/houleymatou-diallo/PhpstormProjects/Find
php artisan serve
```

Le serveur démarre sur `http://localhost:8000`

### 2. Créer un compte (Register)

1. Ouvrir le dossier **1. Authentication**
2. Cliquer sur **Register**
3. Modifier le body JSON avec vos informations:
```json
{
  "name": "Votre Nom",
  "email": "votre.email@example.com",
  "password": "votreMotDePasse123",
  "password_confirmation": "votreMotDePasse123"
}
```
4. Cliquer sur **Send**
5. Vous recevrez un token JWT dans la réponse

### 3. Se connecter (Login)

1. Cliquer sur **Login**
2. Modifier le body:
```json
{
  "email": "votre.email@example.com",
  "password": "votreMotDePasse123"
}
```
3. Cliquer sur **Send**
4. **Copier le token** dans la réponse

### 4. Configurer le token JWT

**Option 1: Manuellement**
1. Copier le token reçu (sans les guillemets)
2. Aller dans l'environnement (icône en haut à gauche)
3. Coller le token dans `jwt_token`

**Option 2: Variable automatique**
Le token est utilisé automatiquement dans tous les endpoints protégés via `{{ _.jwt_token }}`

### 5. Tester les endpoints protégés

Maintenant vous pouvez tester tous les endpoints qui nécessitent une authentification:

#### Users
- **Get My Profile**: Voir votre profil
- **Update My Profile**: Modifier votre profil
- **Get My Statistics**: Voir vos statistiques
- **Get All Users**: Liste des utilisateurs
- **Get Specific User**: Voir un utilisateur (changer l'ID dans l'URL)

#### Signalements
- **Get All Signalements**: Liste publique
- **Get My Signalements**: Vos signalements
- **Create Signalement**: Créer un signalement
- **Get Specific Signalement**: Voir un signalement (changer l'ID)
- **Update Signalement**: Modifier un signalement (propriétaire uniquement)
- **Delete Signalement**: Supprimer un signalement (propriétaire uniquement)
- **Search Signalements**: Rechercher avec filtres
- **Get by Status**: Filtrer par statut (en_cours, retrouve, faux)
- **Get by Type**: Filtrer par type (objet, personne)

#### Messages
- **Send Message**: Envoyer un message
- **Get All Conversations**: Voir toutes vos conversations
- **Get Unread Count**: Nombre de messages non lus
- **Get Messages for Signalement**: Messages d'un signalement
- **Get Conversation With User**: Conversation avec un utilisateur
- **Mark as Read**: Marquer un message comme lu
- **Mark All as Read**: Marquer tous les messages d'un signalement comme lus

## 📝 Exemples de requêtes

### Créer un signalement

**Endpoint**: POST /api/signalements

**Body**:
```json
{
  "type": "objet",
  "title": "iPhone 13 Pro perdu",
  "description": "J'ai perdu mon iPhone 13 Pro noir dans le bus ligne 8 à Dakar. Le téléphone a une coque bleue et un autocollant 'SN' à l'arrière.",
  "date_loss": "2025-11-16",
  "location": "Bus ligne 8, arrêt Liberté 6, Dakar",
  "status": "en_cours"
}
```

**Types valides**:
- `objet`: Pour les objets perdus
- `personne`: Pour les personnes disparues

**Status valides**:
- `en_cours`: Recherche en cours
- `retrouve`: Objet/personne retrouvé(e)
- `faux`: Fausse alerte

### Rechercher des signalements

**Endpoint**: GET /api/signalements/search

**Query Parameters**:
```
?type=objet&status=en_cours&search=iPhone&date_from=2025-11-01&date_to=2025-11-30
```

Vous pouvez combiner plusieurs filtres:
- `type`: objet ou personne
- `status`: en_cours, retrouve, faux
- `search`: recherche dans titre, description, location
- `date_from`: date de début (YYYY-MM-DD)
- `date_to`: date de fin (YYYY-MM-DD)

### Envoyer un message

**Endpoint**: POST /api/messages

**Body**:
```json
{
  "signalement_id": 1,
  "receiver_id": 2,
  "content": "Bonjour, je pense avoir trouvé votre iPhone. Pouvez-vous me donner plus de détails pour vérifier?"
}
```

**Note**: Remplacer `signalement_id` et `receiver_id` par les IDs réels.

## 🔧 Tips et astuces

### 1. Utiliser les variables d'environnement
Utilisez `{{ _.base_url }}` et `{{ _.jwt_token }}` dans vos requêtes pour faciliter le changement d'environnement.

### 2. Dupliquer les requêtes
Clic droit sur une requête > Duplicate pour créer des variantes (ex: tester différents scénarios).

### 3. Organiser vos tests
Créez des dossiers supplémentaires pour organiser vos tests personnalisés.

### 4. Historique des requêtes
Insomnia garde un historique de toutes vos requêtes. Utilisez Ctrl+H (ou Cmd+H sur Mac) pour y accéder.

### 5. Génération de code
Insomnia peut générer du code pour différents langages (curl, JavaScript, Python, etc.). Cliquez sur le bouton "Generate Code" en haut à droite.

### 6. Variables dynamiques
Dans Insomnia, vous pouvez utiliser:
- `{{ _.variable_name }}`: Variables d'environnement
- `{% now 'iso-8601' %}`: Date actuelle
- `{% uuid 'v4' %}`: UUID aléatoire

## ⚠️ Erreurs courantes

### Erreur: "Token not provided"
- **Cause**: Le token JWT n'est pas configuré
- **Solution**: Vérifier que `jwt_token` est bien rempli dans l'environnement

### Erreur: "Token invalid" ou "Token expired"
- **Cause**: Le token a expiré (durée: 60 minutes)
- **Solution**: Se reconnecter ou utiliser l'endpoint **Refresh Token**

### Erreur: 403 Forbidden
- **Cause**: Vous n'avez pas la permission (ex: modifier un signalement d'un autre utilisateur)
- **Solution**: Utiliser un compte avec les bonnes permissions

### Erreur: 422 Validation Error
- **Cause**: Les données envoyées ne respectent pas les règles de validation
- **Solution**: Vérifier le body et corriger les erreurs (détails dans la réponse)

### Erreur: 404 Not Found
- **Cause**: L'endpoint ou la ressource n'existe pas
- **Solution**: Vérifier l'URL et l'ID de la ressource

## 📊 Structure des réponses

### Success Response (200/201)
```json
{
  "data": {
    "id": 1,
    "title": "...",
    ...
  }
}
```

### Error Response (400/422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### Authentication Error (401)
```json
{
  "message": "Unauthenticated."
}
```

## 🎓 Ressources supplémentaires

- **Documentation complète**: Voir `API_DOCUMENTATION.md`
- **Code source**: Explorer les controllers dans `app/Modules/*/Controllers/`
- **Routes**: Voir `routes/api.php`

## 💡 Besoin d'aide?

1. Consulter `API_DOCUMENTATION.md` pour plus de détails
2. Vérifier les logs Laravel: `storage/logs/laravel.log`
3. Utiliser `php artisan route:list` pour voir toutes les routes
4. Activer le mode debug dans `.env`: `APP_DEBUG=true`

---

**Bon test! 🚀**

Si vous rencontrez des problèmes, vérifiez d'abord que:
- ✅ Le serveur Laravel est démarré
- ✅ La base de données est configurée et migrée
- ✅ Le token JWT est valide et configuré
- ✅ Les données envoyées sont au bon format
