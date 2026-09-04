<?php

$title = $_context->config->site_name;
?>

<!DOCTYPE HTML>
<!--
	Spectral by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

<head>
  <title><?= $title ?></title>
  <meta charset="utf-8" />
  <link rel="stylesheet" href="<?= path_asset('/css/main.css', true) ?>" />
  <link rel="stylesheet" href="<?= path_asset('/vendor/flatpickr/flatpickr.min.css', true) ?>" />
  <link rel="stylesheet" href="<?= path_asset('/css/custom.css', true) ?>" />
  <script src="<?= path_asset('/vendor/flatpickr/flatpickr.min.js', true) ?>"></script>
  <script src="<?= path_asset('/vendor/flatpickr/de.js', true) ?>"></script>
  <script src="<?= path_asset('/js/booking-calendar.js', true) ?>"></script>
  <noscript>
    <link rel="stylesheet" href="<?= path_asset('/css/noscript.css', true) ?>" />
  </noscript>
</head>

<body class="landing is-preload">

  <!-- Page Wrapper -->
  <div id="page-wrapper" style="--bg-img: url(<?= $background ?>)">

    <!-- Header -->
    <?= $partial("navigation", ["current" => $current, "headerclass" => $headerclass]) ?>

    <!-- Main Content -->
    <?= $content ?> <!-- Default slot -->

<?php
// Kontext & aktives Haus für CTA ermitteln
$curr_path = strtolower(trim($_context->path ?? ($_context->name ?? ''), '/'));

$house_slug = '';
$house_title = '';
if (str_starts_with($curr_path, 'dangebo')) {
    $house_slug = 'dangebo';
    $house_title = 'Dångebo';
} elseif (str_starts_with($curr_path, 'ringshult')) {
    $house_slug = 'ringshult';
    $house_title = 'Ringshult';
} elseif (str_starts_with($curr_path, 'oksankas')) {
    $house_slug = 'oksankas-gard';
    $house_title = 'Oksankas Gård';
}

$booking_url = $house_slug ? "/buchung?house={$house_slug}" : "/buchung";
$is_booking_page = !empty($is_booking) || ($curr_path === 'buchung') || str_ends_with($curr_path, 'buchung');
?>

    <?php if (!$is_booking_page): ?>
    <!-- CTA -->
    <section id="cta" class="wrapper style4">
      <div class="inner">
        <header>
          <h2>Willst Du eine entspannte Zeit in Schweden verbringen?</h2>
          <p>
            <?= $house_title 
                ? "Prüfe jetzt die Verfügbarkeit für <strong>" . htmlspecialchars($house_title) . "</strong> und sichere dir deinen Wunschurlaub!" 
                : "Dann schaue doch gleich nach der Verfügbarkeit unserer Ferienhäuser!" ?>
          </p>
        </header>
        <ul class="actions stacked">
          <li>
            <a href="<?= htmlspecialchars($booking_url) ?>" class="button fit primary">
              <?= $house_title ? htmlspecialchars($house_title) . " jetzt buchen" : "Jetzt buchen" ?>
            </a>
          </li>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <?= $partial("footer", ["social" => $social]) ?>

  </div>

  <!-- Scripts -->
  <script src="<?= path_asset('/js/jquery.min.js', true) ?>"></script>
  <script src="<?= path_asset('/js/jquery.scrollex.min.js', true) ?>"></script>
  <script src="<?= path_asset('/js/jquery.scrolly.min.js', true) ?>"></script>
  <script src="<?= path_asset('/js/browser.min.js', true) ?>"></script>
  <script src="<?= path_asset('/js/breakpoints.min.js', true) ?>"></script>
  <script src="<?= path_asset('/js/util.js', true) ?>"></script>
  <script src="<?= path_asset('/js/main.js', true) ?>"></script>

</body>

</html>