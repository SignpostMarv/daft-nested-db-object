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
    const BOOL_RETRIEVE_WITH_ROOT = false;

    const INT_LIMIT_ZERO = 0;

    const DEFAULT_COUNT_IF_NOT_OBJECT = 0;

    const INT_ARG_INDEX_FIRST = 1;

    const DEFAULT_BOOL_FETCH_TREE_NOT_PATH = true;

    public function RecallDaftNestedObjectFullTree(int $limit = null) : array
    {
        return $this->RecallDaftNestedObjectTreeFromArgs(
            null,
            null,
            $limit,
            self::BOOL_RETRIEVE_WITH_ROOT
        );
    }

    public function CountDaftNestedObjectFullTree(int $withLimit = null) : int
    {
        return $this->CountDaftNestedObjectTreeFromArgs(
            null,
            null,
            $withLimit,
            self::BOOL_RETRIEVE_WITH_ROOT
        );
    }

    public function RecallDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $withRoot,
        int $limit = null
    ) : array {
        $left = $root->GetIntNestedLeft();
        $right = $root->GetIntNestedRight();
        $limit = is_int($limit) ? ($root->GetIntNestedLevel() + $limit) : null;

        return $this->RecallDaftNestedObjectTreeFromArgs($left, $right, $limit, $withRoot);
    }

    public function CountDaftNestedObjectTreeWithObject(
        DaftNestedObject $root,
        bool $withRoot,
        int $limit = null
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
        int $limit = null
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
                        ? $this->RecallDaftNestedObjectFullTree(self::INT_LIMIT_ZERO)
                        : []
                );
    }

    /**
    * @param mixed $id
    */
    public function CountDaftNestedObjectTreeWithId(
        $id,
        bool $withRoot,
        int $limit = null
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
                        : self::DEFAULT_COUNT_IF_NOT_OBJECT
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
                : self::DEFAULT_COUNT_IF_NOT_OBJECT;
    }

    /**
    * {@inheritdoc}
    */
    public static function DaftObjectRepositoryByType(
        string $type,
        ...$args
    ) : DaftObjectRepository {
        /**
        * @var EasyDB|null
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
    ) {
        NestedTypeParanoia::ThrowIfNotNestedType(
            $object,
            self::INT_ARG_INDEX_FIRST,
            static::class,
            __FUNCTION__
        );

        parent::RememberDaftObjectData($object, $assumeDoesNotExist);
    }

    final protected function SelectingQueryDaftNestedObjectTreeFromArgs(bool $recall) : string
    {
        if ($recall) {
            /**
            * @var string[]
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
        int $left = null,
        int $right = null,
        int $limit = null,
        bool $withRoot = true,
        bool $treeNotPath = self::DEFAULT_BOOL_FETCH_TREE_NOT_PATH
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
        int $left = null,
        int $right = null,
        int $limit = null,
        bool $withRoot = true,
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
            function (DaftObject $maybe = null) : bool {
                return $maybe instanceof DaftNestedObject;
            }
        );
    }

    protected function CountDaftNestedObjectTreeFromArgs(
        int $left = null,
        int $right = null,
        int $limit = null,
        bool $withRoot = true,
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
