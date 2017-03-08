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

    /**
     * @return bool
     */
    public function hasBrokenGround()
    {
        $dest = $this->destination->getConnection();

        foreach ($this->instructions->getTables() as $table) {
            if (! $dest->table($table)->exists()) {
                return false;
            }
        }

        return true;
    }

    public function breakGround()
    {
        $src = $this->source->getConnection();
        $dest = $this->destination->getConnection();

        foreach ($this->instructions->getTables() as $table) {
            if (! $src->getSchemaBuilder()->hasTable($table)) {
                throw ShovelException::sourceTableDoesntExist($table);
            }
        }
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