<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedDbObject\Tests;

use BadMethodCallException;
use Generator;
use InvalidArgumentException;
use ParagonIE\EasyDB\Factory;
use RuntimeException;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\CoverageTest as Base;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\TraitWriteableTree;

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
