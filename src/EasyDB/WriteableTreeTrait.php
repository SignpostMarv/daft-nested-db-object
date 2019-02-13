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
    abstract protected function DaftObjectDatabaseTable() : string;

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

        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (1 === count($res) && ! is_a($type, DefinesOwnArrayIdInterface::class, true)) {
            $res = current($res);
        }

        return $this->RecallDaftObjectOrThrow($res);
    }
}
