<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedDbObject\Tests;

use Generator;
use ParagonIE\EasyDB\Factory;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\NestedTreeTest as Base;

class NestedTreeTest extends Base
{
    public function DataProviderArgs() : Generator
    {
        yield from [
            [
                static::treeClass(),
                static::leafClass(),
                Factory::create('sqlite::memory:'),
            ],
        ];
    }

    protected static function treeClass() : string
    {
        return Fixtures\TestObjectRepository::class;
    }
}
