<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllergenResource;
use App\Models\Allergen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllergenController extends Controller
{
    public function index(): JsonResponse
    {
        return success_response(AllergenResource::collection(Allergen::all()));
    }

    public function myAllergens(Request $request): JsonResponse
    {
        $user = $request->user()->load('allergens');

        return success_response(AllergenResource::collection($user->allergens));
    }

    public function updateMyAllergens(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'allergen_ids'   => ['required', 'array'],
            'allergen_ids.*' => ['integer', 'exists:allergens,id'],
        ]);

        $request->user()->allergens()->sync($validated['allergen_ids']);

        $user = $request->user()->load('allergens');

        return success_response(AllergenResource::collection($user->allergens), 'Allergen profile updated');
    }
}