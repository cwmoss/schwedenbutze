<page-query first>

  *(_type=="post" && slug.current=="booking-content")

</page-query>
<?php
layout("default");
// $page = $page[0];
$img = $ref($page['mainImage']);
?>

<div class="post-title">

  <h1 class="post-title__text"> <?= $page['title'] ?></h1>

</div>

<div class="post content-box">
  <div class="post__header">
    <? if ($img) { ?>

      <?= $image_tag($img, "main", ["noheight" => 1, "alt" => "Conver Image of The House"]) ?>

    <? } ?>
  </div>
  <section class="wrapper">
    <div class="inner">
      <form id="buchungsformular">

        <p><label for="aufenthalt">Zeitraum:</label>
          <b-date-range id="rangepicker" fetchreserved="https://jsonplaceholder.typicode.com/todos/1">
            <input type="text" id="aufenthalt">
          </b-date-range>
        </p>


        <p>
          <label for="name">Dein Name:</label>
          <input id="name" name="name" type="text" required>
        </p>

        <p>
          <label for="email">Deine E-Mail-Adresse:</label>
          <input id="email" name="email" type="email" required>
        </p>

        <p>
          <label for="message">Nachricht (optional):</label>
          <textarea id="message" name="message" v-model="reservation.message"></textarea>
        </p>

        <input id="auto" name="auto" type="text" v-model="reservation.auto" class="auto_me">

        <button class="fit primary">Buchung abschicken!</button>

      </form>
    </div>
  </section>
  <?= $sanity_text($page["body"]) ?>


</div>