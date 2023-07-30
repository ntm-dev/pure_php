<?php

namespace App\Controllers\Line;

use App\Controllers\BaseController;
use Core\Service\Line\FlexMessage\Bubble\Message;
use Core\Service\Line\FlexMessage\Component\Text;
use Core\Service\Line\FlexMessage\Component\Box;
use Core\Service\Line\FlexMessage\Component\Separator;
use Core\Service\Line\FlexMessage\Component\Action\MessageAction;

class MessageController extends BaseController
{
    public function index(Message $message, Text $text, MessageAction $action, Separator $separator)
    {
        $message->body->addContent($text->text('予約内容')->size('sm')->color("#1DB446")->weight('bold'));
        $message->body->addContent($separator->margin('xxl'));

        $text = app()->make(Text::class)->text('予約番号：RE12345')->size('xs')->color("#000000")->flex(0)->wrap(true);
        $horizontalBox = app()->make(Box::class)->layout('horizontal')->margin('md');

        $message->body->addContent((clone $horizontalBox)->addContent($text));
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("スタッフ：staff_name")));
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("メニュー：無し")));
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("オプション：無し")));
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("日付：" . date("Y年m月d日"))));
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("開始時間：" . date("H:i"))));
        $message->body->addContent((clone $horizontalBox)->addContent([
            (clone $text)->text("ご入室に必要な暗証番号：")->flex(50)->wrap(false),
            (clone $text)->text("1234")->weight('bold')->flex(33)->size('lg')->color('#ff0000')->weight('bold'),
        ]));
        $message->body->addContent($separator);
        $message->body->addContent((clone $horizontalBox)->addContent((clone $text)->text("courceDescription")->color('#000080')));

        $message->footer->addContent((clone $horizontalBox->layout('vertical'))->addContent((clone $text)->text("予約が確定しました！")->color('#db7093')));

        $message->style->footer->separator(true);
        // dd($message->toArray());

        echo $message;die;
        return view('welcome');
    }
}
// (new HomeController)->index();
