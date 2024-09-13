<page-query first>

  *(_type=="post" && slug.current=="intro")

</page-query>
<?php
layout("default");
// $page = $page[0];
$img = $ref($page['mainImage'] ?? "");
?>

<div class="post-title">

  <h1 class="post-title__text"><?= $page['title'] ?></h1>

</div>

<div class="post content-box">
  <div class="post__header">
    <? if ($img) { ?>

      <?= $image_tag($img, "main", ["noheight" => 1, "alt" => "Conver Image of The House"]) ?>

    <? } ?>
  </div>


  <?= $sanity_text($page["body"]) ?>


</div>