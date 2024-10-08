<?php

use slowfoot\configuration;
use slowfoot\image\profile;
use slowfoot\image\configuration as img_config;
use slowfoot\loader\json;
use slowfoot_plugin\sanity;

require_once("sanity_block_serializer.php");

return new configuration(
  site_name: "Schwedenbutze",
  site_description: 'Välkommen till Schwedenbutze',
  templates: [
    "post" => '/:slug.current',
    "page" => '/:slug.current'
  ],
  plugins: [
    new sanity\sanity('emjk7lsc', use_cdn: true)
  ],
  sources: [
    "sanity" => sanity\sanity::data_loader(...)
  ],
  assets: new img_config(
    download: true,
    profiles: [
      "main" => new profile(
        size: "1200x600",
        mode: "fill"
      )
    ]
  )
  /*
    "main" => new profile(
      dir: 'images',
        'path' => '/images',
        'download' => true,
        'profiles' => [
            'small' => [
                's' => '600x400',
                'mode' => 'fit'
            ],
            'post' => [
                'size' => '500x',

            ]
        ],)
  ]*/
);
