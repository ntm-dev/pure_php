<?php

namespace Core\Views;

interface ViewInterface
{
    public function __construct(string $template = '');
    public function setdelimiter(string $leftDelimiter = '{{', string $rightDelimiter = '}}');
    public function assign(array $data);
    public function display();
}
