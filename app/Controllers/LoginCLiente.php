<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class LoginCLiente extends ResourceController
{
    private $clienteModel;

    public function __construct(){
        $this->clienteModel = new \App\Models\ClienteModel();
    }
    public function index()
{
    $data = $this->request->getJSON(true); // Usando getJSON ao invés de getPost
    
    if (!isset($data['email']) || !isset($data['password'])) {
        return $this->response->setStatusCode(400)->setJSON([
            'message' => 'Campos obrigatórios estão faltando.',
        ]);
    }

    $cliente = $this->clienteModel->buscaUsuarioPorEmail($data['email']);
    
    if (!$cliente || !password_verify($data['password'], $cliente->password_hash)) {
        return $this->response->setStatusCode(400)->setJSON([
            'message' => 'E-mail ou senha esta incorreto.',
        ]);
    }

    // Retorna os dados do cliente junto com a mensagem de sucesso
    return $this->response->setStatusCode(200)->setJSON([
        'message' => 'Login bem-sucedido!',
        'cliente' => [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
            'email' => $cliente->email,
            'telefone' => $cliente->telefone,
            'endereco' => $cliente->endereco,
            // Adicione outros dados conforme necessário
        ]
    ]);
}

    
}
