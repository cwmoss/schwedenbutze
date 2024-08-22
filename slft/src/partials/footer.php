<?php
$footer = $query1('page(slug.current=="footer")');
debug_js("footer", $footer);
?>

<div class="z-foot">

  <ul>

    <? foreach ($footer["sections"] as $section) {
      $page = $ref($section["ref"]["_ref"]);
    ?><li>
        <a href="<?= $path($section["ref"]["_ref"]) ?>"><?= $section["title"] ?? $page["title"] ?></a>
      </li>
    <? } ?>

  </ul>
</div>
