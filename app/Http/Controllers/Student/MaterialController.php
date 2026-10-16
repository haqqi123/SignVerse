<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Materi belajar student (phase 3): daftar kategori → materi → lesson.
 * Filter bahasa (sibi/bisindo) mengikuti landing project lama.
 */
class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $language = $request->query('language');

        $categories = Category::query()
            ->with(['materials' => fn ($query) => $query
                ->when(in_array($language, [Material::LANGUAGE_SIBI, Material::LANGUAGE_BISINDO], true),
                    fn ($q) => $q->where('language', $language))
                ->withCount('lessons')
                ->orderBy('order'),
            ])
            ->orderBy('order')
            ->get()
            ->filter(fn (Category $category) => $category->materials->isNotEmpty());

        return view('student.materials.index', [
            'categories' => $categories,
            'language' => $language,
        ]);
    }

    public function show(Material $material): View
    {
        $material->load(['category', 'lessons' => fn ($query) => $query->orderBy('order')]);

        return view('student.materials.show', [
            'material' => $material,
        ]);
    }
}
