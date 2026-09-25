<?php

/**
 * Schwedenbutze Web Admin - Content Management & Buchungsverwaltung
 */

require_once __DIR__ . '/auth.php';
use slowfoot\admin\auth;

auth::start_session();
$is_logged_in = auth::is_logged_in();
$user = auth::user();
$csrf_token = auth::get_csrf_token();

$houses_config = [
    'dangebo' => ['name' => 'Dångebo', 'url' => '/dangebo'],
    'ringshult' => ['name' => 'Ringshult', 'url' => '/ringshult'],
    'oksankas-gard' => ['name' => 'Oksankas Gård', 'url' => '/oksankas-gard']
];

$allowed_houses = [];
if ($is_logged_in) {
    if ($user['role'] === 'admin') {
        $allowed_houses = $houses_config;
    } else {
        $hid = $user['house_id'] ?? 'dangebo';
        $allowed_houses = [$hid => $houses_config[$hid] ?? ['name' => ucfirst($hid), 'url' => '/' . $hid]];
    }
}

$initial_house = $_GET['house'] ?? array_key_first($allowed_houses) ?? 'dangebo';
if (!isset($allowed_houses[$initial_house])) {
    $initial_house = array_key_first($allowed_houses) ?? 'dangebo';
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schwedenbutze Admin</title>
    <style>
        :root {
            --primary: #1f4e38;
            --primary-dark: #153727;
            --primary-light: #e8f3ed;
            --secondary: #d97724;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --success: #16a34a;
            --success-light: #dcfce7;
            --radius: 8px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Topbar / Navigation */
        .topbar {
            background-color: var(--primary);
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .topbar-brand span.badge {
            background-color: var(--secondary);
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .topbar-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-tag {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .house-select-wrapper select {
            background: #ffffff;
            color: var(--text-dark);
            border: 1px solid var(--border);
            padding: 0.4rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Container & Tabs */
        .container {
            max-width: 1200px;
            margin: 1.5rem auto;
            padding: 0 1rem;
        }

        .tabs-nav {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
            overflow-x: auto;
            white-space: nowrap;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 0.75rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.15s ease-in-out;
        }
        .tab-btn:hover {
            color: var(--primary);
        }
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* Cards & Forms */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        .card-header h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .card-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
            color: var(--text-dark);
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.9rem;
            background: #ffffff;
            color: var(--text-dark);
            font-family: inherit;
            transition: border-color 0.15s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-help {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.6rem 1.1rem;
            border-radius: var(--radius);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        .btn-secondary {
            background-color: #ffffff;
            border-color: var(--border);
            color: var(--text-dark);
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }
        .btn-danger {
            background-color: var(--danger);
            color: #ffffff;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-sm {
            padding: 0.35rem 0.65rem;
            font-size: 0.8rem;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .data-table th, .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .data-table th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .data-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }
        .gallery-item {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.15s ease;
        }
        .gallery-item:hover {
            box-shadow: var(--shadow-md);
        }
        .gallery-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #e2e8f0;
            display: block;
        }
        .gallery-info {
            padding: 0.75rem;
            font-size: 0.75rem;
            flex-grow: 1;
        }
        .gallery-name {
            font-weight: 600;
            word-break: break-all;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
        }
        .gallery-meta {
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }
        .gallery-actions {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        /* Drag & Drop Upload Zone */
        .dropzone {
            border: 2px dashed var(--border-hover);
            border-radius: var(--radius);
            padding: 2.5rem 1rem;
            text-align: center;
            background: #fdfdfd;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dropzone:hover, .dropzone.dragover {
            border-color: var(--primary);
            background: var(--primary-light);
        }
        .dropzone-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Markdown Editor & Toolbar */
        .editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            background: #f1f5f9;
            padding: 0.4rem;
            border: 1px solid var(--border);
            border-bottom: none;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .editor-toolbar button {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }
        .editor-toolbar button:hover {
            background: #e2e8f0;
        }
        .editor-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 768px) {
            .editor-split { grid-template-columns: 1fr; }
        }
        .markdown-textarea {
            width: 100%;
            border-radius: 0 0 var(--radius) var(--radius);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875rem;
            line-height: 1.6;
            min-height: 320px;
        }
        .markdown-preview {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            background: #ffffff;
            min-height: 320px;
            overflow-y: auto;
            font-size: 0.9rem;
        }
        .markdown-preview h1, .markdown-preview h2, .markdown-preview h3 {
            margin: 1rem 0 0.5rem 0;
            color: var(--text-dark);
        }
        .markdown-preview p { margin-bottom: 0.75rem; }
        .markdown-preview ul, .markdown-preview ol { margin-left: 1.5rem; margin-bottom: 0.75rem; }
        .markdown-preview img { max-width: 100%; height: auto; border-radius: 4px; margin: 0.5rem 0; }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 9999;
        }
        .toast {
            background: #1e293b;
            color: #ffffff;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.2s ease-out;
        }
        .toast-success { background: var(--success); }
        .toast-error { background: var(--danger); }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Login Screen */
        .login-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 1rem;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .hidden { display: none !important; }
    </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
<!-- ========================================== -->
<!-- 1. LOGIN SCREEN -->
<!-- ========================================== -->
<div class="login-wrap">
    <div class="login-card">
        <div class="login-header">
            <h1>🇸🇪 Schwedenbutze Admin</h1>
            <p>Bitte melden Sie sich mit Ihren Zugangsdaten an.</p>
        </div>
        <div id="login-error" class="card" style="background: var(--danger-light); color: var(--danger); display: none; padding: 0.75rem; margin-bottom: 1rem; font-size: 0.875rem;"></div>
        <form id="login-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required autofocus placeholder="z. B. dangebo oder admin">
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required placeholder="••••••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" id="login-submit-btn">
                Anmelden
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('login-submit-btn');
    const errBox = document.getElementById('login-error');
    errBox.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Wird angemeldet...';

    const formData = new FormData(e.target);
    try {
        const res = await fetch('/admin/api/login.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        if (json.success) {
            window.location.reload();
        } else {
            errBox.textContent = json.error || 'Anmeldung fehlgeschlagen.';
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Anmelden';
        }
    } catch (err) {
        errBox.textContent = 'Netzwerkfehler: ' + err.message;
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Anmelden';
    }
});
</script>

<?php else: ?>
<!-- ========================================== -->
<!-- 2. ADMIN DASHBOARD & EDITOREN -->
<!-- ========================================== -->
<div class="topbar">
    <div class="topbar-brand">
        <span>🇸🇪 Schwedenbutze Admin</span>
        <span class="badge"><?= htmlspecialchars(strtoupper($user['role'])) ?></span>
    </div>

    <div class="topbar-controls">
        <?php if (count($allowed_houses) > 1): ?>
        <div class="house-select-wrapper">
            <select id="house-selector">
                <?php foreach ($allowed_houses as $slug => $info): ?>
                    <option value="<?= htmlspecialchars($slug) ?>" <?= ($slug === $initial_house) ? 'selected' : '' ?>>
                        🏡 <?= htmlspecialchars($info['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <span style="font-weight: 600;">🏡 <?= htmlspecialchars($allowed_houses[$initial_house]['name'] ?? ucfirst($initial_house)) ?></span>
        <?php endif; ?>

        <span class="user-tag">👤 <?= htmlspecialchars($user['name'] ?? $user['username']) ?></span>
        <a id="public-link" href="/<?= htmlspecialchars($initial_house) ?>" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: none;">
            🌐 Seite öffnen
        </a>
        <button id="logout-btn" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: none;">
            Abmelden
        </button>
    </div>
</div>

<div class="container">
    <!-- Tab Navigation -->
    <nav class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-details">🏠 Stammdaten & Preise</button>
        <button class="tab-btn" data-tab="tab-calendar">📅 Belegungskalender</button>
        <button class="tab-btn" data-tab="tab-hero">🏡 Hauptseite & Hero</button>
        <button class="tab-btn" data-tab="tab-subpages">📄 Unterseiten-CMS</button>
        <button class="tab-btn" data-tab="tab-gallery">🖼️ Bildergalerie</button>
    </nav>

    <!-- ==================== TAB 1: STAMMDATEN & PREISE ==================== -->
    <div id="tab-details" class="tab-panel">
        <form id="form-details" class="card">
            <div class="card-header">
                <div>
                    <h2>Stammdaten, Belegung & Preise</h2>
                    <p>Grundeinstellungen, Gästekapazitäten und Übernachtungspreise</p>
                </div>
                <button type="submit" class="btn btn-primary">💾 Änderungen speichern</button>
            </div>

            <div class="grid-2">
                <div>
                    <div class="form-group">
                        <label for="detail-title">Hausname / Titel *</label>
                        <input type="text" id="detail-title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="detail-teaser">Kurzer Teaser-Text</label>
                        <textarea id="detail-teaser" name="teaser" rows="2"></textarea>
                        <div class="form-help">Wird in Übersichtslisten und Meta-Tags verwendet.</div>
                    </div>

                    <h3 style="font-size: 1rem; margin: 1.5rem 0 0.75rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.3rem;">
                        Vermieter-Kontaktdaten
                    </h3>

                    <div class="form-group">
                        <label for="landlord-name">Name des Vermieters</label>
                        <input type="text" id="landlord-name" name="landlord[name]">
                    </div>

                    <div class="form-group">
                        <label for="landlord-email">E-Mail für Buchungsanfragen *</label>
                        <input type="email" id="landlord-email" name="landlord[email]" required>
                        <div class="form-help">An diese Adresse werden Gästeanfragen weitergeleitet.</div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="landlord-phone">Telefonnummer</label>
                            <input type="text" id="landlord-phone" name="landlord[phone]">
                        </div>
                        <div class="form-group">
                            <label for="landlord-cc">CC-Benachrichtigung (optional)</label>
                            <input type="email" id="landlord-cc" name="landlord[notify_cc]">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 style="font-size: 1rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.3rem;">
                        Kapazitäten & Mindestaufenthalt
                    </h3>

                    <div class="grid-3">
                        <div class="form-group">
                            <label for="detail-guests">Max. Gäste</label>
                            <input type="number" id="detail-guests" name="details[max_guests]" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="detail-bedrooms">Schlafzimmer</label>
                            <input type="number" id="detail-bedrooms" name="details[bedrooms]" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="detail-bathrooms">Badezimmer</label>
                            <input type="number" id="detail-bathrooms" name="details[bathrooms]" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="detail-minstay">Mindestaufenthalt (Nächte)</label>
                        <input type="number" id="detail-minstay" name="details[min_stay_nights]" min="1" required>
                    </div>

                    <h3 style="font-size: 1rem; margin: 1.5rem 0 0.75rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.3rem;">
                        Preise & Nebenkosten
                    </h3>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="detail-price">Preis pro Nacht (€)</label>
                            <input type="number" id="detail-price" name="details[price_per_night_eur]" min="0" step="1" required>
                        </div>
                        <div class="form-group">
                            <label for="detail-cleaning">Endreinigung (€)</label>
                            <input type="number" id="detail-cleaning" name="details[cleaning_fee_eur]" min="0" step="1" required>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ==================== TAB 2: BELEGUNGSKALENDER ==================== -->
    <div id="tab-calendar" class="tab-panel hidden">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>Google Kalender & iCal Synchronisation</h2>
                    <p>Verknüpfen Sie Ihren externen Kalender zur automatischen Synchronisation freier Termine.</p>
                </div>
                <button type="button" class="btn btn-primary" id="save-ical-btn">💾 iCal-URL speichern</button>
            </div>
            <div class="form-group">
                <label for="cal-ical-url">iCal-Export-URL (z. B. Google Kalender geheime iCal-Adresse)</label>
                <input type="url" id="cal-ical-url" placeholder="https://calendar.google.com/calendar/ical/.../basic.ics">
                <div class="form-help">
                    Termine aus diesem Kalender werden automatisch als belegt im Buchungskalender der Website markiert.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>Manuelle Sperrzeiträume</h2>
                    <p>Sperren Sie Urlaubszeiten oder Eigenbelegungen direkt.</p>
                </div>
            </div>

            <form id="add-block-form" style="background: #f8fafc; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid var(--border);">
                <div class="grid-3" style="align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="new-block-from">Anreise / Von *</label>
                        <input type="date" id="new-block-from" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="new-block-to">Abreise / Bis *</label>
                        <input type="date" id="new-block-to" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="new-block-note">Notiz / Anlass</label>
                        <input type="text" id="new-block-note" placeholder="z. B. Sommerurlaub Fam. Schmidt">
                    </div>
                </div>
                <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">➕ Zeitraum sperren</button>
                </div>
            </form>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Notiz</th>
                        <th style="width: 100px; text-align: right;">Aktion</th>
                    </tr>
                </thead>
                <tbody id="blocked-dates-tbody">
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Lade Sperrzeiten...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB 3: HAUPTSEITE & HERO ==================== -->
    <div id="tab-hero" class="tab-panel hidden">
        <form id="form-hero" class="card">
            <div class="card-header">
                <div>
                    <h2>Hauptseite: Hero-Banner & Infoboxen</h2>
                    <p>Texte und Kacheln auf der Hauptseite dieses Ferienhauses.</p>
                </div>
                <button type="submit" class="btn btn-primary">💾 Hauptseite speichern</button>
            </div>

            <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.3rem;">
                Hero-Bereich (Begrüßungsbanner)
            </h3>

            <div class="grid-2">
                <div class="form-group">
                    <label for="hero-image">Hero-Hintergrundbild</label>
                    <input type="text" id="hero-image" name="hero[image]" placeholder="z. B. IMG_7837.jpeg">
                    <div class="form-help">Wählen Sie einen Dateinamen aus der Bildergalerie.</div>
                </div>
                <div class="form-group">
                    <label for="hero-teaser">Hero-Teaser</label>
                    <input type="text" id="hero-teaser" name="hero[teaser]">
                </div>
            </div>

            <div class="form-group">
                <label for="hero-body">Hero-Fließtext (Markdown)</label>
                <textarea id="hero-body" name="hero[body_markdown]" rows="6" style="font-family: monospace;"></textarea>
            </div>

            <h3 style="font-size: 1.1rem; margin: 2rem 0 0.75rem 0; border-bottom: 1px solid var(--border); padding-bottom: 0.3rem;">
                Highlights & Infoboxen (6 Kacheln)
            </h3>

            <div class="grid-2">
                <div class="form-group">
                    <label for="info-title">Abschnitts-Titel</label>
                    <input type="text" id="info-title" name="infotitle">
                </div>
                <div class="form-group">
                    <label for="info-headline">Abschnitts-Einleitung</label>
                    <input type="text" id="info-headline" name="infoheadline">
                </div>
            </div>

            <div id="infoboxes-container" class="grid-2" style="margin-top: 1rem;">
                <!-- 6 Infoboxen werden dynamisch gerendert -->
            </div>
        </form>
    </div>

    <!-- ==================== TAB 4: UNTERSEITEN (CMS) ==================== -->
    <div id="tab-subpages" class="tab-panel hidden">
        <!-- Unterseiten-Liste -->
        <div id="subpages-list-view" class="card">
            <div class="card-header">
                <div>
                    <h2>Unterseiten verwalten</h2>
                    <p>Erstellen, bearbeiten und formatieren Sie zusätzliche Haus-Unterseiten (z. B. Sauna, Umgebung, Boot).</p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-new-subpage">➕ Neue Unterseite anlegen</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>URL-Kürzel</th>
                        <th>Hauptbild</th>
                        <th>Startseite</th>
                        <th style="width: 180px; text-align: right;">Aktionen</th>
                    </tr>
                </thead>
                <tbody id="subpages-tbody">
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Lade Unterseiten...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Unterseiten-Editor (Modal/View) -->
        <div id="subpage-editor-view" class="card hidden">
            <div class="card-header">
                <div>
                    <h2 id="editor-heading">Unterseite bearbeiten</h2>
                    <p id="editor-subheading">Passen Sie Titel, Metadaten und Inhalt im Markdown-Editor an.</p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" id="btn-cancel-subpage">Abbrechen</button>
                    <button type="button" class="btn btn-primary" id="btn-save-subpage">💾 Unterseite speichern</button>
                </div>
            </div>

            <form id="form-subpage">
                <input type="hidden" id="edit-original-slug" value="">

                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit-subpage-title">Titel der Seite *</label>
                        <input type="text" id="edit-subpage-title" required placeholder="z. B. Die Sauna">
                    </div>
                    <div class="form-group">
                        <label for="edit-subpage-slug">URL-Kürzel (Slug) *</label>
                        <input type="text" id="edit-subpage-slug" required placeholder="z. B. sauna">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="edit-subpage-image">Hauptbild (Dateiname aus Galerie)</label>
                        <input type="text" id="edit-subpage-image" placeholder="z. B. IMG_8208.jpeg">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.8rem;">
                        <input type="checkbox" id="edit-subpage-frontpage" style="width: 18px; height: 18px;">
                        <label for="edit-subpage-frontpage" style="margin-bottom: 0; cursor: pointer;">
                            Als Teaser auf der Haus-Hauptseite anzeigen
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit-subpage-excerpt">Kurzbeschreibung (Teaser-Text)</label>
                    <textarea id="edit-subpage-excerpt" rows="2" placeholder="Kurze Einführung für Teaser-Kacheln..."></textarea>
                </div>

                <!-- Markdown Editor mit Toolbar -->
                <div class="form-group">
                    <label>Inhalt (Markdown & Formatierung)</label>
                    <div class="editor-toolbar">
                        <button type="button" onclick="insertMd('**', '**', 'fetter Text')"><b>B</b></button>
                        <button type="button" onclick="insertMd('*', '*', 'kursiver Text')"><i>I</i></button>
                        <button type="button" onclick="insertMd('## ', '', 'Überschrift 2')">H2</button>
                        <button type="button" onclick="insertMd('### ', '', 'Überschrift 3')">H3</button>
                        <button type="button" onclick="insertMd('- ', '', 'Listenpunkt')">• Liste</button>
                        <button type="button" onclick="insertMd('[', '](https://...)', 'Link-Text')">🔗 Link</button>
                        <button type="button" onclick="insertMd('![', '](bild.jpg)', 'Bildbeschreibung')">🖼️ Bild</button>
                    </div>

                    <div class="editor-split">
                        <textarea id="edit-subpage-body" class="markdown-textarea" placeholder="Schreiben Sie hier Ihren Text mit Markdown..."></textarea>
                        <div id="subpage-preview" class="markdown-preview">
                            <span style="color: var(--text-muted);">Vorschau erscheint hier beim Tippen...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== TAB 5: BILDERGALERIE ==================== -->
    <div id="tab-gallery" class="tab-panel hidden">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>Bildergalerie & Upload</h2>
                    <p>Laden Sie Fotos für dieses Haus hoch. Klicken Sie auf ein Bild, um den Markdown-Code für den Editor zu kopieren.</p>
                </div>
            </div>

            <!-- Drag & Drop Zone -->
            <div id="upload-dropzone" class="dropzone">
                <div class="dropzone-icon">📤</div>
                <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;">
                    Bilder hierher ziehen oder zum Auswählen klicken
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Unterstützt JPEG, PNG, WebP bis maximal 15 MB. Bilder werden für das Web optimiert.
                </div>
                <input type="file" id="file-input" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
            </div>

            <!-- Galerie Grid -->
            <div id="gallery-grid" class="gallery-grid">
                <!-- Dynamische Bild-Karten -->
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div id="toast-container" class="toast-container"></div>

<script>
// State Management
let currentHouse = '<?= htmlspecialchars($initial_house) ?>';
let csrfToken = '<?= htmlspecialchars($csrf_token) ?>';
let houseData = null;
let currentSubpages = [];
let galleryImages = [];

// Simple Markdown Parser für Live-Vorschau
function renderMarkdown(md) {
    if (!md) return '<span style="color: var(--text-muted);">Kein Inhalt vorhanden.</span>';
    let html = md
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/gim, '<em>$1</em>')
        .replace(/!\[(.*?)\]\((.*?)\)/gim, (match, alt, src) => {
            const imgSrc = src.startsWith('http') || src.startsWith('/') ? src : `/houses/${currentHouse}/images/${src}`;
            return `<img src="${imgSrc}" alt="${alt}">`;
        })
        .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2" target="_blank">$1</a>')
        .replace(/^\s*-\s+(.*$)/gim, '<li>$1</li>')
        .replace(/\n\n+/g, '<br><br>');
    return html;
}

// Toast Notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = (type === 'success' ? '✅ ' : '❌ ') + message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Tab Switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        btn.classList.add('active');
        const targetId = btn.getAttribute('data-tab');
        document.getElementById(targetId).classList.remove('hidden');
        window.location.hash = targetId.replace('tab-', '');
    });
});

