<?php

namespace Core\Views\Smarty;

use Smarty;
use Core\Support\Helper\Str;

class Base extends Smarty
{
    /** @var string template file path */
    protected $template;

    public function __construct($template)
    {
        parent::__construct();
        $this->template = implode('/', explode('.', $template));
        $this->bootstrap();
    }

    private function bootstrap()
    {
        $this->setTemplateDir(root_path() . "/" .config('VIEW_TEMPLATE_DIR'));
        $this->setCompileDir(config('VIEW_COMPILE_DIR', __DIR__ . "/compile"));
        // $this->setConfigDir(config('VIEW_CONFIG_DIR'));
        $this->setCacheDir(config('VIEW_CACHE_DIR', __DIR__ . "/cache"));
    }

    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        parent::display("$this->template.tpl" ,$template, $cache_id, $compile_id, $parent);
    }
}
