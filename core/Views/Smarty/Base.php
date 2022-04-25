<?php

namespace Core\Views\Smarty;

use Smarty;
use Core\Views\ViewInterface;
use Core\Views\ViewAbstract;

class Base extends ViewAbstract implements ViewInterface
{
    /** template name */
    public const TEMPLATE_NAME = 'smarty';

    /** defautl template file extension */
    public const TEMPLATE_EXTENSION = 'tpl';

    /** defautl template dir */
    private const TEMPLATE_DIR = 'resources/views/smarty';

    /** @var string template file path */
    protected $template;

    /** view instance */
    protected $view;

    public function __construct(string $template = '')
    {
        parent::__construct();
        $this->template = $template;
        $this->view = new Smarty;
        $this->bootstrap();
    }

    private function bootstrap()
    {
        $this->view->setTemplateDir(root_path() . "/" .config('VIEW_TEMPLATE_DIR', static::TEMPLATE_DIR));
        $this->view->setCompileDir(config('VIEW_COMPILE_DIR', __DIR__ . "/compile"));
        // $this->view->setConfigDir(config('VIEW_CONFIG_DIR'));
        $this->view->setCacheDir(config('VIEW_CACHE_DIR', __DIR__ . "/cache"));
    }

    public function assign($data)
    {
        $this->view->assign($data);
    }

    public function display()
    {
        $this->view->display("$this->template." . static::TEMPLATE_EXTENSION);
    }

    public function setDelimiter(string $leftDelimiter = '{{ ', string $rightDelimiter = ' }}')
    {
        $this->view->setLeftDelimiter($leftDelimiter);
        $this->view->setRightDelimiter($rightDelimiter);
    }
}
