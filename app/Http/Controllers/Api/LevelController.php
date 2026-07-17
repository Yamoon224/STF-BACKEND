<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use OpenApi\Attributes as OA;

class LevelController extends Controller
{
    #[OA\Get(
        path: '/levels',
        summary: 'Lister les niveaux scolaires (cours de renforcement)',
        description: 'Public. Les 4 paliers STF : 6e & 5e, 4e & 3e, 2nde & 1re, Terminale C & D.',
        tags: ['Cours de renforcement'],
        responses: [new OA\Response(response: 200, description: 'Niveaux', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Level')))]
    )]
    public function index()
    {
        return Level::query()->orderBy('order')->get();
    }
}
