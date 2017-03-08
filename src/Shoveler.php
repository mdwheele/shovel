<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use Shovel\Messages\ShovelsPickedUp;

class Shoveler
{

    /**
     * @var Instructions
     */
    private $instructions;

    /**
     * @var ShovelFactory
     */
    private $factory;

    /**
     * @var Capsule
     */
    public $source;

    /**
     * @var Capsule
     */
    public $destination;

    /**
     * @var Bystander[]
     */
    public $bystanders = [];

    public function __construct(Instructions $instructions, ShovelFactory $factory)
    {
        $this->factory = $factory;
        $this->instructions = $instructions;
    }

    public function addBystander(Bystander $bystander)
    {
        $this->bystanders[] = $bystander;
    }

    public function pickUpShovels()
    {
        $this->source = $this->factory->makeShovelFor($this->instructions->getSource());
        $this->destination = $this->factory->makeShovelFor($this->instructions->getDestination());

        $this->announce(new ShovelsPickedUp());
    }

    public function dig()
    {

    }

    private function announce(Message $message) {
        foreach ($this->bystanders as $bystander) {
            $bystander->tell($message);
        }
    }
}