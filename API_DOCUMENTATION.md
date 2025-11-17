# API Documentation - Find (Signalement)

## 📋 Vue d'ensemble

API REST pour l'application de signalement d'objets et personnes perdus. L'API utilise JWT (JSON Web Tokens) pour l'authentification.

## 🚀 Démarrage rapide

### Prérequis
- PHP >= 8.2
- Composer
- SQLite ou MySQL

### Installation

1. Installer les dépendances
```bash
composer install
```

2. Configurer l'environnement
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. Créer la base de données
```bash
touch database/database.sqlite
php artisan migrate
```

4. Démarrer le serveur
```bash
php artisan serve
```

Le serveur démarre sur `http://localhost:8000`

## 📦 Importer la collection Insomnia

1. Ouvrir Insomnia
2. Cliquer sur **Application** > **Preferences** > **Data** > **Import Data**
3. Sélectionner le fichier `insomnia_collection.json`
4. La collection "Find - Signalement API" sera importée avec tous les endpoints

### Configuration de l'environnement

Après l'import, configurer les variables d'environnement :

- `base_url` : URL de base de l'API (défaut: `http://localhost:8000`)
- `jwt_token` : Token JWT obtenu après connexion (sera rempli automatiquement)

## 🔐 Authentication

### Workflow d'authentification

1. **Register** - Créer un nouveau compte
2. **Login** - Se connecter et obtenir un token JWT
3. Copier le token JWT dans la variable d'environnement `jwt_token`
4. Utiliser les endpoints protégés avec le token

### Endpoints publics (sans authentification)

#### POST /api/register
Créer un nouveau compte utilisateur.

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2025-11-17T00:00:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### POST /api/login
Se connecter et obtenir un token JWT.

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Endpoints protégés (nécessitent un token JWT)

Tous les endpoints suivants nécessitent le header:
```
Authorization: Bearer {jwt_token}
```

#### GET /api/me
Obtenir les informations de l'utilisateur connecté.

#### POST /api/logout
Se déconnecter (invalide le token).

#### POST /api/refresh
Rafraîchir le token JWT.

## 👤 Users

### GET /api/users
Obtenir la liste de tous les utilisateurs (pour rechercher des contacts).

### GET /api/users/profile
Obtenir son propre profil.

### POST /api/users/profile
Mettre à jour son profil.

**Body (JSON ou multipart/form-data):**
```json
{
  "name": "John Doe Updated",
  "phone": "+221771234567",
  "profile_photo": "file"
}
```

**Note:** Pour l'upload de photo, utiliser `multipart/form-data`.

### GET /api/users/statistics
Obtenir les statistiques de l'utilisateur connecté.

**Response:**
```json
{
  "total_signalements": 5,
  "signalements_en_cours": 3,
  "signalements_retrouves": 2,
  "signalements_faux": 0,
  "objets_signales": 4,
  "personnes_signalees": 1,
  "messages_sent": 10,
  "messages_received": 8,
  "unread_messages": 2
}
```

### GET /api/users/{id}
Obtenir un utilisateur spécifique par ID.

## 📢 Signalements

### GET /api/signalements
Obtenir tous les signalements (fil public).

### GET /api/signalements/my
Obtenir les signalements de l'utilisateur connecté.

### POST /api/signalements
Créer un nouveau signalement.

**Body:**
```json
{
  "type": "objet",
  "title": "iPhone 13 Pro perdu",
  "description": "J'ai perdu mon iPhone 13 Pro noir dans le bus ligne 8",
  "date_loss": "2025-11-16",
  "location": "Bus ligne 8, Dakar",
  "status": "en_cours",
  "photos": ["url1", "url2"]
}
```

**Validation:**
- `type`: required, in: `objet`, `personne`
- `title`: required, string, max: 255
- `description`: required, string
- `date_loss`: required, date
- `location`: required, string
- `status`: required, in: `en_cours`, `retrouve`, `faux`
- `photos`: optional, array of strings

### GET /api/signalements/{id}
Obtenir un signalement spécifique.

### PUT /api/signalements/{id}
Mettre à jour un signalement (propriétaire ou admin uniquement).

### DELETE /api/signalements/{id}
Supprimer un signalement (propriétaire ou admin uniquement).

### GET /api/signalements/search
Rechercher des signalements avec filtres.

**Query Parameters:**
- `type`: `objet` ou `personne`
- `status`: `en_cours`, `retrouve`, `faux`
- `search`: recherche dans title, description, location
- `date_from`: date de début (format: YYYY-MM-DD)
- `date_to`: date de fin (format: YYYY-MM-DD)

**Example:**
```
GET /api/signalements/search?type=objet&status=en_cours&search=iPhone
```

### GET /api/signalements/status/{status}
Obtenir les signalements par statut.

**Example:**
```
GET /api/signalements/status/en_cours
```

### GET /api/signalements/type/{type}
Obtenir les signalements par type.

