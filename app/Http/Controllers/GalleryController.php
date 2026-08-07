<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GalleryController extends Controller
{
    /**
     * Display a listing of the images (Admin).
     */
    public function index()
    {
        $user = auth()->user();
        $canManage = $user && $user->hasRole('super_admin');

        $query = Gallery::orderBy('order')->latest();

        if (!$canManage) {
            $query->where('is_visible', true);
        }

        return Inertia::render('Gallery/Index', [
            'images' => $query->get(),
            'canManage' => $canManage,
        ]);
    }

    /**
     * Store a newly created image in storage (Super Admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('gallery', 'public');
                
                Gallery::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'image_path' => $path,
                    'order' => $request->order ?? 0,
                    'is_visible' => true,
                ]);
            }

            return back()->with('success', 'Images ajoutées à la galerie avec succès.');
        }

        return back()->with('error', 'Erreur lors de l\'ajout des images.');
    }

    /**
     * Update the specified image in storage (Super Admin).
     */
    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
        ]);

        $gallery->update($request->only(['title', 'description', 'order', 'is_visible']));

        return back()->with('success', 'Image mise à jour avec succès.');
    }

    /**
     * Remove the specified image from storage (Super Admin).
     */
    public function destroy(Gallery $gallery)
    {
        // Supprimer le fichier physiquement
        if (Storage::disk('public')->exists($gallery->image_path)) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return back()->with('success', 'Image supprimée avec succès.');
    }

    /**
     * Fetch images for public view.
     */
    public function getPublicGallery()
    {
        return Gallery::where('is_visible', true)->orderBy('order')->get();
    }
}
