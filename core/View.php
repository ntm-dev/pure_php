<?php

namespace Core;

use Core\Views\ViewInterface;
use Core\Views\Twig\Base as TwigView;
use Core\Views\Smarty\Base as SmartyView;

class View implements ViewInterface
{
    /** @var string template file path */
    protected $template;

    /** @var string view engine */
    protected $viewEngine;

    /** view instance */
    protected $view;

    public function __construct(string $template = '')
    {
        $this->template = implode('/', explode('.', $template));
        $this->bootstrap();
    }

    private function bootstrap()
    {
        $this->viewEngine = config('VIEW_ENGINE', SmartyView::TEMPLATE_NAME);
        switch ($this->viewEngine) {
            case TwigView::TEMPLATE_NAME:
                $this->view = new TwigView($this->template);
                break;
            default:
                $this->view = new SmartyView($this->template);
                break;
        }
        $this->setDelimiter(config('VIEW_LEFT_DELIMITER', '{{'), config('VIEW_RIGHT_DELIMITER', '}}'));
    }

    public function setTempate($path = '')
    {
        $this->view->setTempate($path);

        return $this;
    }

    public function setDelimiter(string $leftDelimiter = '{{', string $rightDelimiter = '}}')
    {
        $this->view->setDelimiter($leftDelimiter, $rightDelimiter);
    }

    public function assign(array $data)
    {
        $this->view->assign($data);
    }

    public function attachViewData($path, array $data): static
    {
        if ($path) {
            $this->setTempate($path);
        }

        if (!empty($data)) {
            $this->assign($data);
        }

        return $this;
    }

    public function display($path = '', array $data = [])
    {
        if ($path) {
            $this->view->setTempate($path);
        }

        $this->view->display();
    }

    public function render($path = '', array $data = [])
    {
        $this->attachViewData($path, $data);

        return $this->view->render();
    }
}
