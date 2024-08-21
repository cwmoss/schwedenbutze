<?php

use slowfoot\hook;

hook::add_filter('sanity.block_serializers', function ($serializers, $ds, $config) {
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
    'mainImage' => function ($item, $parent, $htmlBuilder) use ($ds, $config) {
      // print_r($item);
      //$asset = $ds->ref($item['attributes']['asset']);
      dbg("+++ mainImage", $ds, $config);

      return sprintf(
        '<div class="video">mainImage: %s</div>',
        json_encode($item['attributes']) . json_encode($ds),
        // $ds->get_path($item['attributes']['_ref'])
      );
    },
    'videoEmbed' => function ($item, $parent, $htmlBuilder) {
      // print_r($item);
      return sprintf('<div class="video">%s</div>', convertYoutube($item['attributes']['url']));
    },

  ];
});
