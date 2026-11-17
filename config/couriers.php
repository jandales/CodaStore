<?php

/*
| Couriers the admin can choose when shipping an order.
| "url" is optional: put the courier's public tracking page with {number} where the
| tracking number goes, e.g. 'https://example-courier.test/track?no={number}'.
| Leave it null to show only the courier name and tracking number.
*/

return [
    'lbc' => ['name' => 'LBC Express', 'url' => null],
    'jnt' => ['name' => 'J&T Express', 'url' => null],
    'ninjavan' => ['name' => 'Ninja Van', 'url' => null],
    'flash' => ['name' => 'Flash Express', 'url' => null],
    '2go' => ['name' => '2GO Express', 'url' => null],
    'grab' => ['name' => 'Grab Express', 'url' => null],
    'lalamove' => ['name' => 'Lalamove', 'url' => null],
    'other' => ['name' => 'Other courier', 'url' => null],
];
