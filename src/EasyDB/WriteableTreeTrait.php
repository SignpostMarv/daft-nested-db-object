<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\EasyDB;

use PDO;
use SignpostMarv\DaftObject\DaftNestedWriteableObject;
use SignpostMarv\DaftObject\DefinesOwnArrayIdInterface;
use SignpostMarv\DaftObject\SuitableForRepositoryType;

/**
* @template T as DaftNestedWriteableObject
*/
trait WriteableTreeTrait
{
    /**
    * @param scalar|(scalar|array|object|null)[] $id
    *
    * @psalm-param class-string<T> $type
    *
    * @psalm-return T
    */
    abstract public function RecallDaftObjectOrThrow(
        $id,
        string $type = SuitableForRepositoryType::class
    ) : SuitableForRepositoryType;

    abstract protected function DaftObjectDatabaseTable() : string;

    /**
    * @psalm-return T
    */
    protected function ObtainLastLeafInTree() : DaftNestedWriteableObject
    {
        /**
        * @var \ParagonIE\EasyDB\EasyDB
        */
        $db = $this->db;

        /**
        * @psalm-var class-string<T>
        */
        $type = $this->type;

        $sth = $db->prepare(
            'SELECT ' .
            implode(',', array_map(
                [$db, 'escapeIdentifier'],
                $type::DaftObjectIdProperties()
            )) .
            ' FROM ' .
            $this->DaftObjectDatabaseTable() .
            ' ORDER BY ' .
            $db->escapeIdentifier('intNestedLeft') .
            ' DESC LIMIT 1'
        );

        $sth->execute();

        /**
        * @var array<string, scalar>
        */
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (1 === count($res) && ! is_a($type, DefinesOwnArrayIdInterface::class, true)) {
            $res = current($res);
        }

        /**
        * @var DaftNestedWriteableObject
        *
        * @psalm-var T
        */
        $out = $this->RecallDaftObjectOrThrow($res);

        return $out;
    }
}
