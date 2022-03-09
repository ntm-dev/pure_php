<?php

namespace App\Controllers;

use App\Models\User;

class HomeController extends BaseController
{
    public function index()
    {
        $a = new User;
        $a->select(['name']);
        $c = $a->select()->get();
        $b = new User;
        $d = $b->select()->where(['name', 'like', '%u%'])->get();
        dd([$c, $d]);
        echo "first controller";
    }
}