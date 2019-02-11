<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedDbObject\Tests\Fixtures;

use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DaftNestedWriteableObjectTree;
use SignpostMarv\DaftObject\DaftObjectRepositoryTypeByClassMethodAndTypeException;
use SignpostMarv\DaftObject\TraitDaftNestedObjectIntTree;
use SignpostMarv\DaftObject\WriteableTreeTrait;

/**
* @template TDbObj as DaftNestedWriteableObject
*
* @template-extends TestObjectRepository<TDbObj>
*
* @template-implements DaftNestedWriteableObjectTree<TDbObj>
*/
class TestObjectWriteableRepository
    extends
        TestObjectRepository
    implements
        DaftNestedWriteableObjectTree
{
    /**
    * @use WriteableTreeTrait<TDbObj>
    */
    use WriteableTreeTrait;

    public function GetNestedObjectTreeRootId() : int
    {
        return 0;
    }

    /**
    * @param DaftObject|string $object
    */
    protected static function ThrowIfNotType(
        $object,
        string $type,
        int $argument,
        string $function
    ) : void {
        if ( ! is_a($object, DaftNestedWriteableObject::class, is_string($object))) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                $argument,
                static::class,
                $function,
                DaftNestedWriteableObject::class,
                is_string($object) ? $object : get_class($object)
            );
        }

        parent::ThrowIfNotType($object, $type, $argument, $function);
    }
}
