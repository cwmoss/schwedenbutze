<?php
layout("default");
//$img = $ref($page['mainImage']['asset'] ?? "");
$img = $page['mainImage'] ?? "";

$navigation = $page->header;
$background = $image_url($img, "main", ["noheight" => 1, "alt" => "main image of post"]);
$headerclass="alt"
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
      <li><span class="icon solid fa-tree major style1"><span class="label">Lorem</span></span></li>
      <li><span class="icon solid fa-heart major style2"><span class="label">Ipsum</span></span></li>
      <li><span class="icon solid fa-umbrella-beach major style1"><span class="label">Dolor</span></span></li>
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
      <h2>Accumsan mus tortor nunc aliquet</h2>
      <p>Aliquam ut ex ut augue consectetur interdum. Donec amet imperdiet eleifend<br />
      fringilla tincidunt. Nullam dui leo Aenean mi ligula, rhoncus ullamcorper.</p>
    </header>
    <ul class="features">
      <li class="icon fa-paper-plane">
        <h3>Arcu accumsan</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
      <li class="icon solid fa-laptop">
        <h3>Ac Augue Eget</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
      <li class="icon solid fa-code">
        <h3>Mus Scelerisque</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
      <li class="icon solid fa-headphones-alt">
        <h3>Mauris Imperdiet</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
      <li class="icon fa-heart">
        <h3>Aenean Primis</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
      <li class="icon fa-flag">
        <h3>Tortor Ut</h3>
        <p>Augue consectetur sed interdum imperdiet et ipsum. Mauris lorem tincidunt nullam amet leo Aenean ligula consequat consequat.</p>
      </li>
    </ul>
  </div>
</section>
