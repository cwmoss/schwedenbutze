<?php

/**
 * Flat-File Data Loader für Slowfoot
 * Lädt Hausdaten und Markdown-Unterseiten direkt aus content/houses/{slug}/
 */

namespace slowfoot\loader;

use slowfoot\configuration;
use slowfoot\hook;
use slowfoot\store;
use slowfoot\image\asset;
use Symfony\Component\Yaml\Yaml;
use League\CommonMark\CommonMarkConverter;

class houses {

    /**
     * Hauptdatenquelle für Slowfoot (sources['houses'])
     */
    static public function load(configuration $config, store $db): \Generator {
        $content_dir = $config->base . '/content/houses';
        if (!is_dir($content_dir)) {
            return;
        }

        $house_ids = [
            'dangebo' => 'fefc831a-9151-4e07-8d44-f4a168e6df6a',
            'ringshult' => 'b2fa2962-d272-419d-8541-37deab885248',
            'oksankas-gard' => 'ba29f529-9654-42b7-a57b-52ca4c6b11da'
        ];

        $house_dirs = glob($content_dir . '/*', GLOB_ONLYDIR) ?: [];

        foreach ($house_dirs as $hdir) {
            $slug = basename($hdir);
            $house_file = $hdir . '/house.json';
            if (!file_exists($house_file)) {
                continue;
            }

            $house = json_decode(file_get_contents($house_file), true);
            if (!is_array($house)) {
                continue;
            }

            $house_id = $house_ids[$slug] ?? ('house-' . $slug);

            // 1. Infoboxen verarbeiten & Dokumente yielden
            $infobox_refs = [];
            $boxes = $house['infoboxes'] ?? [];
            foreach ($boxes as $i => $box) {
                $info_id = "infobox-{$slug}-{$i}";
                yield [
                    '_id' => $info_id,
                    '_type' => 'infobox',
                    'title' => $box['title'] ?? '',
                    'icon' => $box['icon'] ?? 'fa-star',
                    'info' => $box['text'] ?? ''
                ];
                $infobox_refs[] = [
                    '_type' => 'infobox',
                    'title' => $box['title'] ?? '',
                    'ref' => ['_ref' => $info_id, '_type' => 'infoboxlink']
                ];
            }

            // 2. Unterseiten verarbeiten & Dokumente yielden
            $sections = [];
            $subpages_dir = $hdir . '/subpages';
            if (is_dir($subpages_dir)) {
                $md_files = glob($subpages_dir . '/*.md') ?: [];
                foreach ($md_files as $md_file) {
                    $short_slug = pathinfo($md_file, PATHINFO_FILENAME);
                    $content = file_get_contents($md_file);

                    $frontmatter = [];
                    $markdown_body = $content;

                    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
                        try {
                            $frontmatter = Yaml::parse($matches[1]) ?: [];
                        } catch (\Exception $e) {
                            $frontmatter = [];
                        }
                        $markdown_body = $matches[2];
                    }

                    $subpage_slug = $frontmatter['slug'] ?? ($slug . '-' . $short_slug);
                    $subpage_id = $frontmatter['_id'] ?? ("post-" . $slug . "-" . $short_slug);

                    // Post-Dokument für Slowfoot
                    yield [
                        '_id' => $subpage_id,
                        '_type' => 'post',
                        'title' => $frontmatter['title'] ?? ucfirst(str_replace('-', ' ', $short_slug)),
                        'slug' => ['current' => $subpage_slug, '_type' => 'slug'],
                        'short_slug' => $short_slug,
                        'house' => $slug,
                        'excerpt' => $frontmatter['excerpt'] ?? '',
                        'body' => $markdown_body,
                        'mainImage' => $frontmatter['main_image'] ?? '',
                        'page' => ['_ref' => $house_id, '_type' => 'reference'],
                        'show_on_frontpage' => $frontmatter['show_on_frontpage'] ?? true,
                        'publishedAt' => $frontmatter['date'] ?? date('c')
                    ];

                    if (!empty($frontmatter['show_on_frontpage'])) {
                        $sections[] = [
                            '_type' => 'section',
                            'title' => $frontmatter['title'] ?? ucfirst(str_replace('-', ' ', $short_slug)),
                            'ref' => ['_ref' => $subpage_id, '_type' => 'sectionlink']
                        ];
                    }
                }
            }

            // 3. Haus-Hauptseite Dokument yielden
            yield [
                '_id' => $house_id,
                '_type' => 'page',
                'title' => $house['title'] ?? ucfirst($slug),
                'slug' => ['current' => $slug, '_type' => 'slug'],
                'excerpt' => $house['hero']['teaser'] ?? $house['teaser'] ?? '',
                'body' => $house['hero']['body_markdown'] ?? '',
                'mainImage' => $house['hero']['image'] ?? '',
                'infotitle' => $house['infotitle'] ?? '',
                'infoheadline' => $house['infoheadline'] ?? '',
                'infoboxes' => $infobox_refs,
                'sections' => $sections,
                'booking_house' => $house
            ];
        }
    }

    /**
     * Registriert Template-Helper und Filter für Markdown & lokale Bilder
     */
    static public function register_hooks(): void {
        // 1. Template Helper: sanity_text unterstützt transparent Markdown-Strings
        hook::add('bind_template_helper', function ($ds, $src, $config) {
            return [
                'sanity_text' => function ($text, $opts = []) use ($ds, $config) {
                    if (!$text) return "";

                    if (is_string($text)) {
                        $converter = new CommonMarkConverter([
                            'html_input' => 'allow',
                            'allow_unsafe_links' => false,
                        ]);
                        return (string)$converter->convert($text);
                    }

                    // Fallback auf Sanity BlockContent bei Array-Daten
                    if (class_exists('slowfoot_plugin\sanity\sanity')) {
                        $plugin = $config->get_plugin('slowfoot_plugin\sanity\sanity');
                        if ($plugin) {
                            return \slowfoot_plugin\sanity\sanity::sanity_text($text, $opts, $ds, $config, $plugin);
                        }
                    }

                    return is_scalar($text) ? htmlspecialchars((string)$text) : '';
                }
            ];
        });

        // 2. Filter: assets_map erkennt lokale Bilder aus content/houses/{slug}/images/
        hook::add_filter('assets_map', function ($img, store $store) {
            if (is_string($img) && !empty($img)) {
                // Suche nach Bild in allen Haus-Ordnern
                $base = SLOWFOOT_BASE;
                $pattern = $base . '/content/houses/*/images/' . basename($img);
                $matches = glob($pattern);

                if (!empty($matches) && file_exists($matches[0])) {
                    $local_file = $matches[0];
                    $info = @getimagesize($local_file);
                    if ($info) {
                        return new asset(
                            _id: basename($img),
                            _src: 'local',
                            url: '',
                            path: $local_file,
                            w: (string)$info[0],
                            h: (string)$info[1],
                            mime: $info['mime']
                        );
                    }
                }
            }

            return $img;
        });
    }
}
