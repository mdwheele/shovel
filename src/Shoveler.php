<?php

namespace Shovel;

use Doctrine\DBAL\Schema\Schema;
use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Collection;
use Shovel\Messages\ShovelsPickedUp;
use Shovel\Messages\PreDiggingStatusReport;
use Shovel\Messages\DigProgress;

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
        $dest = $this->destination->getConnection()->getSchemaBuilder();

        foreach ($this->instructions->getTables() as $table) {
            if (! $dest->hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function breakGround()
    {
        $src = $this->source->getConnection();
        $dest = $this->destination->getConnection();

        $tables = [];

        foreach ($this->instructions->getTables() as $table) {
            if (! $src->getSchemaBuilder()->hasTable($table)) {
                throw ShovelException::sourceTableDoesntExist($table);
            }

            if (! $dest->getSchemaBuilder()->hasTable($table)) {
                $tables[] = $src->getDoctrineSchemaManager()->listTableDetails($table);
            }
        }

        // TODO chooo choooooooo
        foreach ((new Schema($tables))->toSql($dest->getDoctrineConnection()->getDatabasePlatform()) as $statement) {
            $dest->unprepared($statement);
        }
    }

    public function dig()
    {
        $src = $this->source->getConnection();
        $dest = $this->destination->getConnection();

        $this->announce(new PreDiggingStatusReport($this->getPileSize($src), $this->getPileSize($dest)));

        foreach ($this->instructions->getTables() as $table) {
            $dest->table($table)->truncate();

            $src->table($table)->orderBy($src->raw(1))->chunk(100, function (Collection $rows) use ($dest, $table) {
                $rows
                    ->map(function ($row) { return (array) $row; })
                    ->each(function ($row) use ($dest, $table) {
                        $dest->table($table)->insert($row);
                        $this->announce(new DigProgress($row));
                    });
            });
        }
    }

    private function announce(Message $message) {
        foreach ($this->bystanders as $bystander) {
            $bystander->tell($message);
        }
    }

    private function getPileSize(Connection $conn)
    {
        $tables = collect($this->instructions->getTables());

        return $tables->sum(function ($table) use ($conn) {
            return $conn->table($table)->count();
        });
    }
}