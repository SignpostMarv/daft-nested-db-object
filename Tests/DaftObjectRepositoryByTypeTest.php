<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedDbObject\Tests;

use ParagonIE\EasyDB\EasyDB;
use SignpostMarv\DaftObject\AbstractDaftObjectEasyDBRepository;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DaftNestedObjectTree;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\DaftObjectCreatedByArray;
use SignpostMarv\DaftObject\DaftObjectNullStub;
use SignpostMarv\DaftObject\DaftObjectNullStubCreatedByArray;
use SignpostMarv\DaftObject\DatabaseConnectionNotSpecifiedException;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\EasyDB\TestObjectRepository;
use SignpostMarv\DaftObject\ReadWrite;
use SignpostMarv\DaftObject\EasyDB\Tests\DaftObjectRepositoryByTypeTest as Base;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedIntObject;
use SignpostMarv\DaftObject\DaftNestedObject\Tests\Fixtures\DaftNestedWriteableIntObject;

class DaftObjectRepositoryByTypeTest extends Base
{
    public function RepositoryTypeDataProvider() : array
    {
        return [
            [
                Fixtures\TestObjectRepository::class,
                DaftObjectNullStub::class,
                DaftNestedIntObject::class,
            ],
            [
                Fixtures\TestObjectRepository::class,
                DaftObjectNullStub::class,
                DaftNestedWriteableIntObject::class,
            ],
            [
                Fixtures\TestObjectRepository::class,
                '-foo',
                DaftNestedIntObject::class,
            ],
            [
                Fixtures\TestObjectRepository::class,
                '-foo',
                DaftNestedWriteableIntObject::class,
            ],
            [
                Fixtures\TestObjectWriteableRepository::class,
                DaftObjectNullStub::class,
                DaftNestedIntObject::class,
            ],
            [
                Fixtures\TestObjectWriteableRepository::class,
                DaftObjectNullStub::class,
                DaftNestedWriteableIntObject::class,
            ],
            [
                Fixtures\TestObjectWriteableRepository::class,
                '-foo',
                DaftNestedIntObject::class,
            ],
            [
                Fixtures\TestObjectWriteableRepository::class,
                '-foo',
                DaftNestedWriteableIntObject::class,
            ],
        ];
    }

    public function dataProviderDatabaseConnectionNotSpecifiedException(
    ) : array {
        return [
            [
                Fixtures\TestObjectWriteableRepository::class,
                EasyDB::class,
                DaftNestedWriteableIntObject::class,
            ],
        ];
    }

    /**
    * @dataProvider RepositoryTypeDataProvider
    */
    public function testForCreatedByArray(
        string $repoImplementation,
        string $typeImplementation,
        string $typeExpected,
        ...$additionalArgs
    ) {
        if (
            (
                is_a($repoImplementation, DaftNestedWriteableObjectTree::class, true) &&
                ! is_a($typeImplementation, DaftNestedWriteableObject::class, true)
            ) ||
            (
                is_a($repoImplementation, DaftNestedObjectTree::class, true) &&
                ! is_a($typeImplementation, DaftNestedObject::class, true)
            )
        ) {
            $typeExpected =
                is_a($repoImplementation, DaftNestedWriteableObjectTree::class, true)
                    ? DaftNestedWriteableObject::class
                    : DaftNestedObject::class;
        }

        parent::testForCreatedByArray(
            $repoImplementation,
            $typeImplementation,
            $typeExpected,
            ...$additionalArgs
        );
    }
}
