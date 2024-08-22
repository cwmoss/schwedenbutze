<?php

use slowfoot\hook;
use slowfoot\image\processor;

hook::add_filter('sanity.block_serializers', function ($serializers, $opts, $ds, $config) {
  return [
    'marks' => [
      'link' => [
        'head' => function ($mark) use ($ds) {
          return '<a href="' . sanity_link_url($mark, $ds) . '">';
        },
        'tail' => '</a>'
      ],
      'authorLink' => [
        'head' => function ($mark) use ($ds) {
          return '<a href="' . sanity_link_url($mark, $ds) . '">';
        },
        'tail' => '</a>'
      ]
    ],
    'reference' => function ($item, $parent, $htmlBuilder) use ($ds) {
      // print_r($item);
      return sprintf(
        '<div class="video">link %s %s</div>',
        $item['attributes']['_ref'],
        $ds->get_path($item['attributes']['_ref'])
      );
    },
    /*
    <figure><
    img src="https://cdn.sanity.io/images/emjk7lsc/production/7e40b3312512161aa23bc3d5bcd1055036a076be-1152x1536.jpg?auto=format&amp;dpr=2"
    alt="Ofen bitte ausmachen vor der Abfahrt">
    <figcaption>Bitte den Ofen nicht brennen lassen.</figcaption>
    </figure>
    */
    'mainImage' => function ($item, $parent, $htmlBuilder) use ($ds, $config) {
      // print_r($item);
      $asset = $ds->ref($item['attributes']['asset']);
      // alt + caption
      //dbg("+++ mainImage", $item['attributes'], $asset);
      // TODO: $config->get_image_processor()
      $processor = new processor($config->assets);
      $tag = sprintf(
        "<figure>%s<figcaption>%s</figcaption></figure>",
        $processor->image_tag($asset, "600x", ["alt" => $item['attributes']['alt'] ?? ""]),
        $item['attributes']['caption'] ?? ""
      );
      return $tag;
    },
    'videoEmbed' => function ($item, $parent, $htmlBuilder) {
      // print_r($item);
      return sprintf('<div class="video">%s</div>', convertYoutube($item['attributes']['url']));
    },

  ];
});
