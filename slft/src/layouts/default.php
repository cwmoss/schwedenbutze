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
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="<?= path_asset('/css/main.css', true) ?>" />
		<noscript><link rel="stylesheet" href="<?= path_asset('/css/noscript.css', true) ?>" /></noscript>
	</head>
	<body class="landing is-preload">

		<!-- Page Wrapper -->
			<div id="page-wrapper" style="--bg-img: url(<?=$background?>)">

				<!-- Header -->
        <?= $partial("navigation", ["current"=>$current, "headerclass"=>$headerclass]) ?>

        <!-- Main Content -->
        <?= $content ?> <!-- Default slot -->

				<!-- CTA -->
				<section id="cta" class="wrapper style4">
					<div class="inner">
						<header>
							<h2>Willst Du eine entspannte Zeit in Schweden verbringen?</h2>
							<p>Dann schaue doch gleich nach der Verfügbarkeit eines der Häuser!</p>
						</header>
						<ul class="actions stacked">
							<li><a href="#" class="button fit primary">Buchen</a></li>
							<li><a href="#" class="button fit">Learn More</a></li>
						</ul>
					</div>
				</section>

				<!-- Footer -->
				<?= $partial("footer", ["social"=>$social])?>
				
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