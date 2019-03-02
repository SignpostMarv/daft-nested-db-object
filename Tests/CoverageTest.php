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
use SignpostMarv\DaftObject\DaftNestedObject\Tests\CoverageTest as Base;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;

class CoverageTest extends Base
{
    /**
    * @return Generator<int, Fixtures\TestObjectRepository, mixed, void>
    */
    public function DataProviderCoverageNonWriteableRepo() : Generator
    {
        /**
        * @var Fixtures\TestObjectRepository
        */
        $repo = Fixtures\TestObjectRepository::DaftObjectRepositoryByType(
            DaftNestedIntObject::class,
            Factory::create('sqlite::memory:')
        );

        yield [$repo];
    }

    /**
    * @return Generator<int, Fixtures\TestObjectWriteableRepository, mixed, void>
    */
    public function DataProviderCoverageWriteableRepo() : Generator
    {
        /**
        * @var Fixtures\TestObjectWriteableRepository
        */
        $repo = Fixtures\TestObjectWriteableRepository::DaftObjectRepositoryByType(
            DaftNestedWriteableIntObject::class,
            Factory::create('sqlite::memory:')
        );

        yield [$repo];
    }
}