// Restore active tab from hash
if (window.location.hash) {
    const hashTab = 'tab-' + window.location.hash.replace('#', '');
    const matchingBtn = document.querySelector(`[data-tab="${hashTab}"]`);
    if (matchingBtn) matchingBtn.click();
}

// House Selector change
const houseSelector = document.getElementById('house-selector');
if (houseSelector) {
    houseSelector.addEventListener('change', (e) => {
        currentHouse = e.target.value;
        document.getElementById('public-link').href = `/${currentHouse}`;
        loadAllData();
    });
}

// Logout
document.getElementById('logout-btn').addEventListener('click', async () => {
    await fetch('/admin/api/logout.php', { method: 'POST' });
    window.location.reload();
});

// ==========================================
// DATA LOADING
// ==========================================
async function loadAllData() {
    await Promise.all([loadHouse(), loadSubpages(), loadGallery()]);
}

async function loadHouse() {
    try {
        const res = await fetch(`/admin/api/house.php?house=${currentHouse}`);
        const data = await res.json();
        if (data.success) {
            houseData = data.house;
            populateDetails(houseData);
            populateCalendar(houseData);
            populateHero(houseData);
        } else {
            showToast(data.error || 'Fehler beim Laden der Hausdaten.', 'error');
        }
    } catch (err) {
        showToast('Verbindungsfehler: ' + err.message, 'error');
    }
}

