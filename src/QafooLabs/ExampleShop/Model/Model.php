<?php

namespace QafooLabs\ExampleShop\Model;

abstract class Model
{
    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}
