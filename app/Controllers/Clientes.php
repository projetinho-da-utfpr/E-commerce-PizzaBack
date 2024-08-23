<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Clientes extends ResourceController
{
    private $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new \App\Models\ClienteModel();
    }

    public function cadastrar()
    {
        $data = $this->request->getJSON(true);

        // Verificação de todos os campos obrigatórios
        if (!isset($data['email']) || !isset($data['nome']) || !isset($data['password']) || !isset($data['password_confirmation'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'Campos obrigatórios estão faltando.',
            ]);
        }

        // Verificando se o email está cadastrado no BD
        $clienteExistente = $this->clienteModel->buscaUsuarioPorEmail($data['email']);

        if ($clienteExistente) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'E-mail já cadastrado.',
            ]);
        }

        // Definir o valor do campo 'ativo' como 1
        $data['ativo'] = 1;

        // Insere o cliente no banco de dados
        if ($this->clienteModel->save($data)) {
            return $this->response->setStatusCode(201)->setJSON([
                'message' => 'Cliente cadastrado com sucesso!',
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'message' => 'Erro ao cadastrar cliente.',
                'errors' => $this->clienteModel->errors(),
            ]);
        }
    }

    public function buscarCliente($id = null)
    {
        if ($id === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'ID do cliente é necessário.',
            ]);
        }
    
        // Supondo que você tenha um método no modelo que busca o cliente pelo ID
        $cliente = $this->clienteModel->buscaInformacoesCliente($id);
    
        if ($cliente) {
            return $this->response->setStatusCode(200)->setJSON([
                'message' => 'Cliente encontrado com sucesso!',
                'data' => $cliente
            ]);
        } else {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Cliente não encontrado.',
            ]);
        }
    }

        public function alteraDadosCliente($id = null)
    {
        // Captura os dados do POST ou JSON
        $data = $this->request->getJSON(true); // true para array associativo

        // Verifica se o ID do cliente foi fornecido
        if (is_null($id)) {
            return $this->failValidationErrors('O ID do cliente é obrigatório');
        }

        // Verifica se o cliente existe no banco de dados
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return $this->failNotFound('Cliente não encontrado');
        }

        // Atualiza os dados do cliente
        try {
            $this->clienteModel->update($id, $data);

            // Retorna sucesso
            return $this->respond(['status' => 'success', 'message' => 'Dados do cliente atualizados com sucesso']);
        } catch (\Exception $e) {
            // Retorna erro em caso de falha
            return $this->failServerError('Erro ao atualizar dados do cliente: ' . $e->getMessage());
        }
    }

}
