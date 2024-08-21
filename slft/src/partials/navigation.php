<?php
/*
/navigation
*/
$navigation = $query('page(slug.current=="navigation")')[0];
debug_js("navigation", $navigation);

?>
<div class="z-nav">
  <ul>
    <li v-for="section in $static.navigation.sections" :key="section.ref._id">
      <? foreach ($navigation["sections"] as $section) {
        $page = $ref($section["ref"]["_ref"]);
      ?>
        <a href="<?= $path($section["ref"]["_ref"]) ?>"><?= $section["title"] ?? $page["title"] ?></a>
      <? } ?>
    </li>
    <li><a href="/booking">Buchung</a></li>
  </ul>
</div>
