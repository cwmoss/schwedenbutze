<?php

require_once SLOWFOOT_BASE . '/src/lib/utilities.php';

layout("default");
//$img = $ref($page['mainImage']['asset'] ?? "");
$img = $page['mainImage'] ?? "";

$navigation = $page->header;
$background = $image_url($img, "main", ["noheight" => 1, "alt" => "main image of post"]);
$headerclass="alt";
//wrapp socials from page
$social = map_socials($page);
?>

<!-- Banner -->
<section id="banner" style="">
  <div class="inner">
    <h2><?= $page["title"] ?></h2>
    <p><?= $sanity_text($page["excerpt"]) ?></p>
    <ul class="actions special">
      <!--
      <li><a href="#" class="button primary">Activate</a></li>
      -->
    </ul>
  </div>
  <a href="#one" class="more scrolly">Mehr erfahren</a>
</section>

<!-- One -->
<section id="one" class="wrapper style1 special">
  <div class="inner">
    <header class="major">
      <h2><?= $sanity_text($page["excerpt"])?></h2>
      <p><?= $sanity_text($page["body"])?></p>
    </header>
    <ul class="icons major">
      <li><span class="icon solid fa-tree major style1"><span class="label">Natur</span></span></li>
      <li><span class="icon solid fa-heart major style2"><span class="label">Love</span></span></li>
      <li><span class="icon solid fa-umbrella-beach major style1"><span class="label">Erholung</span></span></li>
    </ul>
  </div>
</section>

<!-- Two -->
<!-- Loop thru sections -->
<section id="two" class="wrapper alt style2">
  <? foreach ($page["sections"] as $section) {  
    #foreach ($navigation as $section) {
    $section_page = $ref($section["ref"]["_ref"]);
    $img = $section_page['mainImage'] ?? "";
  ?>
    <section class="spotlight">

    <div class="image"><?=$image_tag($img, "main", ["noheight" => 1, "alt" => "main image of post"]);?></div><div class="content">
      <h2><?= $section["title"] ?? $section_page["title"] ?></h2>
      <p><?= $sanity_text($section_page["excerpt"])?></p>
      <a href="<?= $path($section["ref"]["_ref"]) ?>" class="button">Mehr erfahren</a>
    </div>
  </section>
  <? } ?>
</section>

<!-- Three -->
<section id="three" class="wrapper style3 special">
  <div class="inner">
    <header class="major">
      <h2><?= $page["infotitle"] ?></h2>
      <p><?= $sanity_text($page["infoheadline"]) ?></p>
    </header>
    <ul class="features">
      <? foreach ($page["infoboxes"] as $infobox) {  
        $info = $ref($infobox["ref"]["_ref"]);
      ?>
        <li class="icon solid <?= $info["icon"] ?>">
          <h3><?= $infobox["title"] ?? $info["title"] ?></h3>
          <p><?= $info["info"] ?></p>
        </li>
      <? } ?>
    </ul>
  </div>
</section>
