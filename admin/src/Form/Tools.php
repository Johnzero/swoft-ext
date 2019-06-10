<?php

namespace Swoft\Admin\Form;

use Swoft\Support\Contracts\Htmlable;
use Swoft\Support\Contracts\Renderable;
use Swoft\Support\Collection;
use Swoft\Admin\Admin;

class Tools implements Renderable
{
    /**
     * @var Builder
     */
    protected $form;

    /**
     * Collection of tools.
     *
     * @var array
     */
    protected $tools = ['delete', 'view', 'list'];

    /**
     * Tools should be appends to default tools.
     *
     * @var Collection
     */
    protected $appends;

    /**
     * Tools should be prepends to default tools.
     *
     * @var Collection
     */
    protected $prepends;

    /**
     * Create a new Tools instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->form = $builder;
        $this->appends = new Collection();
        $this->prepends = new Collection();
    }

    /**
     * Append a tools.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function append($tool)
    {
        $this->appends->push($tool);

        return $this;
    }

    /**
     * Prepend a tool.
     *
     * @param mixed $tool
     *
     * @return $this
     */
    public function prepend($tool)
    {
        $this->prepends->push($tool);

        return $this;
    }

    /**
     * Disable `list` tool.
     *
     * @return $this
     */
    public function disableList()
    {
        array_delete($this->tools, 'list');

        return $this;
    }

    /**
     * Disable `delete` tool.
     *
     * @return $this
     */
    public function disableDelete()
    {
        array_delete($this->tools, 'delete');

        return $this;
    }

    /**
     * Disable `edit` tool.
     *
     * @return $this
     */
    public function disableView()
    {
        array_delete($this->tools, 'view');

        return $this;
    }

    /**
     * Get request path for resource list.
     *
     * @return string
     */
    protected function getListPath()
    {
        return Admin::url()->list();
    }

    /**
     * Get request path for edit.
     *
     * @return string
     */
    protected function getDeletePath()
    {
        return Admin::url()->delete($this->form->getResourceId());
    }

    /**
     * Get request path for delete.
     *
     * @return string
     */
    protected function getViewPath()
    {
        return Admin::url()->view($this->form->getResourceId());
    }

    /**
     * Render list button.
     *
     * @return string
     */
    protected function renderList()
    {
        $text = t('List', 'admin');

        return <<<EOT
<div class="btn-group pull-right default" style="margin-right: 5px">
    <a href="{$this->getListPath()}" class="btn btn-sm btn-default" title="$text"><i class="fa fa-list"></i><span class="hidden-xs">&nbsp;$text</span></a>
</div>
EOT;
    }

    /**
     * Render list button.
     *
     * @return string
     */
    protected function renderView()
    {
        $view = t('View', 'admin');

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="{$this->getViewPath()}" class="btn btn-sm btn-info" title="{$view}">
        <i class="fa fa-eye"></i><span class="hidden-xs"> {$view}</span>
    </a>
</div>
HTML;
    }

    /**
     * Render `delete` tool.
     *
     * @return string
     */
    protected function renderDelete()
    {
        if ($this->form->isMode(Builder::MODE_CREATE)) {
            return '';
        }

        $deleteConfirm = t('Are you sure to delete this item?', 'admin');
        $confirm = t('Confirm', 'admin');
        $cancel = t('Cancel', 'admin');

        $class = uniqid();

        $script = <<<SCRIPT
$('.{$class}-delete').unbind('click').click(function() {
     LA.confirm('$deleteConfirm', function () {
        $.ajax({
            method: 'delete',
            url: '{$this->getDeletePath()}',
            data: {
                _method:'delete',
                _token:LA.token
            },
            success: function (data) {
                 $.pjax({container:'#pjax-container', url: '{$this->getListPath()}' });

                if (typeof data === 'object') {
                     if (data.status) {
                        LA.success(data.message);
                    } else {
                        LA.error(data.message);
                    }
                }
            }
        });
    }, '$confirm','$cancel');
});

SCRIPT;

        $delete = t('Delete', 'admin');

        Admin::script($script);

        return <<<HTML
<div class="btn-group pull-right" style="margin-right: 5px">
    <a href="javascript:void(0);" class="btn btn-sm btn-danger {$class}-delete" title="{$delete}">
        <i class="fa fa-trash"></i><span class="hidden-xs">  {$delete}</span>
    </a>
</div>
HTML;
    }

    /**
     * Add a tool.
     *
     * @param string $tool
     *
     * @return $this
     *
     * @deprecated use append instead.
     */
    public function add($tool)
    {
        return $this->append($tool);
    }

    /**
     * Disable list button.
     *
     * @return $this
     *
     * @deprecated Use disableList instead.
     */
    public function disableListButton()
    {
        return $this->disableList();
    }

    /**
     * Render custom tools.
     *
     * @param Collection $tools
     *
     * @return mixed
     */
    protected function renderCustomTools($tools)
    {
        if (empty($tools)) {
            return '';
        }

        return $tools->map(function ($tool) {
            if ($tool instanceof Renderable) {
                return $tool->render();
            }

            if ($tool instanceof Htmlable) {
                return $tool->toHtml();
            }

            return (string) $tool;
        })->implode(' ');
    }

    /**
     * Render tools.
     *
     * @return string
     */
    public function render()
    {
        $output = $this->renderCustomTools($this->prepends);

        foreach ($this->tools as $tool) {
            $renderMethod = 'render'.ucfirst($tool);
            $output .= $this->$renderMethod();
        }

        return $output.$this->renderCustomTools($this->appends);
    }
}
