<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllergenResource;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profiles = $request->user()->profiles()->with('allergens')->get();

        return success_response(ProfileResource::collection($profiles));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $profile = $request->user()->profiles()->create($validated);

        return success_response(new ProfileResource($profile), 'Profile created', 201);
    }

    public function show(Request $request, Profile $profile): JsonResponse
    {
        $this->authorise($request, $profile);

        return success_response(new ProfileResource($profile->load('allergens')));
    }

    public function update(Request $request, Profile $profile): JsonResponse
    {
        $this->authorise($request, $profile);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $profile->update($validated);

        return success_response(new ProfileResource($profile->load('allergens')), 'Profile updated');
    }

    public function destroy(Request $request, Profile $profile): JsonResponse
    {
        $this->authorise($request, $profile);

        $profile->delete();

        return success_response(message: 'Profile deleted');
    }

    public function updateAllergens(Request $request, Profile $profile): JsonResponse
    {
        $this->authorise($request, $profile);

        $validated = $request->validate([
            'allergen_ids'   => ['required', 'array'],
            'allergen_ids.*' => ['integer', 'exists:allergens,id'],
        ]);

        $profile->allergens()->sync($validated['allergen_ids']);

        return success_response(
            AllergenResource::collection($profile->load('allergens')->allergens),
            'Allergens updated'
        );
    }

    private function authorise(Request $request, Profile $profile): void
    {
        abort_if($profile->user_id !== $request->user()->id, 403, 'Forbidden');
    }
}