<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RunSummary;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class SyncFirebaseRuns extends Command
{
    protected $signature = 'firebase:sync';
    protected $description = 'Fetch Users AND Runs from Firebase via REST API';

    public function handle()
    {
        $this->info('Connecting to Firebase via REST API...');

        try {
            // 1. Setup Auth
            $credPath = base_path('storage/app/firebase_credentials.json');
            $jsonKey = json_decode(file_get_contents($credPath), true);
            $projectId = $jsonKey['project_id'];
            
            $scopes = ['https://www.googleapis.com/auth/datastore'];
            $creds = new ServiceAccountCredentials($scopes, $credPath);
            $token = $creds->fetchAuthToken()['access_token'];

            // ---------------------------------------------------------
            // 2. FETCH USERS FIRST (To get their names)
            // ---------------------------------------------------------
            $this->info('Fetching Users list...');
            $usersUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/users";
            $usersResponse = Http::withToken($token)->get($usersUrl);
            
            $userMap = []; // This will store ['user_id' => 'Real Name']

            if ($usersResponse->successful()) {
                $userDocs = $usersResponse->json()['documents'] ?? [];
                
                foreach ($userDocs as $uDoc) {
                    // Extract User ID from the path (users/USER_ID)
                    $userId = basename($uDoc['name']); 
                    
                    // Extract fields safely
                    $fields = $this->flattenFirestoreData($uDoc['fields'] ?? []);
                    
                    // Try to find 'name' or 'username'
                    $realName = $fields['name'] ?? $fields['username'] ?? 'Unknown';
                    
                    $userMap[$userId] = $realName;
                }
                $this->info("Found " . count($userMap) . " users.");
            }

            // ---------------------------------------------------------
            // 3. FETCH RUNS AND SYNC
            // ---------------------------------------------------------
            $this->info('Fetching Runs...');
            $runsUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/runs";
            $response = Http::withToken($token)->get($runsUrl);

            if ($response->failed()) {
                $this->error("API Error: " . $response->body());
                return;
            }

            $rows = $response->json()['documents'] ?? [];
            $count = 0;

            foreach ($rows as $doc) {
                $firebaseId = basename($doc['name']);
                $fields = $this->flattenFirestoreData($doc['fields'] ?? []);

                $userId = $fields['userId'] ?? 'Unknown';
                
                // LOOKUP THE NAME using our map
                // If not found in map, fallback to "Guest"
                $runnerName = $userMap[$userId] ?? 'Guest User';

                RunSummary::updateOrCreate(
                    ['firebase_id' => $firebaseId],
                    [
                        'user_id'     => $userId,
                        'username'    => $runnerName, // <--- SAVING THE NAME HERE
                        'distance_km' => isset($fields['distance_km']) ? (float)$fields['distance_km'] : 0,
                        'time'        => $fields['time'] ?? '00:00:00',
                        'pace'        => $fields['pace'] ?? '00:00',
                        'date'        => isset($fields['date']) ? date('Y-m-d', strtotime($fields['date'])) : now(),
                    ]
                );
                $count++;
            }

            $this->info("Success! Synced {$count} runs.");

        } catch (\Throwable $e) {
            $this->error("CRITICAL ERROR: " . $e->getMessage());
        }
    }

    private function flattenFirestoreData($fields) {
        $flat = [];
        foreach ($fields as $key => $valueWrapper) {
            $flat[$key] = reset($valueWrapper); 
        }
        return $flat;
    }
}