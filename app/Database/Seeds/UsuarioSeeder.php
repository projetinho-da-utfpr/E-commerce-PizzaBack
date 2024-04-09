<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $usuarioModel = new \App\Models\UsuarioModel;
        $usuario = [
            'nome' => 'Matheus Angelo',
            'email' => 'admin@admin.com',
            'cpf' => '415.680.020-25',
            'telefone' => '44 998040796',
            'password'=> '123456',
            'password_confirmation'=>'123456',
        ];

        $usuarioModel->protect(false)->insert($usuario);


        dd($usuarioModel->errors());
    }
}
