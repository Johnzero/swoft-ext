<?php

namespace Swoft\Admin\Grid\Filter;

use Swoft\Admin\Admin;
use Swoft\Db\Query\Builder as QueryBuilder;

class Between extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::filter.between';

    /**
     * 是否把表单值转化为时间戳
     *
     * @var bool
     */
    protected $timestamp = false;

    /**
     * @var int
     */
    protected $width = 4;

    /**
     * Format id.
     *
     * @param string $column
     *
     * @return array|string
     */
    public function formatId($column)
    {
        $id = str_replace('.', '_', $column);

        return ['start' => "{$id}_start", 'end' => "{$id}_end"];
    }

    /**
     * Format two field names of this filter.
     *
     * @param string $column
     *
     * @return array
     */
    protected function formatName($column)
    {
        $columns = explode('.', $column);

        if (count($columns) == 1) {
            $name = $columns[0];
        } else {
            $name = array_shift($columns);

            foreach ($columns as $column) {
                $name .= "[$column]";
            }
        }

        return ['start' => "{$name}[start]", 'end' => "{$name}[end]"];
    }

    /**
     * 把表单值转化为时间戳
     *
     * @return $this
     */
    public function timestamp()
    {
        $this->timestamp = true;
        return $this;
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return mixed
     */
    public function condition($inputs)
    {
        if (!array_has($inputs, $this->column)) {
            return;
        }

        $this->value = array_get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (empty($value)) {
            return;
        }
        $start = $value['start'] ?? null;
        $end = $value['end'] ?? null;

        if ($this->isIgnoreValue($start) && $this->isIgnoreValue($end)) {
            return;
        }
        
        if ($this->timestamp) {
            if (!empty($start) && !$this->isIgnoreValue($start)) {
                $start = $start ? strtotime($start) : '';
            }
            if (!empty($end) && !$this->isIgnoreValue($end)) {
                $end   = $end ? strtotime($end) : '';
            }
        }

        if ($this->isIgnoreValue($start)) {
            return $this->buildCondition($this->column, $end, QueryBuilder::OPERATOR_LTE);
        }

        if ($this->isIgnoreValue($end)) {
            return $this->buildCondition($this->column, $start, QueryBuilder::OPERATOR_GTE);
        }

        $this->query = 'whereBetween';

        return  [$this->query => [$this->column, $start, $end]];
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function datetime($options = [])
    {
        $this->view = 'admin::filter.betweenDatetime';

        $this->setupDatetime($options);

        Admin::css([
            '@admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
        ]);
        Admin::js(
            [
                '@admin/moment/min/moment-with-locales.min.js',
                '@admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            ]
        );

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setupDatetime($options = [])
    {
        $options['format'] = array_get($options, 'format', 'YYYY-MM-DD HH:mm:ss');
        $options['locale'] = array_get($options, 'locale', current_lang());

        $startOptions = json_encode($options);
        $endOptions = json_encode($options + ['useCurrent' => false]);

        $script = <<<EOT
            $('#{$this->id['start']}').datetimepicker($startOptions);
            $('#{$this->id['end']}').datetimepicker($endOptions);
            $("#{$this->id['start']}").on("dp.change", function (e) {
                $('#{$this->id['end']}').data("DateTimePicker").minDate(e.date);
            });
            $("#{$this->id['end']}").on("dp.change", function (e) {
                $('#{$this->id['start']}').data("DateTimePicker").maxDate(e.date);
            });
EOT;

        Admin::script($script);
    }
}
