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
use SignpostMarv\DaftObject\EasyDB\WriteableTreeTrait as EasyDBWriteableTreeTrait;
use SignpostMarv\DaftObject\TraitDaftNestedObjectIntTree;
use SignpostMarv\DaftObject\WriteableTreeTrait;

/**
* @template TDbObj as DaftNestedWriteableObject
*
* @template-extends TestObjectRepository<TDbObj>
*
* @template-implements DaftNestedWriteableObjectTree<TDbObj>
*/
class TestObjectWriteableRepository extends TestObjectRepository implements DaftNestedWriteableObjectTree
{
    /**
    * @use WriteableTreeTrait<TDbObj>
    */
    use WriteableTreeTrait;

    /**
    * @use EasyDBWriteableTreeTrait<TDbObj>
    */
    use EasyDBWriteableTreeTrait;

    public function GetNestedObjectTreeRootId() : int
    {
        return 0;
    }
}
