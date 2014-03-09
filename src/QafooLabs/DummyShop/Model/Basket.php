<?php

namespace QafooLabs\DummyShop\Model;

class Basket extends Model
{
    public $count = 0;
    public $shipments = array();
    public $isShippable = false;
    public $totalGrossShippingCosts = 0;
    public $totalNetShippingCosts = 0;
    public $totalGrossPrice = 0;
    public $totalNetPrice = 0;
}
