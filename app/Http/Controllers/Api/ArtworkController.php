<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use Illuminate\Http\Request;

class ArtworkController extends Controller
{
    public function index(Request $request)
    {
        $query = Artwork::with(['artist', 'primaryImage'])
            ->where('is_active', true)
            ->where('is_approved', true)
            ->where('site_context', '!=', 'worldcup');

        if ($request->category) $query->where('category', $request->category);
        if ($request->region)   $query->where('region', $request->region);
        if ($request->min_price) $query->where('price', '>=', $request->min_price);
        if ($request->max_price) $query->where('price', '<=', $request->max_price);
        if ($request->status)   $query->where('status', $request->status);
        if ($request->artist_id) $query->where('artist_id', $request->artist_id);

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        $artwork = Artwork::with(['artist', 'images', 'collections'])
            ->where('is_active', true)
            ->where('is_approved', true)
            ->findOrFail($id);

        return response()->json($artwork);
    }

    public function featured()
    {
        $artist = \App\Models\Artist::with(['artworks' => function($q) {
            $q->where('is_active', true)
              ->where('is_approved', true)
              ->latest()
              ->limit(4);
        }])
        ->where('is_featured', true)
        ->where('is_active', true)
        ->first();

        if (!$artist) {
            return response()->json(['message' => 'No featured artist'], 404);
        }

        return response()->json($artist);
    }
}