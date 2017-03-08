<?php

namespace Shovel;

class TestCase extends \PHPUnit\Framework\TestCase
{
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

        if (getenv('TRAVIS') === true) {
            $this->markTestSkipped('This test does not run on Travis.');
        }
    }

    private function handleLocalSkips($location)
    {
        if (! array_key_exists('skipIfLocal', $location)) {
            return;
        }

        if (getenv('LOCAL') === true) {
            $this->markTestSkipped('This test does not run on local environments.');
        }
    }
}