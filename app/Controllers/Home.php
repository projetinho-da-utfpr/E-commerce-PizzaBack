<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'titulo' => 'Seja bem vindo (a)!',
        ];
        return view('Home/index',$data);
    }

}
