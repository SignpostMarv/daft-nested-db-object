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
    public function DataProviderCoverageNonWriteableRepo() : Generator
    {
        $repo = Fixtures\TestObjectRepository::DaftObjectRepositoryByType(
            DaftNestedIntObject::class,
            Factory::create('sqlite::memory:')
        );

        yield [$repo];
    }

    public function DataProviderCoverageWriteableRepo() : Generator
    {
        $repo = Fixtures\TestObjectWriteableRepository::DaftObjectRepositoryByType(
            DaftNestedWriteableIntObject::class,
            Factory::create('sqlite::memory:')
        );

        yield [$repo];
    }
}
