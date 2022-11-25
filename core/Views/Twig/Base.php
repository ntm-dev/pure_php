<?php

namespace Core\Views\Twig;

use Twig\Lexer;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;
use Core\Views\ViewInterface;
use Core\Views\ViewAbstract;

class Base extends ViewAbstract implements ViewInterface
{
    /** template name */
    public const TEMPLATE_NAME = 'twig';

    /** defautl template file extension */
    public const TEMPLATE_EXTENSION = 'twig';

    /** defautl template dir */
    private const TEMPLATE_DIR = 'resources/views/twig';

    /** @var string template file path */
    protected $template;

    /** view instance */
    protected $view;

    /** assign data */
    protected $data = [];

    public function __construct(string $template = '')
    {
        parent::__construct();
        $this->template = $template;
        $this->bootstrap();
    }

    public function setTempate(string $template = '')
    {
        $this->template = $template;

        return $this;
    }

    private function bootstrap()
    {
        $loader = new FilesystemLoader(base_path() . "/" . config('VIEW_TEMPLATE_DIR', static::TEMPLATE_DIR));
        $this->view = new Environment($loader, [
            'auto_reload' => true,
            'cache' => config('VIEW_CACHE_DIR', __DIR__ . "/cache"),
        ]);
        $this->view->addFunction(new TwigFunction('app_name', 'app_name'));
    }

    public function assign(array $data)
    {
        $this->data = $data;
    }

    public function display($path = '')
    {
        echo $this->render();
    }

    public function render($path = '')
    {
        return $this->view->render("$this->template." . static::TEMPLATE_EXTENSION, $this->data);
    }

    public function setdelimiter(string $leftDelimiter = '{{', string $rightDelimiter = '}}')
    {
        $lexer = new Lexer($this->view, [
            // 'tag_comment'   => ['{#', '#}'],
            // 'tag_block'     => ['{%', '%}'],
            'tag_variable'  => [$leftDelimiter, $rightDelimiter],
            // 'interpolation' => ['#{', '}'],
        ]);
        $this->view->setLexer($lexer);
    }
}
