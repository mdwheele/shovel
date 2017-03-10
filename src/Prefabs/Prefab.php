<?php

namespace Shovel\Prefabs;

use Illuminate\Database\Connection;
use Shovel\Instructions;

abstract class Prefab
{
    /**
     * @var Connection
     */
    protected $source;

    /**
     * @var Connection
     */
    protected $destination;

    /**
     * @var Instructions
     */
    protected $instructions;

    public function __construct(Connection $source, Connection $destination, Instructions $instructions)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->instructions = $instructions;
    }

}