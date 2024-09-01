<?php
layout("default");
//$img = $ref($page['mainImage']['asset'] ?? "");
$img = $page['mainImage'] ?? "";

$navigation = $page->header;
?>

<div class="post-title">
  <h1 class="post-title__text"><?= $page["title"] ?></h1>

</div>

<div class="post content-box">
  <div class="post__header">
    <? if ($img) { ?>
      <?= $image_tag($img, "main", ["noheight" => 1, "alt" => "main image of post"]) ?>
    <? } ?>

  </div>

  <?= $sanity_text($page["body"]) ?>


  <div class="post__footer">
    <post-tags :post="$page.post" v-if="$page.post" />
  </div>
</div>

