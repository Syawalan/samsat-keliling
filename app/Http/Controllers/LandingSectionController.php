<?php

namespace App\Http\Controllers;

use App\Models\LandingSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingSectionController extends Controller
{
    public function index(): View
    {
        $sections = LandingSection::query()->orderBy('sort_order')->get();

        return view('admin.landing', compact('sections'));
    }

    public function update(Request $request, LandingSection $section): RedirectResponse
    {
        $data = $request->validate([
            'icon' => ['required', 'string', 'max:16'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'subheading' => ['nullable', 'string', 'max:180'],
            'subheading_text' => ['nullable', 'string', 'max:5000'],
            'items_title' => ['nullable', 'string', 'max:180'],
            'items' => ['nullable', 'string', 'max:10000'],
        ]);

        $section->update([
            ...$data,
            'items' => array_values(array_filter(array_map(
                'trim',
                preg_split('/\R/u', $data['items'] ?? '') ?: [],
            ))),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Informasi landing page berhasil diperbarui.');
    }
}