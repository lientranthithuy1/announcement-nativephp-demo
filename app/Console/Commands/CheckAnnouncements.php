<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Native\Laravel\Facades\Notification;

class CheckAnnouncements extends Command
{
    protected $signature = 'announcements:check';
    protected $description = 'Check for new announcements and notify users';

    public function handle(): void
    {
        $jsonPath = storage_path('app/announcements.json');
        $seenPath = storage_path('app/seen.json');

        // Read announcement file
        if (!file_exists($jsonPath)) {
            $this->warn('Could not find announcements.json');
            return;
        }

        $announcements = json_decode(file_get_contents($jsonPath), true);
        
        // Debug: Check if announcements loaded correctly
        if (!$announcements) {
            $this->error('Failed to decode announcements.json');
            return;
        }
        
        $this->info('Found ' . count($announcements) . ' announcements');

        // Load seen IDs
        $seen = [];
        if (file_exists($seenPath)) {
            $seenContent = file_get_contents($seenPath);
            $seen = json_decode($seenContent, true) ?: [];
        }
        
        $this->info('Previously seen: ' . count($seen) . ' announcements');
        $this->info('Seen IDs: ' . implode(', ', $seen));

        $newCount = 0;
        $newSeen = $seen;

        foreach ($announcements as $item) {
            // Debug: Check each announcement
            $this->info("Processing announcement ID: {$item['id']}");
            
            if (!in_array($item['id'], $seen)) {
                $this->info("New announcement found: {$item['title']}");
                
                // Display popup notification
                Notification::title($item['title'])
                    ->message($item['message'])
                    ->show();

                // Mark this announcement as seen
                $newSeen[] = $item['id'];
                $newCount++;
                
                $this->info("Displayed notification for: {$item['title']}");
            } else {
                $this->info("Already seen: {$item['id']}");
            }
        }

        // Save updated seen IDs
        if ($newCount > 0) {
            $result = file_put_contents($seenPath, json_encode($newSeen, JSON_PRETTY_PRINT));
            if ($result === false) {
                $this->error('Failed to write seen.json');
            } else {
                $this->info("Updated seen.json with {$newCount} new announcements");
            }
        }

        $this->info("Finished checking announcements. {$newCount} new notifications shown.");
    }
}