<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\DaftNestedDbObject\Tests\Fixtures;

use ParagonIE\EasyDB\EasyDB;
use ReflectionClass;
use ReflectionType;
use RuntimeException;
use SignpostMarv\DaftObject\AbstractDaftObjectEasyDBTree;
use SignpostMarv\DaftObject\DaftNestedObject;
use SignpostMarv\DaftObject\DefinesOwnIdPropertiesInterface;
use SignpostMarv\DaftObject\TraitDaftNestedObjectIntTree;

/**
* @template TDbObj as DaftNestedObject
*
* @template-extends AbstractDaftObjectEasyDBTree<TDbObj>
*/
class TestObjectRepository extends AbstractDaftObjectEasyDBTree
{
    use TraitDaftNestedObjectIntTree;

    /**
    * @var int
    */
    public static $counts = 0;

    /**
    * @var int
    */
    protected $count = 0;

    /**
    * @psalm-param class-string<TDbObj> $type
    */
    protected function __construct(string $type, EasyDB $db)
    {
        parent::__construct($type, $db);

        ++self::$counts;

        $this->count = self::$counts;

        /**
        * @var DefinesOwnIdPropertiesInterface
        */
        $type = $type;

        $query = 'CREATE TABLE ' . $db->escapeIdentifier($this->DaftObjectDatabaseTable()) . ' (';

        $queryParts = [];

        $ref = new ReflectionClass($type);
        $nullables = $type::DaftObjectNullableProperties();

        foreach ($type::DaftObjectProperties() as $prop) {
            $methodName = 'Get' . ucfirst($prop);

            if ('GetDaftNestedObjectParentId' === $methodName) {
                continue;
            }

            if (true === $ref->hasMethod($methodName)) {
                /**
                * @var ReflectionType
                */
                $refReturn = $ref->getMethod($methodName)->getReturnType();

                $queryPart =
                    $db->escapeIdentifier($prop) .
                    static::QueryPartTypeFromRefReturn($refReturn);

                if (false === in_array($prop, $nullables, true)) {
                    $queryPart .= ' NOT NULL';
                }

                $queryParts[] = $queryPart;
            }
        }

        $primaryKeyCols = [];

        foreach ($type::DaftObjectIdProperties() as $col) {
            $primaryKeyCols[] = $db->escapeIdentifier($col);
        }

        if (count($primaryKeyCols) > 0) {
            $queryParts[] = 'PRIMARY KEY (' . implode(',', $primaryKeyCols) . ')';
        }

        $query .= implode(',', $queryParts) . ');';

        $db->safeQuery($query);
    }

    protected function DaftObjectDatabaseTable() : string
    {
        return
            preg_replace('/[^a-z]+/', '_', (string) mb_strtolower($this->type)) .
            '_' .
            ((string) $this->count);
    }

    protected static function QueryPartTypeFromRefReturn(ReflectionType $refReturn) : string
    {
        if ($refReturn->isBuiltin()) {
            switch ($refReturn->__toString()) {
                case 'string':
                    return ' VARCHAR(255)';
                case 'float':
                    return ' REAL';
                case 'int':
                case 'bool':
                    return ' INTEGER';
                default:
                    throw new RuntimeException(sprintf(
                        'Unsupported data type! (%s)',
                        $refReturn->__toString()
                    ));
            }
        }

        throw new RuntimeException('Only supports builtins');
    }
}
