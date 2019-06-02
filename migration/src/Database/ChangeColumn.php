<?php

namespace Swoft\Migrations\Database;

use Swoft\Migrations\Database\Column\Biginteger;
use Swoft\Migrations\Database\Column\Binary;
use Swoft\Migrations\Database\Column\Blob;
use Swoft\Migrations\Database\Column\BooleanColumn;
use Swoft\Migrations\Database\Column\Char;
use Swoft\Migrations\Database\Column\Cidr;
use Swoft\Migrations\Database\Column\Column;
use Swoft\Migrations\Database\Column\Date;
use Swoft\Migrations\Database\Column\Datetime;
use Swoft\Migrations\Database\Column\Decimal;
use Swoft\Migrations\Database\Column\Enum;
use Swoft\Migrations\Database\Column\Filestream;
use Swoft\Migrations\Database\Column\FloatColumn;
use Swoft\Migrations\Database\Column\Geometry;
use Swoft\Migrations\Database\Column\Inet;
use Swoft\Migrations\Database\Column\IntegerColumn;
use Swoft\Migrations\Database\Column\Interval;
use Swoft\Migrations\Database\Column\Json;
use Swoft\Migrations\Database\Column\Jsonb;
use Swoft\Migrations\Database\Column\Linestring;
use Swoft\Migrations\Database\Column\Macaddr;
use Swoft\Migrations\Database\Column\Point;
use Swoft\Migrations\Database\Column\Polygon;
use Swoft\Migrations\Database\Column\Set;
use Swoft\Migrations\Database\Column\StringColumn;
use Swoft\Migrations\Database\Column\Text;
use Swoft\Migrations\Database\Column\Time;
use Swoft\Migrations\Database\Column\Timestamp;
use Swoft\Migrations\Database\Column\Uuid;
use Swoft\Migrations\Database\Column\Varbinary;
use Swoft\Migrations\Exceptions\UnknownColumnException;
use Phinx\Db\Table as PhinxTable;

/**
 *
 * @method \Swoft\Migrations\Database\Column\StringColumn string($column)
 * @method \Swoft\Migrations\Database\Column\Char char($column)
 * @method \Swoft\Migrations\Database\Column\Text text($column)
 * @method \Swoft\Migrations\Database\Column\Integer integer($column)
 * @method \Swoft\Migrations\Database\Column\Biginteger biginteger($column)
 * @method \Swoft\Migrations\Database\Column\FloatColumn float($column)
 * @method \Swoft\Migrations\Database\Column\Decimal decimal($column)
 * @method \Swoft\Migrations\Database\Column\Datetime datetime($column)
 * @method \Swoft\Migrations\Database\Column\Timestamp timestamp($column)
 * @method \Swoft\Migrations\Database\Column\Time time($column)
 * @method \Swoft\Migrations\Database\Column\Date date($column)
 * @method \Swoft\Migrations\Database\Column\Binary binary($column)
 * @method \Swoft\Migrations\Database\Column\Varbinary varbinary($column)
 * @method \Swoft\Migrations\Database\Column\Blob blob($column)
 * @method \Swoft\Migrations\Database\Column\Boolean boolean($column)
 * @method \Swoft\Migrations\Database\Column\Json json($column)
 * @method \Swoft\Migrations\Database\Column\Jsonb jsonb($column)
 * @method \Swoft\Migrations\Database\Column\Uuid uuid($column)
 * @method \Swoft\Migrations\Database\Column\Filestream filestream($column)
 * @method \Swoft\Migrations\Database\Column\Geometry geometry($column)
 * @method \Swoft\Migrations\Database\Column\Point point($column)
 * @method \Swoft\Migrations\Database\Column\Linestring linestring($column)
 * @method \Swoft\Migrations\Database\Column\Polygon polygon($column)
 * @method \Swoft\Migrations\Database\Column\Enum enum($column)
 * @method \Swoft\Migrations\Database\Column\Set set($column)
 * @method \Swoft\Migrations\Database\Column\Cidr cidr($column)
 * @method \Swoft\Migrations\Database\Column\Inet inet($column)
 * @method \Swoft\Migrations\Database\Column\Macaddr macaddr($column)
 * @method \Swoft\Migrations\Database\Column\Interval Interval($column)
 */
class ChangeColumn
{
    /**
     * @var TableProxy
     */
    protected $table;

    /**
     * @var array
     */
    protected $columntypes = [];

    /**
     * @var array
     */
    protected $columns = [];

    public function __construct(TableProxy $table)
    {
        $this->table = $table;

        $this->columntypes = $table->getColumntypes();
    }

    /**
     * @param $column
     * @param $name
     * @return Column
     * @throws UnknownColumnException
     */
    protected function createColumn($column, $name)
    {
        $class = $this->columntypes[$column];

        return new $class($name);
    }

    /**
     * 增加字段
     *
     * @param Column $column
     * @return $this
     */
    protected function pushColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * 完成字段配置
     *
     * @return $this
     */
    public function done()
    {
        foreach ($this->columns as $column) {
            $this->table->phinx()->changeColumn(
                $column->getName(), $column->getType(), $column->getOptions()
            );
        }

        $this->columns = [];

        return $this;
    }

    public function __call($method, $arguments)
    {
        if (!isset($this->columntypes[$method])) {
            throw new UnknownColumnException;
        }
        $name = array_get($arguments, 0);
        if (! $name) {
            throw new \InvalidArgumentException('The column name cannot be empty.');
        }

        $column = $this->createColumn($method, $name);

        $this->pushColumn($column);

        return $column;

    }
}
