<?php

namespace App\Controllers\Line;

use App\Controllers\BaseController;
use Core\Service\Line\FlexMessage\Enum\BubbleSize;
use Core\Service\Line\FlexMessage\Bubble\Message;
use Core\Service\Line\FlexMessage\Component\Text;

class MessageController extends BaseController
{
    public function index(Message $message, Text $text)
    {
        $message->size(BubbleSize::Giga);
        $message->body->setContent($text->text('abc')->size('3xl'));
        return view('welcome');
    }
}
// (new HomeController)->index();
