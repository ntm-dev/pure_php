<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Request;
use Core\Console\Command;
use Core\Support\Facades\View;


class HomeController extends BaseController
{
    /** @var \Core\Http\Request */
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(Request $request)
    {
        // throw new \Exception('abc');
        return view('welcome');
        $a = app()->make(View::class);
        return View::render('love');
        $command = new Command;
        $max = 100000000;

        $user = User::first()->toArray();
        unset($user['id']);

        $command->progressStart($max);

        for ($i=0; $i < $max; $i++) {
            User::create($user);
            $command->progressAdvance();
        }
        $command->progressFinish();
        die;
        $a =  User::create(
            [
                'name' => 'Manh1'
            ]);
        $a = User::where(['id', '>' , '0'])->first();
        $b = User::get();
        foreach ($a as $key => $value) {
            echo "$key => $value<br>";
        }
        dd($a);
    }
}
// (new HomeController)->index();