function populateDetails(h) {
    document.getElementById('detail-title').value = h.title || '';
    document.getElementById('detail-teaser').value = h.teaser || '';
    document.getElementById('landlord-name').value = h.landlord?.name || '';
    document.getElementById('landlord-email').value = h.landlord?.email || '';
    document.getElementById('landlord-phone').value = h.landlord?.phone || '';
    document.getElementById('landlord-cc').value = h.landlord?.notify_cc || '';

    const d = h.details || {};
    document.getElementById('detail-guests').value = d.max_guests || 1;
    document.getElementById('detail-bedrooms').value = d.bedrooms || 1;
    document.getElementById('detail-bathrooms').value = d.bathrooms || 1;
    document.getElementById('detail-minstay').value = d.min_stay_nights || 1;
    document.getElementById('detail-price').value = d.price_per_night_eur || 0;
    document.getElementById('detail-cleaning').value = d.cleaning_fee_eur || 0;
}

function populateCalendar(h) {
    document.getElementById('cal-ical-url').value = h.calendars?.ical_url || '';
    renderBlockedDates(h.blocked_dates || []);
}

function renderBlockedDates(ranges) {
    const tbody = document.getElementById('blocked-dates-tbody');
    if (!ranges || ranges.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Keine manuellen Sperrzeiten eingetragen.</td></tr>';
        return;
    }

    tbody.innerHTML = ranges.map((r, idx) => `
        <tr>
            <td><strong>${r.from}</strong></td>
            <td><strong>${r.to}</strong></td>
            <td>${r.note || '<span style="color:var(--text-muted);">-</span>'}</td>
            <td style="text-align: right;">
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteBlockedDate(${idx})">🗑️ Löschen</button>
            </td>
        </tr>
    `).join('');
}

