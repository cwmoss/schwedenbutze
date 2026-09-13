<?php

/**
 * Migration Script: Sanity DB / Legacy Flat-Files -> Modular Flat-File Structure
 * 
 * Target structure:
 * slft/content/houses/{slug}/
 *   ├── house.json
 *   ├── subpages/
 *   │     └── {subpage}.md
 *   └── images/
 *         └── {image files}
 */

$base_dir = dirname(__DIR__);
$db_file = $base_dir . '/var/slowfoot.db';
$houses_dir = $base_dir . '/content/houses';
$download_dir = $base_dir . '/var/download';

if (!file_exists($db_file)) {
    die("FEHLER: SQLite Datenbank $db_file nicht gefunden!\n");
}

$db = new SQLite3($db_file, SQLITE3_OPEN_READONLY);

/**
 * Convert PortableText blocks to Markdown
 */
function portable_text_to_markdown($blocks) {
    if (empty($blocks)) return '';
    if (is_string($blocks)) return trim($blocks);
    if (!is_array($blocks)) return '';

    $md_parts = [];

    foreach ($blocks as $block) {
        $type = $block['_type'] ?? '';
        if ($type !== 'block') {
            // Check for image or other embeds inside body
            if ($type === 'mainImage' || $type === 'image') {
                $caption = $block['caption'] ?? '';
                $ref = $block['asset']['_ref'] ?? '';
                $md_parts[] = "![$caption]($ref)";
            }
            continue;
        }

        $style = $block['style'] ?? 'normal';
        $markDefs = [];
        if (!empty($block['markDefs'])) {
            foreach ($block['markDefs'] as $def) {
                if (!empty($def['_key'])) {
                    $markDefs[$def['_key']] = $def;
                }
            }
        }

        $line = '';
        $children = $block['children'] ?? [];
        foreach ($children as $child) {
            $text = $child['text'] ?? '';
            $marks = $child['marks'] ?? [];

            // Apply formatting
            $is_bold = in_array('strong', $marks);
            $is_italic = in_array('em', $marks);
            $is_code = in_array('code', $marks);

            if ($is_code) $text = "`$text`";
            if ($is_bold) $text = "**$text**";
            if ($is_italic) $text = "*$text*";

            // Apply link marks
            foreach ($marks as $mark) {
                if (isset($markDefs[$mark])) {
                    $def = $markDefs[$mark];
                    if (($def['_type'] ?? '') === 'link' && !empty($def['href'])) {
                        $text = "[$text](" . $def['href'] . ")";
                    }
                }
            }

            $line .= $text;
        }

        $line = trim($line);
        if ($line === '') continue;

        // Apply block style
        $listItem = $block['listItem'] ?? null;
        if ($listItem === 'bullet') {
            $md_parts[] = "- " . $line;
        } elseif ($listItem === 'number') {
            $md_parts[] = "1. " . $line;
        } elseif ($style === 'h1') {
            $md_parts[] = "# " . $line;
        } elseif ($style === 'h2') {
            $md_parts[] = "## " . $line;
        } elseif ($style === 'h3') {
            $md_parts[] = "### " . $line;
        } elseif ($style === 'h4') {
            $md_parts[] = "#### " . $line;
        } elseif ($style === 'blockquote') {
            $md_parts[] = "> " . $line;
        } else {
            $md_parts[] = $line;
        }
    }

    return implode("\n\n", $md_parts);
}

/**
 * Resolve Sanity image asset reference to local filename
 */
