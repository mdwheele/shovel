<?php

namespace Shovel\Messages;

use Shovel\Message;

class PreDiggingStatusReport implements Message
{
    /**
     * @var int
     */
    public $sourcePileSize;

    /**
     * @var int
     */
    public $destPileSize;

    public function __construct($sourcePileSize, $destPileSize)
    {
        $this->sourcePileSize = $sourcePileSize;
        $this->destPileSize = $destPileSize;
    }
}