<?php

namespace Swoft\Admin\Grid\Filter;

class Ilike extends AbstractFilter
{
    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     */
    public function condition($inputs)
    {
        $value = array_get($inputs, $this->column);

        if (is_array($value)) {
            $value = array_filter($value);
        }

        if ($this->isIgnoreValue($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, "%{$this->value}%", 'ilike');
    }
}
