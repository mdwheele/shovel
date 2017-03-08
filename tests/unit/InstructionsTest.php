<?php

namespace Shovel;

class InstructionsTest extends TestCase
{

    /** @test */
    public function has_a_source_and_a_destination()
    {
        $instructions = $this->getInstructions('testing');

        $this->assertArraySubset(['driver' => 'oracle'], $instructions->getSource());
        $this->assertArraySubset(['driver' => 'mysql'], $instructions->getDestination());
    }

    /** @test */
    public function gets_pissed_as_hell_if_you_dont_have_a_source_and_destination()
    {
        $instructions = null;

        try {
            $instructions = $this->getInstructions('invalid.no-source-dest');
        } catch (ShovelException $e) {
            $this->assertNull($instructions);
            return;
        }

        $this->fail('Was able to get instructions even though source and destination were not specified.');
    }

    /** @test */
    public function tables_to_shovel_can_be_specified()
    {
        $instructions = $this->getInstructions('testing');

        $this->assertArraySubset([
            'PS_NC_HELLO_WORLD',
            'PS_NC_HELLO_CONTINENT',
            'PS_NC_HELLO_COUNTRY',
        ], $instructions->getTables());
    }
}