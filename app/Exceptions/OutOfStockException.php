<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order asks for more units than are in stock.
 */
class OutOfStockException extends RuntimeException
{
    public string $productName;
    public int $available;

    public function __construct(string $productName, int $available)
    {
        $this->productName = $productName;
        $this->available = max(0, $available);

        parent::__construct($this->available === 0
            ? ucfirst($productName) . ' is sold out. Please remove it from your bag.'
            : 'Only ' . $this->available . ' left of ' . ucfirst($productName) . '. Please update your bag.');
    }
}