function populateHero(h) {
    document.getElementById('hero-image').value = h.hero?.image || '';
    document.getElementById('hero-teaser').value = h.hero?.teaser || '';
    document.getElementById('hero-body').value = h.hero?.body_markdown || '';
    document.getElementById('info-title').value = h.infotitle || '';
    document.getElementById('info-headline').value = h.infoheadline || '';

    const boxContainer = document.getElementById('infoboxes-container');
    const boxes = h.infoboxes || [];
    const iconOptions = ['fa-sun', 'fa-hiking', 'fa-home', 'fa-water', 'fa-hot-tub', 'fa-star', 'fa-tree', 'fa-heart', 'fa-fish', 'fa-bicycle'];

    boxContainer.innerHTML = '';
    for (let i = 0; i < 6; i++) {
        const b = boxes[i] || { icon: 'fa-star', title: '', text: '' };
        const boxHtml = `
            <div class="card" style="margin-bottom: 0; background: #fafafa; border: 1px solid var(--border);">
                <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem; color: var(--primary);">Kachel ${i + 1}</div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="font-size: 0.75rem;">Icon</label>
                    <select class="infobox-icon" data-index="${i}">
                        ${iconOptions.map(ico => `<option value="${ico}" ${ico === b.icon ? 'selected' : ''}>${ico}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="font-size: 0.75rem;">Titel</label>
                    <input type="text" class="infobox-title" data-index="${i}" value="${b.title || ''}" placeholder="z. B. Sauna">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.75rem;">Text</label>
                    <textarea class="infobox-text" data-index="${i}" rows="2" placeholder="Kurze Beschreibung...">${b.text || ''}</textarea>
                </div>
            </div>
        `;
        boxContainer.insertAdjacentHTML('beforeend', boxHtml);
    }
}

// ==========================================
// SAVE HANDLERS
// ==========================================
async function saveHousePayload(payload) {
    try {
        const res = await fetch(`/admin/api/house.php?house=${currentHouse}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            houseData = data.house;
            showToast(data.message || 'Erfolgreich gespeichert!');
            return true;
        } else {
            showToast(data.error || 'Fehler beim Speichern.', 'error');
            return false;
        }
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
        return false;
    }
}

