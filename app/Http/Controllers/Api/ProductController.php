<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScanResultResource;
use App\Models\Product;
use App\Models\Profile;
use App\Models\ScanLog;
use App\Services\NutritionixService;
use App\Services\OpenFoodFactsService;
use App\Services\ProductSyncService;
use App\Services\ScanRatingService;
use App\Services\UpcItemDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly UpcItemDbService     $upcService,
        private readonly OpenFoodFactsService $offService,
        private readonly NutritionixService   $nutritionixService,
        private readonly ProductSyncService   $syncService,
        private readonly ScanRatingService    $ratingService,
    ) {}

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode'    => ['required', 'string', 'max:50'],
            'profile_id' => ['nullable', 'integer', 'exists:profiles,id'],
        ]);

        // Resolve who we're scanning for
        $profile     = null;
        $allergenIds = [];

        if (! empty($validated['profile_id'])) {
            $profile = Profile::with('allergens')->findOrFail($validated['profile_id']);
            abort_if($profile->user_id !== $request->user()->id, 403, 'Forbidden');
            $allergenIds = $profile->allergens->pluck('id')->toArray();
        } else {
            $allergenIds = $request->user()->load('allergens')->allergens->pluck('id')->toArray();
        }

        // Try to load from local DB first (cached within 30 days, and data must be complete)
        $product = Product::with('allergens')->where('barcode', $validated['barcode'])->first();

        $needsFetch = ! $product
            || ! $product->fetched_at
            || $product->fetched_at->diffInDays(now()) > 30
            || ! $product->name
            || ! $product->ingredients_text;

        if ($needsFetch) {
            // Try UPC Item DB → Open Food Facts (UK + World) → Nutritionix
            $item = $this->upcService->fetchByBarcode($validated['barcode'])
                ?? $this->offService->fetchByBarcode($validated['barcode'])
                ?? $this->nutritionixService->fetchByBarcode($validated['barcode']);

            if (! $item) {
                return error_response('Product not found. Please check the barcode and try again.', 404);
            }

            $product = $this->syncService->sync($validated['barcode'], $item);
        }
        // Compute safety rating
        $result = $this->ratingService->computeRating($product->allergens, $allergenIds);

        // Log the scan
        $scanLog = ScanLog::create([
            'user_id'             => $request->user()->id,
            'profile_id'          => $profile?->id,
            'product_id'          => $product->id,
            'rating'              => $result['rating'],
            'triggered_allergens' => $result['triggered_allergens'],
            'created_at'          => now(),
        ]);

        $scanLog->setRelation('product', $product);
        $scanLog->setRelation('user', $request->user());

        if ($profile) {
            $scanLog->setRelation('profile', $profile);
        }

        return success_response(new ScanResultResource($scanLog));
    }
}
