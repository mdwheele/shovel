<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Yajra\Oci8\Oci8Connection;

/** @skipOnTravis */
class ShovelerTest extends TestCase
{
    public function setUp()
    {
        $factory = new ShovelFactory();
        $instructions = $this->getInstructions('testing');
        $source = $factory->makeShovelFor($instructions->getSource());
        $schema = $source->getConnection()->getSchemaBuilder();

        foreach ($instructions->getTables() as $table) {
            $schema->dropIfExists($table);
            $schema->create($table, function (Blueprint $table) {
                $table->string('MSG');
            });

            $source->getConnection()->table($table)->insert([
                ['MSG' => 'Oh hai!'],
                ['MSG' => 'Oh hai again!'],
                ['MSG' => 'It is nice to see you.'],
                ['MSG' => 'Bye bye!'],
            ]);
        }
    }

    /** @test */
    public function shoveler_is_given_instructions_and_grab_shovels_from_the_factory()
    {
        $shoveler = $this->getShoveler('testing');

        $shoveler->pickUpShovels();

        // Perhaps overly technical, but we need to know that the
        // correct connections are being created.
        $this->assertInstanceOf(Capsule::class, $shoveler->source);
        $this->assertInstanceOf(Capsule::class, $shoveler->destination);
        $this->assertInstanceOf(Oci8Connection::class, $shoveler->source->getConnection());
        $this->assertInstanceOf(MySqlConnection::class, $shoveler->destination->getConnection());
    }

    /** @test */
    public function can_tell_things_to_bystanders()
    {
        $shoveler = $this->getShoveler('testing');

        $bystander = Mockery::spy(Bystander::class);
        $shoveler->addBystander($bystander);

        $shoveler->pickUpShovels();

        $bystander->shouldHaveReceived('tell');
    }

    /** @test */
    public function can_break_ground_to_create_tables_in_destination()
    {
        $shoveler = $this->getShoveler('testing');

        $shoveler->pickUpShovels();
        $shoveler->breakGround();

        $dest = $shoveler->destination->getConnection();

        $this->assertTrue($dest->getSchemaBuilder()->hasTable('PS_NC_HELLO_WORLD'));
    }

    private function getShoveler($fixture)
    {
        return new Shoveler($this->getInstructions($fixture), new ShovelFactory());
    }
}