function resolve_image_asset($db, $asset_ref, $download_dir, $target_img_dir) {
    if (empty($asset_ref)) return null;

    $stmt = $db->prepare("SELECT body FROM docs WHERE _id = :id AND _type = 'sanity.imageAsset'");
    $stmt->bindValue(':id', $asset_ref, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    if (!$row) return null;

    $asset = json_decode($row['body'], true);
    $orig_name = $asset['originalFilename'] ?? '';
    $ext = $asset['extension'] ?? 'jpg';
    $sha1 = $asset['sha1hash'] ?? '';

    // Destination filename
    $dest_filename = $orig_name ? preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $orig_name) : "$sha1.$ext";
    
    // Check download cache
    $cache_pattern = $download_dir . '/' . $asset_ref;
    $matches = glob($download_dir . '/*' . $sha1 . '*');
    $src_file = null;
    if (file_exists($cache_pattern)) {
        $src_file = $cache_pattern;
    } elseif (!empty($matches)) {
        $src_file = $matches[0];
    }

    if ($src_file && file_exists($src_file)) {
        if (!is_dir($target_img_dir)) {
            mkdir($target_img_dir, 0755, true);
        }
        $dest_path = $target_img_dir . '/' . $dest_filename;
        copy($src_file, $dest_path);
        return $dest_filename;
    }

    return $orig_name ?: ($sha1 . '.' . $ext);
}

$houses = [
    'dangebo' => [
        'page_id' => 'fefc831a-9151-4e07-8d44-f4a168e6df6a',
        'subpage_prefix' => 'dangebo-'
    ],
    'ringshult' => [
        'page_id' => 'b2fa2962-d272-419d-8541-37deab885248',
        'subpage_prefix' => 'ringshult-'
    ],
    'oksankas-gard' => [
        'page_id' => 'ba29f529-9654-42b7-a57b-52ca4c6b11da',
        'subpage_prefix' => 'oksankas-gard-'
    ]
];

echo "🚀 Starte Migration der Hausdaten in modulare Flat-File Struktur...\n\n";

