<?php

namespace Shovel;

use Symfony\Component\Yaml\Yaml;

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @param $fixture
     *
     * @return Instructions
     */
    protected function getInstructions($fixture)
    {
        return new Instructions($this->getFixtureArray($fixture));
    }

    protected function getFixtureArray($fixture)
    {
        $contents = file_get_contents(__DIR__ . '/fixtures/' . $fixture . '.yml');

        return Yaml::parse($contents);
    }

    protected function checkRequirements()
    {
        parent::checkRequirements();

        collect($this->getAnnotations())->each(function ($location) {
            $this->handleTravisSkips($location);
            $this->handleLocalSkips($location);
        });
    }

    private function handleTravisSkips($location)
    {
        if (! array_key_exists('skipIfTravis', $location)) {
            return;
        }

        if (getenv('TRAVIS')) {
            $this->markTestSkipped('This test does not run on Travis.');
        }
    }

    private function handleLocalSkips($location)
    {
        if (! array_key_exists('skipIfLocal', $location)) {
            return;
        }

        if (getenv('LOCAL')) {
            $this->markTestSkipped('This test does not run on local environments.');
        }
    }
}