<?php


class OCITalksToOracleTest extends \PHPUnit\Framework\TestCase
{
    private $connection = null;

    protected function setUp()
    {
        $this->connection = oci_connect('system', 'oracle', 'localhost:1521/xe.oracle.docker');

        $this->query("
            BEGIN
                EXECUTE IMMEDIATE 'DROP TABLE phpunit';
            EXCEPTION
                WHEN OTHERS THEN
                    IF SQLCODE != -942 THEN
                        RAISE;
                    END IF;
            END;
        ");
        $this->query('CREATE TABLE phpunit (msg VARCHAR2(255))');
        $this->query("INSERT INTO phpunit (msg) VALUES ('Hello, World!')");
    }

    protected function query($sql)
    {
        oci_execute($ret = oci_parse($this->connection, $sql));

        return $ret;
    }

    /** @test */
    public function it_can_query_the_database()
    {
        $stmt = $this->query('SELECT * FROM phpunit');
        oci_execute($stmt);

        $row = oci_fetch_assoc($stmt);

        $this->assertEquals('Hello, World!', $row['MSG']);
    }
}