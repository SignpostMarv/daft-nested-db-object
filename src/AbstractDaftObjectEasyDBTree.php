<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use PDO;
use PDOStatement;

/**
* @template TDbObj as DaftNestedObject
*
* @template-extends AbstractDaftObjectEasyDBRepository<TDbObj>
*
* @template-implements DaftNestedObjectTree<TDbObj>
*/
abstract class AbstractDaftObjectEasyDBTree extends AbstractDaftObjectEasyDBRepository implements DaftNestedObjectTree
{
    const BOOL_RETRIEVE_WITH_ROOT = false;

    const INT_LIMIT_ZERO = 0;

    const DEFAULT_COUNT_IF_NOT_OBJECT = 0;

    const INT_ARG_INDEX_FIRST = 1;

    const DEFAULT_BOOL_FETCH_TREE_NOT_PATH = true;

    /**
    * {@inheritdoc}
    *
    * @psalm-return array<int, TDbObj>
    */
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

    /**
    * {@inheritdoc}
    *
    * @psalm-param TDbObj $root
    */
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

    /**
    * {@inheritdoc}
    *
    * @psalm-param TDbObj $root
    */
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
    * {@inheritdoc}
    *
    * @psalm-return array<int, TDbObj>
    */
    public function RecallDaftNestedObjectTreeWithId(
        $id,
        bool $withRoot,
        ? int $limit
    ) : array {
        /**
        * @psalm-var TDbObj|null
        */
        $object = $this->RecallDaftObject($id);

        /**
        * @psalm-var array<int, TDbObj>
        */
        $out =
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

        return $out;
    }

    /**
    * {@inheritdoc}
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
                        : self::DEFAULT_COUNT_IF_NOT_OBJECT
                );
    }

    /**
    * {@inheritdoc}
    *
    * @psalm-param TDbObj $leaf
    *
    * @psalm-return array<int, TDbObj>
    */
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

    /**
    * {@inheritdoc}
    *
    * @psalm-param TDbObj $leaf
    */
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
    * {@inheritdoc}
    *
    * @psalm-return array<int, TDbObj>
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
    * {@inheritdoc}
    */
    public function CountDaftNestedObjectPathToId($id, bool $includeLeaf) : int
    {
        $object = $this->RecallDaftObject($id);

        return
            ($object instanceof DaftNestedObject)
                ? $this->CountDaftNestedObjectPathToObject($object, $includeLeaf)
                : self::DEFAULT_COUNT_IF_NOT_OBJECT;
    }

    final protected function SelectingQueryDaftNestedObjectTreeFromArgs(bool $recall) : string
    {
        if ($recall) {
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

    /**
    * {@inheritdoc}
    *
    * @psalm-return array<int, TDbObj>
    */
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

        /**
        * @var array<int, DaftNestedObject>
        *
        * @psalm-var array<int, TDbObj>
        */
        $out = array_filter(
            array_map([$this, 'RecallDaftObject'], (array) $sth->fetchAll(PDO::FETCH_NUM)),
            function (? DaftObject $maybe) : bool {
                return $maybe instanceof DaftNestedObject;
            }
        );

        return $out;
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
