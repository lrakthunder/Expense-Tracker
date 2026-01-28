<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * List categories for the authenticated user (optionally filter by type).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $type = $request->get('type');
        $query = Category::where('created_by', $user->id);
        if ($type && in_array($type, ['income', 'expense'], true)) {
            $query->where('type', $type);
        }

        $categories = $query
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'type', 'created_at']);

        return response()->json(['data' => $categories]);
    }

    /**
     * Create a new category for the authenticated user.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense']), Rule::unique('categories')->where(function ($q) use ($user, $request) {
                return $q->where('created_by', $user->id)
                    ->where('type', $request->input('type'))
                    ->where('name', $request->input('name'));
            })],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        $category = Category::create([
            'name' => trim($data['name']),
            'type' => $data['type'],
            'created_by' => $user->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['data' => $category], 201);
        }

        return back()->with('success', 'Category created');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $category = Category::where('id', $id)
            ->where('created_by', $user->id)
            ->first();

        if (!$category) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return back()->with('error', 'Category not found');
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense']), Rule::unique('categories')->where(function ($q) use ($user, $request, $id) {
                return $q->where('created_by', $user->id)
                    ->where('type', $request->input('type'))
                    ->where('name', $request->input('name'))
                    ->where('id', '!=', $id);
            })],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        $category->update([
            'name' => trim($data['name']),
            'type' => $data['type'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['data' => $category]);
        }

        return back()->with('success', 'Category updated');
    }

    /**
     * Delete a category.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $category = Category::where('id', $id)
            ->where('created_by', $user->id)
            ->first();

        if (!$category) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return back()->with('error', 'Category not found');
        }

        // Prevent deletion of default categories
        $defaultCategories = ['Food', 'Transport', 'Utilities', 'Salary', 'Interest', 'Other'];
        if (in_array($category->name, $defaultCategories)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Cannot delete default category'], 403);
            }
            return back()->with('error', 'Cannot delete default category');
        }

        $category->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Category deleted']);
        }

        return back()->with('success', 'Category deleted');
    }
}
