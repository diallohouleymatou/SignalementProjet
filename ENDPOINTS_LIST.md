# Liste complète des Endpoints - API Find

## 🔓 Endpoints publics (sans authentification)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/register` | Créer un nouveau compte |
| POST | `/api/login` | Se connecter et obtenir un token JWT |

---

## 🔒 Endpoints protégés (nécessitent un token JWT)

### 👤 Authentication

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/me` | Obtenir les informations de l'utilisateur connecté |
| POST | `/api/logout` | Se déconnecter (invalider le token) |
| POST | `/api/refresh` | Rafraîchir le token JWT |

### 👥 Users

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/users` | Obtenir tous les utilisateurs |
| GET | `/api/users/profile` | Obtenir son propre profil |
| POST | `/api/users/profile` | Mettre à jour son profil |
| GET | `/api/users/statistics` | Obtenir ses statistiques |
| GET | `/api/users/{id}` | Obtenir un utilisateur spécifique |

### 📢 Signalements

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/signalements` | Obtenir tous les signalements |
| GET | `/api/signalements/my` | Obtenir ses propres signalements |
| GET | `/api/signalements/search` | Rechercher des signalements avec filtres |
| GET | `/api/signalements/status/{status}` | Obtenir par statut (en_cours, retrouve, faux) |
| GET | `/api/signalements/type/{type}` | Obtenir par type (objet, personne) |
| GET | `/api/signalements/{id}` | Obtenir un signalement spécifique |
| POST | `/api/signalements` | Créer un nouveau signalement |
| PUT | `/api/signalements/{id}` | Mettre à jour un signalement (propriétaire/admin) |
| DELETE | `/api/signalements/{id}` | Supprimer un signalement (propriétaire/admin) |

### 💬 Messages

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/messages` | Envoyer un nouveau message |
| GET | `/api/messages/conversations` | Obtenir toutes ses conversations |
| GET | `/api/messages/unread-count` | Obtenir le nombre de messages non lus |
| GET | `/api/messages/signalement/{signalement_id}` | Obtenir tous les messages d'un signalement |
| GET | `/api/messages/conversation/{signalement_id}/{user_id}` | Obtenir la conversation avec un utilisateur |
| PUT | `/api/messages/{id}/read` | Marquer un message comme lu |
| PUT | `/api/messages/signalement/{signalement_id}/read-all` | Marquer tous les messages comme lus |

---

## 📊 Résumé

- **Total Endpoints**: 26
  - Publics: 2
  - Protégés: 24
    - Authentication: 3
    - Users: 5
    - Signalements: 9
    - Messages: 7

## 🔑 Authentication

Tous les endpoints protégés nécessitent le header:
```
Authorization: Bearer {jwt_token}
```

## 📋 Paramètres de recherche

### Signalements Search (`/api/signalements/search`)

Query parameters disponibles:
- `type`: `objet` | `personne`
- `status`: `en_cours` | `retrouve` | `faux`
- `search`: Recherche dans title, description, location
- `date_from`: Date de début (YYYY-MM-DD)
- `date_to`: Date de fin (YYYY-MM-DD)

**Exemple**:
```
GET /api/signalements/search?type=objet&status=en_cours&search=iPhone
```

## 📝 Formats de données

### Signalement
```json
{
  "type": "objet|personne",
  "title": "string (max 255)",
  "description": "string",
  "date_loss": "YYYY-MM-DD",
  "location": "string",
  "status": "en_cours|retrouve|faux",
  "photos": ["url1", "url2"]
}
```

### Message
```json
{
  "signalement_id": "integer",
  "receiver_id": "integer",
  "content": "string (max 2000)"
}
```

### User Profile Update
```json
{
  "name": "string (max 255)",
  "phone": "string",
  "profile_photo": "file (image, max 2048KB)"
}
```

## 🎯 Quick Start

1. **Créer un compte**: `POST /api/register`
2. **Se connecter**: `POST /api/login` → Copier le token
3. **Utiliser le token**: Ajouter `Authorization: Bearer {token}` dans les headers
4. **Tester les endpoints**: Utiliser Insomnia avec la collection `insomnia_collection.json`

## 📚 Documentation complète

- **Guide d'utilisation Insomnia**: `INSOMNIA_GUIDE.md`
- **Documentation API complète**: `API_DOCUMENTATION.md`
- **Collection Insomnia**: `insomnia_collection.json`

---

**Version**: 1.0.0
**Date**: 2025-11-17
**Base URL**: `http://localhost:8000`
