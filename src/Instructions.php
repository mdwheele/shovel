<?php

namespace Shovel;

class Instructions
{
    private $config;

    public function __construct(array $config = [])
    {
        if (! array_key_exists('src', $config)) {
            throw ShovelException::sourceRequired();
        }

        if (! array_key_exists('src', $config)) {
            throw ShovelException::destinationRequired();
        }

        $this->config = $config;
    }

    public function getSource()
    {
        return $this->config['src'];
    }

    public function getDestination()
    {
        return $this->config['dest'];
    }
}