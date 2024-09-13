<?php
/*
/navigation
*/
$navigation = $query1('page(slug.current=="navigation")');
debug_js("navigation", $navigation);

?>
<header id="header" class="alt">
  <h1><a href="/">Home</a></h1>
  <nav id="nav">
    <ul>
      <li class="special">
        <a href="#menu" class="menuToggle"><span>Menu</span></a>
        <div id="menu">
          <ul>
            <? foreach ($navigation["sections"] as $section) {
#foreach ($navigation as $section) {
              $page = $ref($section["ref"]["_ref"]);
            ?><li>
                <a href="<?= $path($section["ref"]["_ref"]) ?>"><?= $section["title"] ?? $page["title"] ?></a>
              </li>
            <? } ?>
          </ul>
        </div>
      </li>
    </ul>
  </nav>
</header>
