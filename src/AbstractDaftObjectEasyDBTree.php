<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use ParagonIE\EasyDB\EasyDB;
use PDO;
use PDOStatement;

abstract class AbstractDaftObjectEasyDBTree extends AbstractDaftObjectEasyDBRepository implements DaftNestedObjectTree
{
    public function RecallDaftNestedObjectFullTree(int $limit = null) : array
    {
        return $this->RecallDaftNestedObjectTreeFromArgs(null, null, $limit, false);
    }

    public function CountDaftNestedObjectFullTree(int $withLimit = null) : int
    {
        return $this->CountDaftNestedObjectTreeFromArgs(null, null, $withLimit, false);
    }

    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $withRoot,
        ? int $limit
    ) : array {
        $left = $root->GetIntNestedLeft();
        $right = $root->GetIntNestedRight();
        $limit = is_int($limit) ? ($root->GetIntNestedLevel() + $limit) : null;

        return $this->RecallDaftNestedObjectTreeFromArgs($left, $right, $limit, $withRoot);
    }

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $withRoot,
        ? int $limit
    ) : int {
        $left = $root->GetIntNestedLeft();
        $right = $root->GetIntNestedRight();
        $limit = is_int($limit) ? ($root->GetIntNestedLevel() + $limit) : null;

        return $this->CountDaftNestedObjectTreeFromArgs($left, $right, $limit, $withRoot);
    }

    /**
    * @param mixed $id
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $withRoot,
        ? int $limit
    ) : array {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectTreeWithObject(
                    $object,
                    $withRoot,
                    $limit
                )
                : (
                    ((array) $id === (array) $this->GetNestedObjectTreeRootId())
                        ? $this->RecallDaftNestedObjectFullTree(0)
                        : []
                );
    }

    /**
    * @param mixed $id
    */
    public function CountDaftNestedObjectTreeWithId(
        $id,
        bool $withRoot,
        ? int $limit
    ) : int {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->CountDaftNestedObjectTreeWithObject(
                    $object,
                    $withRoot,
                    $limit
                )
                : (
                    ((array) $id === (array) $this->GetNestedObjectTreeRootId())
                        ? $this->CountDaftNestedObjectFullTree($limit)
                        : 0
                );
    }

    public function RecallDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : array {
        return $this->RecallDaftNestedObjectTreeFromArgs(
            $leaf->GetIntNestedLeft(),
            $leaf->GetIntNestedRight(),
            null,
            $includeLeaf,
            false
        );
    }

    public function CountDaftNestedObjectPathToObject(
        DaftNestedObject $leaf,
        bool $includeLeaf
    ) : int {
        return $this->CountDaftNestedObjectTreeFromArgs(
            $leaf->GetIntNestedLeft(),
            $leaf->GetIntNestedRight(),
            null,
            $includeLeaf,
            false
        );
    }

    /**
    * @param mixed $id
    */
    public function RecallDaftNestedObjectPathToId($id, bool $includeLeaf) : array
    {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->RecallDaftNestedObjectPathToObject($object, $includeLeaf)
                : [];
    }

    /**
    * @param mixed $id
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->CountDaftNestedObjectPathToObject($object, $includeLeaf)
                : 0;
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        /**
        * @var EasyDB|null $db
        */
        $db = array_shift($args) ?: null;

        if (is_a(static::class, DaftNestedWriteableObjectTree::class, true)) {
            if ( ! is_a($type, DaftNestedWriteableObject::class, true)) {
                throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                    1,
                    static::class,
                    __FUNCTION__,
                    DaftNestedWriteableObject::class,
                    $type
                );
            }
        } elseif ( ! is_a($type, DaftNestedObject::class, true)) {
            throw new DaftObjectRepositoryTypeByClassMethodAndTypeException(
                1,
                static::class,
                __FUNCTION__,
                DaftNestedObject::class,
                $type
            );
        }

        return parent::DaftObjectRepositoryByType($type, $db, ...$args);
    }

    public function RememberDaftObjectData(
        DefinesOwnIdPropertiesInterface $object,
        bool $assumeDoesNotExist = false
    ) : void {
        static::ThrowIfNotType($object, DaftNestedObject::class, 1, __METHOD__);

        parent::RememberDaftObjectData($object, $assumeDoesNotExist);
    }

    final protected function SelectingQueryDaftNestedObjectTreeFromArgs(bool $recall) : string
    {
        if ($recall) {
            /**
            * @var string[] $props
            */
            $props = $this->type::DaftObjectIdProperties();

            return implode(', ', array_map(
                function (string $prop) : string {
                    return $this->db->escapeIdentifier($prop);
                },
                $props
            ));
        }

        return 'COUNT(*)';
    }

    /**
    * @param array<int, string> $filter
    */
    final protected function FilterQueryDaftNestedObjectTreeFromArgs(array $filter) : string
    {
        return (count($filter) > 0) ? (' WHERE ' . implode(' AND ', $filter)) : '';
    }

    /**
    * @return array<int, string>
    */
    final protected function LeftOpRightOpDaftNestedObjectTreeFromArgs(
        bool $withRoot,
        bool $treeNotPath
    ) : array {
        $leftOp = ($withRoot ? ' >= ' : ' > ');
        $rightOp = ($withRoot ? ' <= ' : ' < ');

        if ( ! $treeNotPath) {
            list($leftOp, $rightOp) = [$rightOp, $leftOp];
        }

        return [$leftOp, $rightOp];
    }

    protected function QueryDaftNestedObjectTreeFromArgs(
        bool $recall,
        ? int $left,
        ? int $right,
        ? int $limit,
        bool $withRoot,
        bool $treeNotPath = true
    ) : PDOStatement {
        $queryArgs = [];
        $filter = [];

        list($leftOp, $rightOp) = $this->LeftOpRightOpDaftNestedObjectTreeFromArgs(
            $withRoot,
            $treeNotPath
        );

        $escapedLeft = $this->db->escapeIdentifier('intNestedLeft');

        $maybeArgs = [
            ($escapedLeft . $leftOp . ' ?') => $left,
            ($this->db->escapeIdentifier('intNestedRight') . $rightOp . ' ?') => $right,
            ($this->db->escapeIdentifier('intNestedLevel') . ' <= ?') => $limit,
        ];

        foreach (array_filter($maybeArgs, 'is_int') as $filterStr => $queryArgVar) {
            $queryArgs[] = $queryArgVar;
            $filter[] = $filterStr;
        }

        $query =
            'SELECT ' .
            $this->SelectingQueryDaftNestedObjectTreeFromArgs($recall) .
            ' FROM ' .
            $this->db->escapeIdentifier($this->DaftObjectDatabaseTable()) .
            $this->FilterQueryDaftNestedObjectTreeFromArgs($filter) .
            ' ORDER BY ' .
            $escapedLeft;

        $sth = $this->db->prepare($query);

        $sth->execute($queryArgs);

        return $sth;
    }

    protected function RecallDaftNestedObjectTreeFromArgs(
        ? int $left,
        ? int $right,
        ? int $limit,
        bool $withRoot,
        bool $treeNotPath = true
    ) : array {
        $sth = $this->QueryDaftNestedObjectTreeFromArgs(
            true,
            $left,
            $right,
            $limit,
            $withRoot,
            $treeNotPath
        );

        return array_filter(
            array_map([$this, 'RecallDaftObject'], (array) $sth->fetchAll(PDO::FETCH_NUM)),
            function (? DaftObject $maybe) : bool {
                return $maybe instanceof DaftNestedObject;
            }
        );
    }

    protected function CountDaftNestedObjectTreeFromArgs(
        ? int $left,
        ? int $right,
        ? int $limit,
        bool $withRoot,
        bool $treeNotPath = true
    ) : int {
        $sth = $this->QueryDaftNestedObjectTreeFromArgs(
            false,
            $left,
            $right,
            $limit,
            $withRoot,
            $treeNotPath
        );

        return (int) $sth->fetchColumn();
    }
}
