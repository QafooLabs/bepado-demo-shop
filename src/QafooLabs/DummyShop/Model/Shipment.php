<?php

namespace QafooLabs\DummyShop\Model;

class Shipment extends Model
{
    public $items = array();
    public $isShippable = false;
    public $grossShippingCosts = 0;
    public $netShippingCosts = 0;
    public $netPrice;
    public $grossPrice;
}
