<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Root OpenAPI document metadata (info, servers, security scheme, tags).
 * Holds no code — swagger-php discovers it purely from the attributes below.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'STF — API',
    description: "API REST de la plateforme STF (Sciences & Technologies au Féminin) : site public, espaces mentée/mentore et back-office.\n\n"
        ."**Authentification** : jeton Bearer (Laravel Sanctum). Connectez-vous via `POST /auth/login` (ou `/auth/register`), "
        ."puis cliquez sur *Authorize* ci-dessus et collez le `token` reçu (sans le préfixe `Bearer`).\n\n"
        .'Les comptes admin/staff peuvent avoir la double authentification (TOTP) activée : `POST /auth/login` renvoie alors '
        ."`{ mfa_required: true, mfa_challenge }` au lieu d'un jeton ; complétez avec `POST /auth/mfa/verify`.",
    contact: new OA\Contact(email: 'contact@stf-organisation.org')
)]
#[OA\Server(url: 'http://127.0.0.1:8000/api', description: 'Développement local')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum personal access token',
)]
#[OA\Tag(name: 'Auth', description: "Inscription, connexion, double authentification, session courante")]
#[OA\Tag(name: 'Utilisatrices', description: 'Administration des comptes (admin/staff)')]
#[OA\Tag(name: 'Rôles', description: 'Rôles et permissions RBAC')]
#[OA\Tag(name: 'Programmes', description: 'Programmes de mentorat')]
#[OA\Tag(name: 'Cohortes', description: 'Cohortes rattachées à un programme')]
#[OA\Tag(name: 'Matching', description: 'Suggestions de matching mentée/mentore')]
#[OA\Tag(name: 'Binômes', description: 'Binômes de mentorat (mentorship_pairings)')]
#[OA\Tag(name: 'Sessions', description: 'Sessions de mentorat et leurs notes')]
#[OA\Tag(name: 'Modules', description: 'Modules pédagogiques, progression et quiz')]
#[OA\Tag(name: 'Badges', description: "Badges et attribution")]
#[OA\Tag(name: 'Certificats', description: 'Certificats délivrés')]
#[OA\Tag(name: 'Projets', description: 'Projets déposés par les mentées')]
#[OA\Tag(name: 'Groupes', description: 'Groupes, membres, publications, commentaires, fichiers')]
#[OA\Tag(name: 'Messagerie', description: 'Conversations et messages')]
#[OA\Tag(name: 'Signalements', description: 'Signalements et modération')]
#[OA\Tag(name: "Journaux d'audit", description: 'Historique des actions sensibles')]
#[OA\Tag(name: 'CMS', description: 'Pages et articles du site public')]
#[OA\Tag(name: 'Partenaires', description: 'Logos partenaires affichés sur le site public')]
#[OA\Tag(name: 'Témoignages', description: 'Témoignages affichés sur le site public')]
#[OA\Tag(name: 'FAQ', description: 'Questions fréquentes')]
#[OA\Tag(name: 'Contact', description: 'Formulaire de contact public')]
#[OA\Tag(name: 'Tableau de bord', description: 'Indicateurs agrégés pour le back-office')]
#[OA\Tag(name: 'Statistiques', description: "Statistiques d'impact publiques")]
class OpenApiSpec
{
    //
}
