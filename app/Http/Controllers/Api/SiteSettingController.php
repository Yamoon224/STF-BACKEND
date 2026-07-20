<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SiteSettingController extends Controller
{
    /** The fixed set of editable settings keys. */
    public const KEYS = [
        'address',
        'phone',
        'email_primary',
        'email_secondary',
        'site_url',
        'social_linkedin',
        'social_facebook',
        'social_instagram',
        'social_youtube',
        'social_x',
    ];

    #[OA\Get(
        path: '/site-settings',
        summary: 'Lister les paramètres du site (contact, réseaux sociaux)',
        description: 'Public. Retourne un objet clé/valeur.',
        tags: ['Paramètres du site'],
        responses: [new OA\Response(response: 200, description: 'Paramètres', content: new OA\JsonContent(type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'string', nullable: true)))]
    )]
    public function index()
    {
        return SiteSetting::query()->pluck('value', 'key');
    }

    #[OA\Patch(
        path: '/site-settings',
        summary: 'Modifier les paramètres du site',
        description: 'Met à jour une ou plusieurs clés connues (adresse, téléphone, emails, réseaux sociaux).',
        security: [['bearerAuth' => []]],
        tags: ['Paramètres du site'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'string', nullable: true))
        ),
        responses: [
            new OA\Response(response: 200, description: 'Paramètres mis à jour', content: new OA\JsonContent(type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'string', nullable: true))),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate(
            collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => ['sometimes', 'nullable', 'string', 'max:2000']])->all()
        );

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return SiteSetting::query()->pluck('value', 'key');
    }
}
