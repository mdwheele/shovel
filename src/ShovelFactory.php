<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use Yajra\Oci8\Connectors\OracleConnector;
use Yajra\Oci8\Oci8Connection;

class ShovelFactory
{
    public function makeShovelFor($config)
    {
        switch ($config['driver']) {
            case 'oracle':
                return $this->makeOracleShovel($config);
                break;

            case 'mysql':
                return $this->makeMySQLShovel($config);
                break;

            default:
                throw ShovelException::unsupportedDriver($config['driver']);
                break;
        }
    }

    public function makeOracleShovel(array $config)
    {
        $capsule = new Capsule;

        $manager = $capsule->getDatabaseManager();

        $manager->extend('oracle', function($config) {
            $connector = new OracleConnector();
            $connection = $connector->connect($config);

            $db = new Oci8Connection($connection, $config["database"], $config["prefix"], $config);
            $db->setDateFormat('YYYY-MM-DD HH24:MI:SS');
            return $db;
        });

        $capsule->addConnection([
            'driver'   => 'oracle',
            'host'     => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'prefix'   => '',
            'port'  => $config['port']
        ]);

        return $capsule;
    }

    public function makeMySQLShovel($config)
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'   => 'mysql',
            'host'     => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'prefix'   => '',
            'port'  => $config['port']
        ]);

        return $capsule;
    }

}