<?php
layout("default");

?>

<div class="post-title">
  <h1 class="post-title__text">xxx{{ $page.post.title }}</h1>

</div>

<div class="post content-box">
  <div class="post__header">
    <img
      alt="Cover image"
      v-if="$page.post.mainImage"
      :src="$urlForImage($page.post.mainImage, $page.metadata.sanityOptions).width(600).auto('format').url()" />
  </div>

  <? foreach ($page['sections'] as $section) { ?>

    <?= $partial('section', ['section' => $section]) ?>

  <? } ?>

  <block-content
    class="post__content"
    :blocks="$page.post._rawBody"
    v-if="$page.post._rawBody" />

  <div class="post__footer">
    <post-tags :post="$page.post" v-if="$page.post" />
  </div>
</div>