// Tab 1 Submit
document.getElementById('form-details').addEventListener('submit', async (e) => {
    e.preventDefault();
    const updated = {
        ...houseData,
        title: document.getElementById('detail-title').value,
        teaser: document.getElementById('detail-teaser').value,
        landlord: {
            name: document.getElementById('landlord-name').value,
            email: document.getElementById('landlord-email').value,
            phone: document.getElementById('landlord-phone').value,
            notify_cc: document.getElementById('landlord-cc').value
        },
        details: {
            max_guests: parseInt(document.getElementById('detail-guests').value),
            bedrooms: parseInt(document.getElementById('detail-bedrooms').value),
            bathrooms: parseInt(document.getElementById('detail-bathrooms').value),
            min_stay_nights: parseInt(document.getElementById('detail-minstay').value),
            price_per_night_eur: parseFloat(document.getElementById('detail-price').value),
            cleaning_fee_eur: parseFloat(document.getElementById('detail-cleaning').value)
        }
    };
    await saveHousePayload(updated);
});

// Tab 2: Save iCal
document.getElementById('save-ical-btn').addEventListener('click', async () => {
    const updated = {
        ...houseData,
        calendars: {
            ...(houseData?.calendars || {}),
            ical_url: document.getElementById('cal-ical-url').value
        }
    };
    await saveHousePayload(updated);
});

