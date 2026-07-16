# STF — API Laravel

API REST pour la plateforme STF (site public, espaces mentée/mentore, back-office), consommée par les deux apps Next.js du monorepo (`apps/site`, `apps/backoffice`).

## Stack

- Laravel 13 / PHP 8.3
- **MySQL** (base `stf`, via Laragon — `DB_HOST=127.0.0.1`, `DB_USERNAME=root`, pas de mot de passe en local)
- **Laravel Sanctum** : auth par jeton (Bearer token), pas de cookies/CSRF — adapté à deux SPA sur des origines différentes
- **spatie/laravel-permission** : rôles (`admin`, `staff`, `mentor`, `mentee`, `donor`) et permissions
- **pragmarx/google2fa-laravel** + **bacon/bacon-qr-code** : double authentification (TOTP) pour les comptes Administratrice/Collaboratrice STF

## Démarrage

```bash
composer install
php artisan key:generate
# Créer la base MySQL si besoin :
#   mysql -u root -e "CREATE DATABASE IF NOT EXISTS stf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
php artisan serve       # http://127.0.0.1:8000
```

`CORS_ALLOWED_ORIGINS` dans `.env` doit lister les origines des deux apps Next.js (par défaut `http://localhost:3000,http://localhost:3001`).

## Comptes de démonstration (mot de passe : `password`)

| Email | Rôle | Statut |
|---|---|---|
| admin@stf-organisation.org | admin | actif |
| staff@stf-organisation.org | staff | actif |
| fatou.konate@example.org | mentor | validée |
| kadidia.traore@example.org | mentor | validée |
| sarah.nguessan@example.org | mentor | en attente de validation |
| aicha.diallo@example.org | mentee | actif |
| mariam.sow@example.org | mentee | actif |
| ndeye.fall@example.org | mentee | en attente de binôme |
| contact@fondation-numerique.org | donor | actif |

## Authentification

```
POST /api/auth/register   { name, email, password, password_confirmation, role: mentee|mentor|donor, ... }
POST /api/auth/login      { email, password } → { token } ou { mfa_required: true, mfa_challenge }
POST /api/auth/mfa/verify { mfa_challenge, code } → { token }
GET  /api/auth/me         (Bearer token)
POST /api/auth/logout
POST /api/auth/mfa/setup    → { secret, otpauth_url, qr_code_svg }
POST /api/auth/mfa/confirm  { code } → { recovery_codes }
POST /api/auth/mfa/disable  { password }
```

Toutes les routes protégées attendent `Authorization: Bearer <token>`.

## Ressources principales

`programs`, `cohorts`, `pairings` (binômes de mentorat), `sessions` (+ `notes`), `modules` (+ `quizzes`, `progress`), `badges`, `certificates`, `projects`, `groups` (+ `posts`, `comments`, `files`), `conversations` (+ `messages`), `reports` (signalements), `audit-logs`, `users`, ainsi que le contenu éditorial (`cms/pages`, `partners`, `testimonials`, `faqs`) et les statistiques (`dashboard/*`, `stats/impact`).

L'autorisation combine :
- des **permissions** Spatie (`users.manage`, `programs.manage`, `matching.manage`, `groups.manage`, `cms.manage`, …) pour les actions d'administration/back-office ;
- des **policies** Laravel pour les données propres à une utilisatrice (une mentée ne voit que son binôme, ses sessions, ses projets).

## Frontends branchés

`site` et `backoffice` consomment cette API directement (plus de mocks) via un pattern "backend-for-frontend" :
- Chaque app Next.js a son propre `.env.local` (`API_URL=http://127.0.0.1:8000/api`).
- Les appels à l'API se font uniquement côté serveur (Server Components, Server Actions) — le jeton Sanctum est stocké dans un cookie httpOnly (`stf_token` pour `site`, `stf_admin_token` pour `backoffice`), jamais exposé au JS client.
- `src/proxy.ts` (convention Next.js 16, ex-`middleware.ts`) protège les routes `/mentee`, `/mentore` (site) et tout le back-office hors `/connexion`.

## Prochaines étapes

- Ajouter le stockage de fichiers (certificats, fichiers de groupe) sur un disque S3-compatible en production.
- Ajouter des tests Feature pour les policies et le flux MFA.
- Générer réellement les exports CSV/PDF de la page Reporting (actuellement des indicateurs réels mais des boutons d'export désactivés).
# STF-BACKEND
