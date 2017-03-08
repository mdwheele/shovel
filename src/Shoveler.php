<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use Shovel\Messages\ShovelsPickedUp;

class Shoveler
{
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

    public function __construct(ShovelFactory $factory)
    {
        $this->factory = $factory;
    }

    public function dig(Instructions $instructions)
    {
        $this->pickUpShovels($instructions);
    }

    private function announce(Message $message) {
        foreach ($this->bystanders as $bystander) {
            $bystander->tell($message);
        }
    }

    private function pickUpShovels($instructions)
    {
        $this->source = $this->factory->makeShovelFor($instructions->getSource());
        $this->destination = $this->factory->makeShovelFor($instructions->getDestination());

        $this->announce(new ShovelsPickedUp());
    }

    public function addBystander(Bystander $bystander)
    {
        $this->bystanders[] = $bystander;
    }
}