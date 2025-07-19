<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function index()
    {
        $jsonPath = storage_path('app/announcements.json');
        $seenPath = storage_path('app/seen.json');
        
        $announcements = file_exists($jsonPath) 
            ? json_decode(file_get_contents($jsonPath), true) 
            : [];
            
        $seen = file_exists($seenPath) 
            ? json_decode(file_get_contents($seenPath), true) 
            : [];       

        return view('announcements', compact('announcements', 'seen'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $jsonPath = storage_path('app/announcements.json');
        
        $announcements = file_exists($jsonPath) 
            ? json_decode(file_get_contents($jsonPath), true) 
            : [];

        $newId = empty($announcements) ? 1 : max(array_column($announcements, 'id')) + 1;

        $newAnnouncement = [
            'id' => $newId,
            'title' => $request->title,
            'message' => $request->message,
            'created_at' => now()->format('Y-m-d H:i:s')
        ];

        $announcements[] = $newAnnouncement;

        file_put_contents($jsonPath, json_encode($announcements, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => 'Announcement added successfully',
            'announcement' => $newAnnouncement
        ]);
    }

    public function clearSeen(): JsonResponse
    {
        $seenPath = storage_path('app/seen.json');
        
        if (file_exists($seenPath)) {
            unlink($seenPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Seen announcements cleared'
        ]);
    }
}