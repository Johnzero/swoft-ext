<?php

namespace Swoft\Migrations\Database\Column;

use Phinx\Db\Adapter\AdapterInterface;

class Jsonb extends Column
{
    protected $type = AdapterInterface::PHINX_TYPE_JSONB;
}
