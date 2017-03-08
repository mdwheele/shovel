<?php

namespace Shovel;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use Yajra\Oci8\Connectors\OracleConnector;
use Yajra\Oci8\Oci8Connection;
use Yajra\Pdo\Oci8;

/**
 * @skipIfTravis
 */
class TalksToOracleTest extends TestCase
{
    private $connection = null;

    protected function setUp()
    {
        if (getenv('TRAVIS')) {
            $this->markTestSkipped('OCITalksToOracleTest not run on TravisCI');
        }

        $this->connection = oci_connect('system', 'oracle', 'localhost:1521/xe.oracle.docker');

        $this->query("
            BEGIN
                EXECUTE IMMEDIATE 'DROP TABLE oracle';
            EXCEPTION
                WHEN OTHERS THEN
                    IF SQLCODE != -942 THEN
                        RAISE;
                    END IF;
            END;
        ");
        $this->query('CREATE TABLE oracle (msg VARCHAR2(255))');
        $this->query("INSERT INTO oracle (msg) VALUES ('Hello, World!')");
    }

    protected function query($sql)
    {
        oci_execute($ret = oci_parse($this->connection, $sql));

        return $ret;
    }

    /** @test */
    public function using_the_oci8_extension_functions()
    {
        $stmt = $this->query('SELECT * FROM oracle');
        oci_execute($stmt);

        $row = oci_fetch_assoc($stmt);

        $this->assertEquals('Hello, World!', $row['MSG']);
    }

    /** @test */
    public function using_the_userland_pdo_oci8_wrapper()
    {
        $pdo = new Oci8('oci:dbname=//localhost:1521/xe.oracle.docker', 'system', 'oracle');

        $stmt = $pdo->query('SELECT * FROM oracle');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Hello, World!', $row['MSG']);
    }

    /** @test */
    public function using_laravel_database_abstraction_layer()
    {
        $capsule = $this->getCapsule();

        $row = $capsule->table('oracle')->first();

        $this->assertEquals('Hello, World!', $row->msg);
    }

    private function getCapsule()
    {
        $capsule = new Capsule;

        $manager = $capsule->getDatabaseManager();

        $manager->extend('oracle', function($config) {
            $connector = new OracleConnector();
            $connection = $connector->connect($config);
            $db = new Oci8Connection($connection, $config["database"], $config["prefix"]);
            // set oracle date format to match PHP's date
            $db->setDateFormat('YYYY-MM-DD HH24:MI:SS');
            return $db;
        });

        $capsule->addConnection(array(
            'driver'   => 'oracle',
            'host'     => 'localhost',
            'database' => 'xe',
            'username' => 'system',
            'password' => 'oracle',
            'prefix'   => '',
            'port'  => 1521
        ));

        $capsule->setAsGlobal();

        return $capsule;
    }

}