// Tab 2: Add Blocked Date
document.getElementById('add-block-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const from = document.getElementById('new-block-from').value;
    const to = document.getElementById('new-block-to').value;
    const note = document.getElementById('new-block-note').value;

    if (!from || !to) {
        showToast('Bitte Anreise- und Abreisedatum angeben.', 'error');
        return;
    }
    if (from >= to) {
        showToast('Das Abreisedatum muss nach dem Anreisedatum liegen.', 'error');
        return;
    }

    const currentBlocks = [...(houseData?.blocked_dates || [])];
    currentBlocks.push({ from, to, note });

    const updated = {
        ...houseData,
        blocked_dates: currentBlocks
    };

    if (await saveHousePayload(updated)) {
        document.getElementById('new-block-from').value = '';
        document.getElementById('new-block-to').value = '';
        document.getElementById('new-block-note').value = '';
        renderBlockedDates(currentBlocks);
    }
});

// Tab 2: Delete Blocked Date
async function deleteBlockedDate(idx) {
    if (!confirm('Diesen Sperrzeitraum wirklich löschen?')) return;
    const currentBlocks = [...(houseData?.blocked_dates || [])];
    currentBlocks.splice(idx, 1);
    const updated = {
        ...houseData,
        blocked_dates: currentBlocks
    };
    if (await saveHousePayload(updated)) {
        renderBlockedDates(currentBlocks);
    }
}

// Tab 3: Save Hero & Infoboxes
document.getElementById('form-hero').addEventListener('submit', async (e) => {
    e.preventDefault();
    const boxes = [];
    for (let i = 0; i < 6; i++) {
        const icon = document.querySelector(`.infobox-icon[data-index="${i}"]`)?.value || 'fa-star';
        const title = document.querySelector(`.infobox-title[data-index="${i}"]`)?.value || '';
        const text = document.querySelector(`.infobox-text[data-index="${i}"]`)?.value || '';
        if (title || text) {
            boxes.push({ icon, title, text });
        }
    }

    const updated = {
        ...houseData,
        hero: {
            image: document.getElementById('hero-image').value,
            teaser: document.getElementById('hero-teaser').value,
            body_markdown: document.getElementById('hero-body').value
        },
        infotitle: document.getElementById('info-title').value,
        infoheadline: document.getElementById('info-headline').value,
        infoboxes: boxes
    };
    await saveHousePayload(updated);
});