**Example:**
```
GET /api/signalements/type/objet
```

## 💬 Messages

### POST /api/messages
Envoyer un nouveau message.

**Body:**
```json
{
  "signalement_id": 1,
  "receiver_id": 2,
  "content": "Bonjour, j'ai trouvé votre iPhone!"
}
```

**Validation:**
- `signalement_id`: required, exists in signalements table
- `receiver_id`: required, exists in users table
- `content`: required, string, max: 2000

### GET /api/messages/conversations
Obtenir toutes les conversations de l'utilisateur connecté.

**Response:**
```json
{
  "conversations": [
    {
      "partner": {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com"
      },
      "last_message": {
        "id": 10,
        "content": "Merci!",
        "created_at": "2025-11-17T00:00:00.000000Z"
      },
      "unread_count": 2
    }
  ]
}
```

### GET /api/messages/unread-count
Obtenir le nombre de messages non lus.

**Response:**
```json
{
  "unread_count": 5
}
```

### GET /api/messages/signalement/{signalement_id}
Obtenir tous les messages d'un signalement.

### GET /api/messages/conversation/{signalement_id}/{user_id}
Obtenir la conversation avec un utilisateur spécifique concernant un signalement.

### PUT /api/messages/{id}/read
Marquer un message comme lu.

### PUT /api/messages/signalement/{signalement_id}/read-all
Marquer tous les messages d'un signalement comme lus.

## 📊 Codes de statut HTTP

- `200` - OK
- `201` - Created
- `400` - Bad Request (validation error)
- `401` - Unauthorized (token invalide ou manquant)
- `403` - Forbidden (pas de permission)
- `404` - Not Found
- `422` - Unprocessable Entity (validation error)
- `500` - Internal Server Error

## 🔒 Autorizations

### Signalements
- **View**: Tous les utilisateurs authentifiés
- **Create**: Tous les utilisateurs authentifiés
- **Update**: Propriétaire ou Admin
- **Delete**: Propriétaire ou Admin

### Messages
- **Send**: Tous les utilisateurs authentifiés
- **View**: Tous les utilisateurs authentifiés
- **Mark as read**: Destinataire uniquement

## 🎯 Exemples d'utilisation

### Workflow complet

1. **Créer un compte**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

2. **Se connecter**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

3. **Créer un signalement**
```bash
curl -X POST http://localhost:8000/api/signalements \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "type": "objet",
    "title": "iPhone perdu",
    "description": "iPhone 13 Pro noir",
    "date_loss": "2025-11-16",
    "location": "Dakar",
    "status": "en_cours"
  }'
```

4. **Rechercher des signalements**
```bash
curl -X GET "http://localhost:8000/api/signalements/search?type=objet&search=iPhone" \
  -H "Authorization: Bearer {token}"
```

5. **Envoyer un message**
```bash
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "signalement_id": 1,
    "receiver_id": 2,
    "content": "J ai trouvé votre objet!"
  }'
```

## 🛠️ Développement

### Structure du projet
```
app/
├── Modules/
│   ├── User/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Signalement/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Requests/
│   │   └── Resources/
│   └── Message/
│       ├── Controllers/
│       ├── Models/
│       ├── Requests/
│       └── Resources/
├── Policies/
│   └── SignalementPolicy.php
└── Providers/
    └── AppServiceProvider.php
routes/
└── api.php
```

### Tester l'API

Utiliser Insomnia, Postman, ou curl pour tester les endpoints.

## 📝 Notes importantes

1. **Token JWT**: Le token JWT expire après 60 minutes par défaut. Utiliser `/api/refresh` pour le renouveler.

2. **Photos**: Les photos de profil sont stockées dans `storage/profile_photos/`. Pour les signalements, les photos sont stockées en JSON dans la base de données.

3. **Validation**: Toutes les entrées sont validées. Les erreurs de validation retournent un code 422 avec les détails.

4. **CORS**: Si vous utilisez un frontend séparé, configurez CORS dans `config/cors.php`.

5. **Base de données**: Par défaut, SQLite est utilisé. Pour MySQL/PostgreSQL, modifier `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=find
DB_USERNAME=root
DB_PASSWORD=
```

## 🔧 Dépannage

### Erreur "Token not provided"
Vérifier que le header `Authorization: Bearer {token}` est présent.

### Erreur "Token invalid"
Le token a expiré ou est invalide. Se reconnecter ou utiliser `/api/refresh`.

### Erreur 403 Forbidden
Vous n'avez pas la permission pour cette action (ex: modifier un signalement d'un autre utilisateur).

### Erreur de validation
Vérifier que tous les champs requis sont présents et valides.

## 📞 Support

Pour toute question ou problème, créer une issue sur le repository GitHub du projet.

---

**Version**: 1.0.0
**Date**: 2025-11-17
**Framework**: Laravel 12.0
**Authentication**: JWT (tymon/jwt-auth)
