<?php
layout("default");
//$img = $ref($page['mainImage']['asset'] ?? "");
$img = $page['mainImage'] ?? "";
$background = $image_url($img, "main", ["noheight" => 1, "alt" => "main image of post"]);
?>

<!-- Main -->
<article id="main">
  <header>
    <h2><?= $page["title"] ?></h2>
    <p><?= $sanity_text($page["excerpt"]) ?></p>
  </header>
  <section class="wrapper style5">
    <div class="inner">
      <?= $sanity_text($page["body"]) ?>
    </div>
  </section>
</article>