<?php

namespace QafooLabs\DemoShop\Model;

/**
 * Product Model of the ExampleShop.
 *
 * This model matches the Bepado model almost exactly. This is done
 * for simplicity. There is still a {@link ProductConverter} to show
 * the basic workflow that is probably necessary in your own plugin.
 *
 * What is missing in this model is the Bepado "shopId" and "sourceId"
 * fields. Depending on your shop system you probably need to save
 * those variables in another database table.
 */
class ShopProduct extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $ean;

    /**
     * @var string
     */
    public $shortDescription;

    /**
     * @var string
     */
    public $longDescription;

    /**
     * @var string
     */
    public $vendor;

    /**
     * @var float
     */
    public $vat = 0.19;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $purchasePrice;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var \DateTime|null
     */
    public $deliveryDate;

    /**
     * @var int
     */
    public $availability;

    /**
     * @var string[]
     */
    public $images = array();

    /**
     * @var string
     */
    public $category;

    /**
     * @var array<string,string>
     */
    public $attributes = array();

    /**
     * @var int
     */
    public $deliveryWorkDays;

    public function formattedGrossPrice()
    {
        return round($this->price * (1 + $this->vat), 2) . " " . $this->currency;
    }
}
