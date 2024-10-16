<?php

namespace Skeleton\Store\DataStructure;

use Illuminate\Database\Eloquent\Model;

class ProductDetail
{
    public $name;
    public $amount;
    public $model;
    public $media_url = [];
    public $quantity;

    /**
     * ProductDetail constructor.
     *
     * @param string $name
     * @param float $amount
     * @param Model $model
     * @param array $media_url
     * @param int $quantity
     */
    public function __construct(string $name, float $amount, Model $model, int $quantity, array $media_url = [])
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->model = $model;
        $this->quantity = $quantity;
        $this->media_url = $media_url;
    }

    /**
     * Get total cost based on quantity.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->amount * $this->quantity;
    }

    /**
     * Optionally, add other methods for more functionality.
     */
}
