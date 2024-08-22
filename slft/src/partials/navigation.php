<?php
/*
/navigation
*/
$navigation = $query1('page(slug.current=="navigation")');
debug_js("navigation", $navigation);

?>
<div class="z-nav">
  <ul>

    <? foreach ($navigation["sections"] as $section) {
      $page = $ref($section["ref"]["_ref"]);
    ?><li>
        <a href="<?= $path($section["ref"]["_ref"]) ?>"><?= $section["title"] ?? $page["title"] ?></a>
      </li>
    <? } ?>

    <li><a href="/booking">Buchung</a></li>
  </ul>
</div>
