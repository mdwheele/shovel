<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\MySqlConnection;
use Mockery;
use Yajra\Oci8\Oci8Connection;

class ShovelerTest extends TestCase
{
    /** @test */
    public function shoveler_is_given_instructions_and_grab_shovels_from_the_factory()
    {
        $shoveler = new Shoveler($this->getShovelFactory());

        $shoveler->dig($this->getInstructions('testing'));

        $this->assertInstanceOf(Capsule::class, $shoveler->source);
        $this->assertInstanceOf(Oci8Connection::class, $shoveler->source->getConnection());

        $this->assertInstanceOf(Capsule::class, $shoveler->destination);
        $this->assertInstanceOf(MySqlConnection::class, $shoveler->destination->getConnection());
    }

    /** @test */
    public function can_tell_things_to_bystanders()
    {
        $shoveler = new Shoveler($this->getShovelFactory());
        $bystander = Mockery::spy(Bystander::class);
        $shoveler->addBystander($bystander);

        $shoveler->dig($this->getInstructions('testing'));

        $bystander->shouldHaveReceived('tell');
    }

    private function getShovelFactory()
    {
        return new ShovelFactory();
    }
}