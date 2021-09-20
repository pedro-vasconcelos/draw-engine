<?php

return [
    'models' => [
        'draw' => PedroVasconcelos\DrawEngine\Models\Draw::class,
        'game' => PedroVasconcelos\DrawEngine\Models\Game::class,
        'prize' => App\Models\Prize::class,
    ],
    'game_identifier_field' => 'identifier',
    'region_field' => 'region',
];
