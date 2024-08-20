<?php

use slowfoot\configuration;
use slowfoot\image\profile;
use slowfoot\image\configuration as img_config;
use slowfoot\loader\json;
use slowfoot\plugins\sanity;

return new configuration(
  site_name: "Zorros Südhof",
  site_description: 'Välkommen till Zorros Südhof',
  templates: [
    "post" => '/:slug.current',
    // "page" => '/:slug.current'
  ],
  plugins: [
    new sanity('emjk7lsc', use_cdn: true)
  ],
  sources: [
    "sanity" => sanity::data_loader(...)
  ],
  assets: new img_config(
    download: true,
    profiles: [
      "main" => new profile(
        size: "600x400",
        mode: "fit"
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
