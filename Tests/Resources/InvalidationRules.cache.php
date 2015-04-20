<?php

return [
    'stdClass' => [
        0 => [
            'id' => 'some_cache_key_${relation.id}_${property}',
            'events' =>
                [
                    0 => 'PERSIST',
                    1 => 'UPDATE',
                    2 => 'DELETE',
                ],
        ],
    ],
];
