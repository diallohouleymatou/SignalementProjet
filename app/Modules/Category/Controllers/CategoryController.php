<?php

namespace App\Modules\Category\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Get all categories (with hierarchy)
     */
    public function index(Request $request)
    {
        $query = Category::query()->active();

        // Si on veut seulement les catégories racines
        if ($request->boolean('roots_only')) {
            $query->roots();
        }

        // Charger les relations
        if ($request->boolean('with_children')) {
            $query->with('children');
        }

        if ($request->boolean('with_signalements_count')) {
            $query->withCount('signalements');
        }

        $categories = $query->ordered()->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get a specific category
     */
    public function show($id)
    {
        $category = Category::with(['children', 'parent'])
            ->withCount('signalements')
            ->findOrFail($id);

        return new CategoryResource($category);
    }

    /**
     * Create a new category (Admin only)
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        return new CategoryResource($category);
    }

    /**
     * Update a category (Admin only)
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|string|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return new CategoryResource($category);
    }

    /**
     * Delete a category (Admin only)
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::findOrFail($id);

        // Vérifier si la catégorie a des signalements
        if ($category->signalements()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with signalements. Reassign them first.'
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Get categories tree (hierarchical structure)
     */
    public function tree()
    {
        $rootCategories = Category::active()
            ->roots()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->withCount('signalements')
            ->ordered()
            ->get();

        return CategoryResource::collection($rootCategories);
    }

    /**
     * Get popular categories (most signalements)
     */
    public function popular(Request $request)
    {
        $limit = $request->input('limit', 10);

        $categories = Category::active()
            ->withCount('signalements')
            ->orderBy('signalements_count', 'desc')
            ->limit($limit)
            ->get();

        return CategoryResource::collection($categories);
    }
}
