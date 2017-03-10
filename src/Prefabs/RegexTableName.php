<?php

namespace Shovel\Prefabs;

class RegexTableName extends Prefab
{
    public function __invoke($match, $replace)
    {
        foreach ($this->instructions->getTables() as $table) {
            $transformedTable = preg_replace($match, $replace, $table);

            $this->destination->getSchemaBuilder()->drop($transformedTable);
            $this->destination->getSchemaBuilder()->rename($table, $transformedTable);
        }
    }
}
