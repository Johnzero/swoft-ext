<?php

namespace Swoft\Migrations;

use Swoft\Migrations\Console\Application;
use Swoft\Migrations\Database\TableProxy;
use Swoft\Migrations\Exceptions\InvalidArgumentException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table as PhinxTable;

class Migrator extends AbstractMigration
{
    /**
     * 切换配置文件
     *
     * @var string
     */
    protected $configure = null;

    /**
     * @param string $tableName
     * @param \Closure $callback
     * @return $this
     */
    public function tableProxy($tableName, $callback = null)
    {
        $table = new TableProxy($this->table($tableName));

        $callback($table);

        $table->done();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function table($tableName, $options = [])
    {
        return new PhinxTable($tableName, $options, $this->getAdapter());
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $options = $this->getDbConfig();

            $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

            if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
                $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
            }

            $this->adapter = $adapter;
        }

        return $this->adapter;
    }

    /**
     * 获取数据库配置
     *
     * @return array
     */
    protected function getDbConfig()
    {
        $dbConfig = Config::getDatabase($this->configure);

        $dbConfig['default_migration_table'] = Config::get('environments.default_migration_table', $this->configure) ?: 'phinxlog';

        return $dbConfig;
    }

}