// ==========================================
// SUBPAGES CMS
// ==========================================
async function loadSubpages() {
    try {
        const res = await fetch(`/admin/api/subpages.php?house=${currentHouse}`);
        const data = await res.json();
        if (data.success) {
            currentSubpages = data.subpages || [];
            renderSubpagesList(currentSubpages);
        }
    } catch (err) {
        showToast('Fehler beim Laden der Unterseiten: ' + err.message, 'error');
    }
}

function renderSubpagesList(pages) {
    const tbody = document.getElementById('subpages-tbody');
    if (!pages || pages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Keine Unterseiten vorhanden. Klicken Sie oben auf "+ Neue Unterseite anlegen".</td></tr>';
        return;
    }

    tbody.innerHTML = pages.map(p => `
        <tr>
            <td><strong>${p.title}</strong></td>
            <td><code>${p.short_slug}</code></td>
            <td>${p.main_image || '<span style="color:var(--text-muted);">-</span>'}</td>
            <td>${p.show_on_frontpage ? '✅ Ja' : 'Nein'}</td>
            <td style="text-align: right;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="editSubpage('${p.short_slug}')">✏️ Bearbeiten</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSubpage('${p.short_slug}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

// Markdown Live Preview Listener
document.getElementById('edit-subpage-body').addEventListener('input', (e) => {
    document.getElementById('subpage-preview').innerHTML = renderMarkdown(e.target.value);
});

// New Subpage Button
document.getElementById('btn-new-subpage').addEventListener('click', () => {
    document.getElementById('editor-heading').textContent = 'Neue Unterseite anlegen';
    document.getElementById('editor-subheading').textContent = 'Erstellen Sie eine neue formatierte Unterseite für dieses Haus.';
    document.getElementById('edit-original-slug').value = '';
    document.getElementById('edit-subpage-title').value = '';
    document.getElementById('edit-subpage-slug').value = '';
    document.getElementById('edit-subpage-image').value = '';
    document.getElementById('edit-subpage-frontpage').checked = true;
    document.getElementById('edit-subpage-excerpt').value = '';
    document.getElementById('edit-subpage-body').value = '## Neuer Abschnitt\n\nHier Text eingeben...';
    document.getElementById('subpage-preview').innerHTML = renderMarkdown('## Neuer Abschnitt\n\nHier Text eingeben...');

    document.getElementById('subpages-list-view').classList.add('hidden');
    document.getElementById('subpage-editor-view').classList.remove('hidden');
});

// Cancel Subpage
document.getElementById('btn-cancel-subpage').addEventListener('click', () => {
    document.getElementById('subpage-editor-view').classList.add('hidden');
    document.getElementById('subpages-list-view').classList.remove('hidden');
});

// Edit Subpage
async function editSubpage(shortSlug) {
    try {
        const res = await fetch(`/admin/api/subpages.php?house=${currentHouse}&page=${shortSlug}`);
        const data = await res.json();
        if (data.success && data.subpage) {
            const p = data.subpage;
            document.getElementById('editor-heading').textContent = `Unterseite bearbeiten: ${p.title}`;
            document.getElementById('editor-subheading').textContent = `Bearbeiten Sie den Inhalt und die Metadaten von "${p.short_slug}.md".`;
            document.getElementById('edit-original-slug').value = p.short_slug;
            document.getElementById('edit-subpage-title').value = p.title || '';
            document.getElementById('edit-subpage-slug').value = p.short_slug || '';
            document.getElementById('edit-subpage-image').value = p.main_image || '';
            document.getElementById('edit-subpage-frontpage').checked = !!p.show_on_frontpage;
            document.getElementById('edit-subpage-excerpt').value = p.excerpt || '';
            document.getElementById('edit-subpage-body').value = p.body || '';
            document.getElementById('subpage-preview').innerHTML = renderMarkdown(p.body || '');

            document.getElementById('subpages-list-view').classList.add('hidden');
            document.getElementById('subpage-editor-view').classList.remove('hidden');
        } else {
            showToast(data.error || 'Fehler beim Laden der Seite.', 'error');
        }
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

// Save Subpage
document.getElementById('btn-save-subpage').addEventListener('click', async () => {
    const origSlug = document.getElementById('edit-original-slug').value;
    const title = document.getElementById('edit-subpage-title').value.trim();
    const shortSlug = document.getElementById('edit-subpage-slug').value.trim();
    const mainImage = document.getElementById('edit-subpage-image').value.trim();
    const showOnFront = document.getElementById('edit-subpage-frontpage').checked;
    const excerpt = document.getElementById('edit-subpage-excerpt').value.trim();
    const body = document.getElementById('edit-subpage-body').value;

    if (!title) {
        showToast('Bitte geben Sie einen Seitentitel an.', 'error');
        return;
    }

    const payload = {
        title,
        short_slug: shortSlug,
        main_image: mainImage,
        show_on_frontpage: showOnFront,
        excerpt,
        body
    };

    const isEdit = !!origSlug;
    const url = isEdit ? `/admin/api/subpages.php?house=${currentHouse}&page=${origSlug}` : `/admin/api/subpages.php?house=${currentHouse}`;
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Unterseite erfolgreich gespeichert!');
            document.getElementById('subpage-editor-view').classList.add('hidden');
            document.getElementById('subpages-list-view').classList.remove('hidden');
            loadSubpages();
        } else {
            showToast(data.error || 'Fehler beim Speichern der Unterseite.', 'error');
        }
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
});

// Delete Subpage
async function deleteSubpage(shortSlug) {
    if (!confirm(`Möchten Sie die Unterseite "${shortSlug}" wirklich unwiderruflich löschen?`)) return;
    try {
        const res = await fetch(`/admin/api/subpages.php?house=${currentHouse}&page=${shortSlug}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Unterseite gelöscht.');
            loadSubpages();
        } else {
            showToast(data.error || 'Fehler beim Löschen.', 'error');
        }
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

// Markdown Toolbar Helper
function insertMd(prefix, suffix, defaultText) {
    const textarea = document.getElementById('edit-subpage-body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end) || defaultText;
    const replacement = prefix + selected + suffix;
    textarea.value = text.substring(0, start) + replacement + text.substring(end);
    textarea.focus();
    textarea.selectionStart = start + prefix.length;
    textarea.selectionEnd = start + prefix.length + selected.length;
    document.getElementById('subpage-preview').innerHTML = renderMarkdown(textarea.value);
}

// ==========================================
// GALLERY & UPLOAD
// ==========================================
async function loadGallery() {
    try {
        const res = await fetch(`/admin/api/images.php?house=${currentHouse}`);
        const data = await res.json();
        if (data.success) {
            galleryImages = data.images || [];
            renderGallery(galleryImages);
        }
    } catch (err) {
        showToast('Fehler beim Laden der Galerie: ' + err.message, 'error');
    }
}

function renderGallery(images) {
    const grid = document.getElementById('gallery-grid');
    if (!images || images.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 2rem;">Noch keine Bilder hochgeladen. Ziehen Sie Bilder in das Upload-Feld oben.</div>';
        return;
    }

    grid.innerHTML = images.map(img => `
        <div class="gallery-item">
            <img class="gallery-thumb" src="${img.url}" alt="${img.filename}" loading="lazy">
            <div class="gallery-info">
                <div class="gallery-name">${img.filename}</div>
                <div class="gallery-meta">${Math.round(img.size_bytes / 1024)} KB ${img.dimensions.width ? `• ${img.dimensions.width}x${img.dimensions.height}` : ''}</div>
            </div>
            <div class="gallery-actions">
                <button type="button" class="btn btn-secondary btn-sm" style="flex-grow: 1;" onclick="copyMarkdown('${img.filename}')">📋 Kopieren</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteImage('${img.filename}')">🗑️</button>
            </div>
        </div>
    `).join('');
}

function copyMarkdown(filename) {
    const md = `![${filename.replace(/\.[^/.]+$/, "")}](${filename})`;
    navigator.clipboard.writeText(md).then(() => {
        showToast(`Code kopiert: ${md}`);
    }).catch(() => {
        showToast('Kopieren fehlgeschlagen.', 'error');
    });
}

async function deleteImage(filename) {
    if (!confirm(`Möchten Sie das Bild "${filename}" wirklich löschen?`)) return;
    try {
        const res = await fetch(`/admin/api/images.php?house=${currentHouse}&file=${encodeURIComponent(filename)}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Bild gelöscht.');
            loadGallery();
        } else {
            showToast(data.error || 'Fehler beim Löschen.', 'error');
        }
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

// Drag & Drop Upload
const dropzone = document.getElementById('upload-dropzone');
const fileInput = document.getElementById('file-input');

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files?.length) {
        uploadFiles(e.dataTransfer.files);
    }
});
fileInput.addEventListener('change', () => {
    if (fileInput.files?.length) {
        uploadFiles(fileInput.files);
    }
});

async function uploadFiles(files) {
    for (const file of files) {
        const formData = new FormData();
        formData.append('image', file);

        try {
            showToast(`Lade "${file.name}" hoch...`);
            const res = await fetch(`/admin/api/upload.php?house=${currentHouse}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(`"${file.name}" erfolgreich hochgeladen!`);
            } else {
                showToast(`Fehler bei "${file.name}": ${data.error}`, 'error');
            }
        } catch (err) {
            showToast(`Upload-Fehler bei "${file.name}": ${err.message}`, 'error');
        }
    }
    fileInput.value = '';
    loadGallery();
}

// Initial Data Load on Start
loadAllData();
</script>
<?php endif; ?>

</body>
</html>