foreach ($houses as $slug => $info) {
    echo "========================================================\n";
    echo "Processing House: $slug\n";
    echo "========================================================\n";

    $house_dir = $houses_dir . '/' . $slug;
    $subpages_dir = $house_dir . '/subpages';
    $images_dir = $house_dir . '/images';

    if (!is_dir($house_dir)) mkdir($house_dir, 0755, true);
    if (!is_dir($subpages_dir)) mkdir($subpages_dir, 0755, true);
    if (!is_dir($images_dir)) mkdir($images_dir, 0755, true);

    // 1. Existing legacy json
    $legacy_json_file = $houses_dir . '/' . $slug . '.json';
    $house_data = [];
    if (file_exists($legacy_json_file)) {
        $house_data = json_decode(file_get_contents($legacy_json_file), true) ?: [];
    }

    // 2. Load page from Sanity DB
    $page_stmt = $db->prepare("SELECT body FROM docs WHERE _id = :id AND _type = 'page'");
    $page_stmt->bindValue(':id', $info['page_id'], SQLITE3_TEXT);
    $page_res = $page_stmt->execute();
    $page_row = $page_res->fetchArray(SQLITE3_ASSOC);
    $page_doc = $page_row ? json_decode($page_row['body'], true) : [];

    // Resolve Hero Image
    $hero_asset_ref = $page_doc['mainImage']['asset']['_ref'] ?? '';
    $hero_image_file = resolve_image_asset($db, $hero_asset_ref, $download_dir, $images_dir);

    // Hero Teaser & Body
    $hero_teaser = portable_text_to_markdown($page_doc['excerpt'] ?? '');
    $hero_body = portable_text_to_markdown($page_doc['body'] ?? '');

    $house_data['hero'] = [
        'image' => $hero_image_file ?: ($page_doc['mainImage']['asset']['_ref'] ?? ''),
        'teaser' => $hero_teaser,
        'body_markdown' => $hero_body
    ];

    // Infotitle & Headline
    $house_data['infotitle'] = $page_doc['infotitle'] ?? 'Highlights';
    $house_data['infoheadline'] = portable_text_to_markdown($page_doc['infoheadline'] ?? '');

    // Resolve Infoboxes
    $infoboxes = [];
    if (!empty($page_doc['infoboxes'])) {
        foreach ($page_doc['infoboxes'] as $ib) {
            $ref_id = $ib['ref']['_ref'] ?? '';
            $custom_title = $ib['title'] ?? null;
            if ($ref_id) {
                $info_stmt = $db->prepare("SELECT body FROM docs WHERE _id = :id AND _type = 'info'");
                $info_stmt->bindValue(':id', $ref_id, SQLITE3_TEXT);
                $info_res = $info_stmt->execute();
                if ($info_row = $info_res->fetchArray(SQLITE3_ASSOC)) {
                    $info_doc = json_decode($info_row['body'], true);
                    $infoboxes[] = [
                        'icon' => $info_doc['icon'] ?? 'fa-star',
                        'title' => $custom_title ?: ($info_doc['title'] ?? ''),
                        'text' => $info_doc['info'] ?? ''
                    ];
                }
            }
        }
    }
    $house_data['infoboxes'] = $infoboxes;

    // Resolve Sections (Frontpage Subpages order)
    $sections_order = [];
    $section_subpage_ids = [];
    if (!empty($page_doc['sections'])) {
        foreach ($page_doc['sections'] as $sec) {
            $ref_id = $sec['ref']['_ref'] ?? '';
            if ($ref_id) {
                $section_subpage_ids[$ref_id] = true;
            }
        }
    }

    // 3. Find all posts belonging to this page
    $post_stmt = $db->prepare("SELECT _id, body FROM docs WHERE _type = 'post' AND json_extract(body, '$.page._ref') = :page_id");
    $post_stmt->bindValue(':page_id', $info['page_id'], SQLITE3_TEXT);
    $post_res = $post_stmt->execute();

    $subpages_found = 0;
    while ($p_row = $post_res->fetchArray(SQLITE3_ASSOC)) {
        $p_id = $p_row['_id'];
        $p_doc = json_decode($p_row['body'], true);

        $p_title = $p_doc['title'] ?? 'Unbenannt';
        $p_slug = $p_doc['slug']['current'] ?? '';
        if (!$p_slug) continue;

        // Clean short filename for subpages/
        $short_slug = $p_slug;
        if (str_starts_with($p_slug, $info['subpage_prefix'])) {
            $short_slug = substr($p_slug, strlen($info['subpage_prefix']));
        }

        if (isset($section_subpage_ids[$p_id])) {
            $sections_order[] = $short_slug;
        }

        // Subpage Image
        $p_img_ref = $p_doc['mainImage']['asset']['_ref'] ?? '';
        $p_img_file = resolve_image_asset($db, $p_img_ref, $download_dir, $images_dir);

        $p_excerpt = portable_text_to_markdown($p_doc['excerpt'] ?? '');
        $p_body = portable_text_to_markdown($p_doc['body'] ?? '');
        $show_on_frontpage = isset($section_subpage_ids[$p_id]);
        $published_at = $p_doc['publishedAt'] ?? date('c');

        // Build Markdown File Content with Frontmatter
        $md_content = "---\n";
        $md_content .= "title: " . json_encode($p_title, JSON_UNESCAPED_UNICODE) . "\n";
        $md_content .= "slug: " . json_encode($p_slug, JSON_UNESCAPED_UNICODE) . "\n";
        $md_content .= "short_slug: " . json_encode($short_slug, JSON_UNESCAPED_UNICODE) . "\n";
        $md_content .= "main_image: " . json_encode($p_img_file ?: $p_img_ref, JSON_UNESCAPED_UNICODE) . "\n";
        $md_content .= "excerpt: " . json_encode($p_excerpt, JSON_UNESCAPED_UNICODE) . "\n";
        $md_content .= "show_on_frontpage: " . ($show_on_frontpage ? "true" : "false") . "\n";
        $md_content .= "published_at: " . json_encode($published_at) . "\n";
        $md_content .= "---\n\n";
        $md_content .= $p_body . "\n";

        $subpage_file = $subpages_dir . '/' . $short_slug . '.md';
        file_put_contents($subpage_file, $md_content);
        echo "  📄 Subpage migrated: $short_slug.md (Slug: $p_slug, Title: '$p_title')\n";
        $subpages_found++;
    }

    $house_data['sections_order'] = $sections_order;

    // Save house.json
    $target_house_json = $house_dir . '/house.json';
    file_put_contents($target_house_json, json_encode($house_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "  🏠 House JSON created: $target_house_json\n";
    echo "  ✨ $subpages_found Subpages & " . count($infoboxes) . " Infoboxes migriert.\n\n";
}

echo "🎉 Migration erfolgreich abgeschlossen!\n";
