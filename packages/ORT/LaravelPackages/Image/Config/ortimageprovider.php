<?php

return [
    'public' => [
        'path' => [
            'images' => public_path('uploads/images'),
            'thumbnails' => public_path('uploads/thumbnails'),
        ],
    ],
    'thumbnail' => [
        'sizes' => [
            'large' => ['width' => 1920, 'height' => 1920],
            'medium' => ['width' => 960, 'height' => 960],
            'small' => ['width' => 480, 'height' => 480]
        ]
    ]
];
