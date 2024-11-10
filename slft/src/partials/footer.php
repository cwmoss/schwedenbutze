<?php
$footer = $query1('page(slug.current=="footer")');
debug_js("footer", $footer);
?>

<footer id="footer">
  <ul class="icons">
    <? if ($social["twitter"]) { ?>
    <li><a href="<?= $social["twitter"] ?>" class="icon brands fa-twitter"><span class="label">Twitter</span></a></li>
    <? } ?>
    <? if ($social["facebook"]) { ?>
    <li><a href="<?= $social["facebook"] ?>" class="icon brands fa-facebook-f"><span class="label">Facebook</span></a></li>
    <? } ?>
    <? if ($social["insta"]) { ?>
    <li><a href="<?= $social["insta"] ?>" class="icon brands fa-instagram"><span class="label">Instagram</span></a></li>
    <? } ?>
    <? if ($social["dribble"]) { ?>
    <li><a href="<?= $social["dribble"] ?>" class="icon brands fa-dribbble"><span class="label">Dribbble</span></a></li>
    <? } ?>
    <? if ($social["email"]) { ?>
    <li><a href="<?= $social["email"] ?>" class="icon solid fa-envelope"><span class="label">Email</span></a></li>
    <? } ?>
  </ul>
  <ul class="copyright">
    <li>&copy; 2024 Schwedenbutze</li>
    <li>Content Lake: <a href="https://sanity.io">Sanity.io</a></li>
    <li>Site Generator: <a href="https://github.com/cwmoss/slowfoot">slowfoot</a></li>
    <li>Design: <a href="https://html5up.net">HTML5 UP</a></li>
  </ul>
</footer>
