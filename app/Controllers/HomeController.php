<?php

namespace App\Controllers;

use App\Models\User;
use Core\Support\Helper\Str;
use Core\Support\Validation\Rule;
use App\Requests\NewRequest;
use Core\Support\Facades\Response;
class HomeController extends BaseController
{
    public function index()
    {
        Response::setCookie('name', 'value', 30, "", "", true, true);
        // abort(404);
        return view('error.404', ['a' => 'b']);
        // $a = collect([
        //     ['foo' => 10],
        //     ['foo' => 10],
        //     ['foo' => 20],
        //     ['foo' => 40]
        // ]);
        // $b = $a->avg('foo');
        $a = ['a'=> 'a'];
        $b = NewRequest::validate($a);
        $rule = new Rule();
        // dd($rule->validateHankaku('気'));// return 'Hello_World'
        // dd($rule->validateHankaku('片仮名'));// return 'Hello_World'
        // dd($rule->validateHankaku('つけてね'));// return 'Hello_World'
        dd(Rule::isJapanese('カタカナ'));// return 'Hello_World'
        dd($rule->validateHankaku('カタカナ'));// return 'Hello_World'
        die;
        $a = collect(['a', 'b', 'c']);
        $a->abc = 'ádssd';
        $a->append('foobar');
        $a->pop('abc');
        
        $a[0];
        $a[4] = '4';
        $c = isset($a[4]);
        unset($a[4]);
        foreach($a as $key => &$value) {
            echo "$key => $value<br>";
        }

    }
}