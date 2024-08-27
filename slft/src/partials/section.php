<?php
$doc = $ref($section['ref']);
$title = $section['title'] ?: ($doc['title'] ?: $doc['name']);
// $img = $ref($doc['mainImage']['asset']);
$img = $doc['mainImage'];
#var_dump($img);
?>
<article>
  <? if (false && $img) { ?>
    <?= $image_tag($img, 'post', ['alt' => $doc['mainImage']['caption']]) ?>
  <? } ?>


  <h2><?= $title ?></h2>

  <div>
    <? if ($doc['_type'] == 'tour') { ?>
      <p><?= $doc['title'] ?></p>
      <table class="termine">
        <tbody>
          <? foreach ($doc['events'] as $termin) { ?>
            <tr>
              <td><?= date('d.m.Y', strtotime($termin['start'])) ?></td>
              <td><strong><?= $termin['city'] ?></strong></td>
              <td><?= $termin['location'] ?></td>
            </tr>
          <? } ?>
        </tbody>
      </table>
    <? } ?>
    <? if ($doc['_type'] == 'author') { ?>
      <?= $partial('block', ['body' => $doc['bio']]) ?>
    <? } ?>
    <? if ($doc['_type'] == 'post') { ?>
      <?= $partial('block', ['body' => $doc['body']]) ?>
    <? } ?>

</article>