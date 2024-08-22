<?php

$title = $_context->config->site_name;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <link rel="stylesheet" href="<?= path_asset('/css/index.css', true) ?>" type="text/css">
  <script src="<?= path_asset('/js/site.js', true) ?>"></script>
</head>

<body>
  <div id="app">
    <header class="header">
      <div class="header__left">
        <? if (false) { ?>
          <a class="logo" href="/">
            <span class="logo__text">&larr; <?= $title ?></span>
          </a>
        <? } ?>
        <?= $partial("navigation") ?>
      </div>

      <div class="header__right">:)</div>
    </header>

    <main class="main">
      <div>
        <?= $content ?> <!-- Default slot -->
      </div>
    </main>

    <footer class="footer">
      <div class="footer__left">
        Copyright © 2024 - Powered by Zorro
      </div>

      <div class="footer__right">
        <?= $partial("footer") ?>
      </div>

    </footer>
  </div>

</body>

</html>
