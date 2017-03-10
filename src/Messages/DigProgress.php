<?php

namespace Shovel\Messages;

use Shovel\Message;

class DigProgress implements Message
{
    /**
     * @var mixed
     */
    public $object;

    public function __construct($object)
    {
        $this->object = $object;
    }
}