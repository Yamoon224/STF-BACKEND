<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use OpenApi\Attributes as OA;

class SubjectController extends Controller
{
    #[OA\Get(
        path: '/subjects',
        summary: 'Lister les matières (cours de renforcement)',
        description: 'Public. STF est purement scientifique : Mathématiques, Physique, Chimie, SVT.',
        tags: ['Cours de renforcement'],
        responses: [new OA\Response(response: 200, description: 'Matières', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Subject')))]
    )]
    public function index()
    {
        return Subject::query()->orderBy('name')->get();
    }
}
