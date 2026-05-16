<?php

namespace App\Services;

class FootballDataService
{
    private string $apiKey;
    private string $apiUrl;
    private int $cacheTime;
    private string $cacheDir;

    public function __construct()
    {
        EnvLoader::load();
        
        $this->apiKey = EnvLoader::get('FOOTBALL_DATA_API_KEY', '');
        $this->apiUrl = EnvLoader::get('FOOTBALL_DATA_API_URL', 'https://api.football-data.org/v4');
        $this->cacheTime = (int) EnvLoader::get('FOOTBALL_DATA_CACHE_TIME', '60');
        $this->cacheDir = __DIR__ . '/../../cache/football-data';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Make a request to football-data.org API
     * 
     * @param string $endpoint The API endpoint (e.g., "/competitions/WC", "/competitions/WC/matches")
     * @return array The response data or empty array on error
     */
    public function request(string $endpoint): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API key not configured'];
        }

        // Check cache first
        $cacheKey = md5($endpoint);
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $url = $this->apiUrl . $endpoint;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'X-Auth-Token: ' . $this->apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                error_log("Football-Data API error: $error");
                return ['error' => $error];
            }

            // Handle rate limiting
            if ($httpCode === 429) {
                return ['error' => 'Rate limit exceeded. Max 10 requests per minute.'];
            }

            if ($httpCode === 401) {
                return ['error' => 'Invalid API token'];
            }

            if ($httpCode !== 200) {
                return ['error' => "HTTP $httpCode"];
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Football-Data JSON decode error: " . json_last_error_msg());
                return ['error' => 'Invalid JSON response'];
            }

            // Cache the result
            $this->setCache($cacheKey, $data);

            return $data;

        } catch (\Exception $e) {
            error_log("Football-Data Service Exception: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get live matches from World Cup 2026
     * 
     * @return array Array of matches
     */
    public function getLiveMatches(): array
    {
        $response = $this->request('/competitions/WC/matches?status=LIVE');
        return $response['matches'] ?? [];
    }

    /**
     * Get scheduled matches from World Cup 2026
     * 
     * @return array Array of matches
     */
    public function getScheduledMatches(): array
    {
        $response = $this->request('/competitions/WC/matches?status=SCHEDULED');
        return $response['matches'] ?? [];
    }

    /**
     * Get all matches from World Cup 2026
     * 
     * @return array Array of matches
     */
    public function getAllMatches(): array
    {
        $response = $this->request('/competitions/WC/matches');
        return $response['matches'] ?? [];
    }

    /**
     * Get World Cup standings/table
     * 
     * @return array Array of standings data
     */
    public function getStandings(): array
    {
        $response = $this->request('/competitions/WC/standings');
        return $response['standings'] ?? [];
    }

    /**
     * Get World Cup competition info
     * 
     * @return array Competition data
     */
    public function getCompetition(): array
    {
        return $this->request('/competitions/WC');
    }

    /**
     * Get Premier League scheduled matches
     * 
     * @return array Array of matches
     */
    public function getPremierLeagueScheduledMatches(): array
    {
        $response = $this->request('/competitions/PL/matches?status=SCHEDULED');
        return $response['matches'] ?? [];
    }

    /**
     * Get all Premier League matches
     * 
     * @return array Array of matches
     */
    public function getPremierLeagueAllMatches(): array
    {
        $response = $this->request('/competitions/PL/matches');
        return $response['matches'] ?? [];
    }

    /**
     * Get Premier League standings/table
     * 
     * @return array Array of standings data
     */
    public function getPremierLeagueStandings(): array
    {
        $response = $this->request('/competitions/PL/standings');
        return $response['standings'] ?? [];
    }

    /**
     * Get Premier League competition info
     * 
     * @return array Competition data
     */
    public function getPremierLeagueCompetition(): array
    {
        return $this->request('/competitions/PL');
    }

    /**
     * Get cache file path
     */
    private function getCachePath(string $key): string
    {
        return $this->cacheDir . '/' . $key . '.json';
    }

    /**
     * Get data from cache if not expired
     */
    private function getCache(string $key): ?array
    {
        $path = $this->getCachePath($key);
        
        if (!file_exists($path)) {
            return null;
        }

        $mtime = filemtime($path);
        if (time() - $mtime > $this->cacheTime) {
            unlink($path);
            return null;
        }

        $data = json_decode(file_get_contents($path), true);
        return $data;
    }

    /**
     * Store data in cache
     */
    private function setCache(string $key, array $data): void
    {
        $path = $this->getCachePath($key);
        file_put_contents($path, json_encode($data), LOCK_EX);
    }

    /**
     * Clear all cache
     */
    public function clearCache(): void
    {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
