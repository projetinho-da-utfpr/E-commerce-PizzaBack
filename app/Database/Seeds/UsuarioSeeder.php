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
        ];

        $usuarioModel->protect(false)->insert($usuario);

        $usuario = [
            'nome' => 'Marcos GPS',
            'email' => 'marcola@admin.com',
            'cpf' => '283.600.670-66',
            'telefone' => '44 9898748492',
        ];

        $usuarioModel->protect(false)->insert($usuario);

        dd($usuarioModel->errors());
    }
}
