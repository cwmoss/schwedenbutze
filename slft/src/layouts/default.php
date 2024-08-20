<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $_context->config->site_name ?></title>
  <link rel="stylesheet" href="<?= path_asset('/css/index.css', true) ?>" type="text/css">
  <script src="<?= path_asset('/js/site.js', true) ?>"></script>
</head>

<body>
  <div id="app">
    <header class="header">
      <div class="header__left">
        <header-logo v-if="showLogo" />
        <navigation />
      </div>

      <div class="header__right">

        <toggle-theme />
      </div>
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
        <footer-parts />
      </div>

    </footer>
  </div>

</body>

</html>
