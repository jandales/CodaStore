<?php

return [
    // Customers can ask to return a delivered order for this many days after delivery.
    'return_days' => (int) env('STORE_RETURN_DAYS', 30),
];
