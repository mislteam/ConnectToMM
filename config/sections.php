<?php

return [
    'simple_transparent_secure' => [
        'hasImg' => true,
        'page' => 'home',
        'type' => 'hero_with_button',
        'hasDesc' => true,
        'items' => [
            ['type' => 'button', 'count' => 1],
        ]
    ],
    'what_we_do' => [
        'hasImg' => false,
        'page' => 'home',
        'type' => 'mixed_cards',
        'hasDesc' => false,
        'items' => [
            ['type' => 'mixed_card', 'count' => 3],
            ['type' => 'mini_card', 'count' => 2],
        ]
    ],
    'about_connect_to_myanmar' => [
        'hasImg' => true,
        'page' => 'all',
        'type' => 'stat',
        'hasDesc' => true,
        'items' => [
            ['type' => 'list_card', 'count' => 2],
        ]
    ],
    'our_services' => [
        'hasImg' => true,
        'page' => 'home',
        'type' => 'service',
        'hasDesc' => false,
        'items' => [
            ['type' => 'sim_card', 'count' => 2],
            ['type' => 'sim_image', 'count' => 1],
        ]
    ],
    'manage_section' => [
        'hasImg' => true,
        'page' => 'home',
        'type' => 'manage_cards',
        'hasDesc' => false,
        'items' => [
            ['type' => 'manage_card', 'count' => 3],
        ]
    ],
    'need_more_help' => [
        'hasImg' => false,
        'page' => 'all',
        'type' => 'need_help',
        'hasDesc' => true,
        'items' => [
            ['type' => 'mix_card_with_btn', 'count' => 3],
        ]
    ],
    'about_company' => [
        'hasImg' => false,
        'page' => 'aboutus',
        'type' => 'mixed_cards',
        'hasDesc' => false,
        'items' => [
            ['type' => 'mixed_card', 'count' => 3],
        ]
    ],
    'how_we_work' => [
        'hasImg' => true,
        'page' => 'aboutus',
        'type' => 'work_lists',
        'hasDesc' => true,
        'items' => [
            ['type' => 'texts_list', 'count' => 4],
            ['type' => 'btns_list', 'count' => 2],
        ]
    ],
    'frequently_asked_questions' => [
        'hasImg' => false,
        'page' => 'aboutus',
        'type' => 'mini_section',
        'hasDesc' => true,
        'items' => [
            ['type' => 'title_desc', 'count' => 1],
            ['type' => 'connection_card', 'count' => 2],
        ]
    ],
];
