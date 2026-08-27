<?php
/**
 * Caching- und Fetching-Layer für iCal / Google-Kalender Feeds
 * Schützt vor API-Rate-Limits, sorgt für minimale Ladezeiten (<5ms)
 * und bietet Stale-Fallback bei Offline-Verbindung zu Google.
 */

require_once __DIR__ . '/ICalParser.php';

class ICalCache {
    private string $cache_dir;
    private int $default_ttl;
    private int $timeout;

    public function __construct(?string $cache_dir = null, int $default_ttl = 900, int $timeout = 3) {
        $this->cache_dir = $cache_dir ?: __DIR__ . '/../var/cache/ical';
        $this->default_ttl = $default_ttl;
        $this->timeout = $timeout;

        if (!is_dir($this->cache_dir)) {
            @mkdir($this->cache_dir, 0777, true);
        }
    }

    /**
     * Holt Termine für eine oder mehrere iCal URLs (aus dem Cache oder frisch von der URL).
     *
     * @param string|array $urls Einzelne URL oder Array von URLs
     * @param int|null $ttl Cache-Gültigkeit in Sekunden (Standard: 900s / 15 Min)
     * @param bool $force_refresh Erzwingt Neuabruf
     * @return array Liste aller gefundenen Events ['from', 'to', 'summary']
     */
    public function getEvents($urls, ?int $ttl = null, bool $force_refresh = false): array {
        if (empty($urls)) {
            return [];
        }

        if (is_string($urls)) {
            $urls = [$urls];
        }

        $ttl = $ttl !== null ? $ttl : $this->default_ttl;
        $all_events = [];

        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;

            $events = $this->fetchSingleFeed($url, $ttl, $force_refresh);
            $all_events = array_merge($all_events, $events);
        }

        return $all_events;
    }

    /**
     * Verarbeitet einen einzelnen iCal-Feed mit Caching und Fallback.
     */
    private function fetchSingleFeed(string $url, int $ttl, bool $force_refresh): array {
        $cache_key = md5($url);
        $cache_file = $this->cache_dir . '/' . $cache_key . '.json';

        // 1. Prüfen, ob noch gültiger Cache vorliegt
        $has_cache = file_exists($cache_file);
        if ($has_cache && !$force_refresh) {
            $mtime = filemtime($cache_file);
            if ((time() - $mtime) < $ttl) {
                $cached_data = json_decode(file_get_contents($cache_file), true);
                if (isset($cached_data['events']) && is_array($cached_data['events'])) {
                    return $cached_data['events'];
                }
            }
        }

        // 2. Feed aus dem Web abrufen (mit kurzem Timeout)
        $ics_content = $this->fetchUrl($url);

        // 3. Bei erfolgreichem Abruf parsen und cachen
        if ($ics_content !== null && strpos($ics_content, 'BEGIN:VCALENDAR') !== false) {
            $events = ICalParser::parse($ics_content);
            $cache_payload = [
                'updated_at' => time(),
                'url' => $url,
                'events' => $events
            ];
            @file_put_contents($cache_file, json_encode($cache_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $events;
        }

        // 4. Fallback: Bei Netzwerkfehler bestehenden alten Cache nutzen (Stale-While-Revalidate)
        if ($has_cache) {
            $cached_data = json_decode(file_get_contents($cache_file), true);
            if (isset($cached_data['events']) && is_array($cached_data['events'])) {
                return $cached_data['events'];
            }
        }

        return [];
    }

    /**
     * Führt HTTP GET Abruf durch (unterstützt file:// für lokale Test-Fixtures sowie http:// / https://).
     */
    protected function fetchUrl(string $url): ?string {
        // Lokale Datei / Test-Fixture
        if (strpos($url, 'file://') === 0 || (!preg_match('/^https?:\/\//i', $url) && file_exists($url))) {
            $filepath = str_replace('file://', '', $url);
            return file_exists($filepath) ? file_get_contents($filepath) : null;
        }

        // Remote HTTP(S) Abruf
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SchwedenbutzeCalendarSync/1.0');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code >= 200 && $http_code < 300 && is_string($response)) {
                return $response;
            }
        } else {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => $this->timeout,
                    'user_agent' => 'SchwedenbutzeCalendarSync/1.0'
                ]
            ]);
            $response = @file_get_contents($url, false, $ctx);
            if (is_string($response)) {
                return $response;
            }
        }

        return null;
    }
}
