<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\ClienteModel;

class Pedidos extends ResourceController
{
    protected $pedidoModel;
    protected $produtoModel;
    protected $clienteModel;
    public function __construct(){
        $this->pedidoModel = new PedidoModel();
        $this->produtoModel = new ProdutoModel();
        $this->clienteModel = new ClienteModel();
    }
    public function index()
    {
        // Captura os dados do POST
        $data = $this->request->getPost();
        
        // Se $data estiver vazio, tente capturar dados JSON
        if (empty($data)) {
            $json = $this->request->getJSON();
            if ($json) {
                $data = (array) $json;
            }
        }
        if (!empty($data)) {
            // Verifica se o campo 'id_cliente' está presente nos dados
            if (!isset($data['cliente_id'])) {
                return $this->failValidationErrors('O campo "cliente_id" é obrigatório');
            }

            // Verifica se o produto com o nome especificado existe no banco de dados
            $existingProduct = $this->produtoModel->where('nome', $data['produtos'])->first();
            $existingCustom = $this->produtoModel->where('nome', $data['customizavel'])->first();
            $existingClient = $this->clienteModel->where('id', $data['cliente_id'])->first();

            if (!$existingProduct) {
                return $this->failValidationErrors('O produto especificado não existe');
            }
            if (!$existingClient) {
                return $this->failValidationErrors('Cliente especificado não existe');
            }
            if (!$existingCustom) {
                return $this->failValidationErrors('O customizável especificado não existe');
            }

            // Prepara os dados para inserir
            $pedidoData = [
                'cliente_id' => $data['cliente_id'],
                'produtos' => $existingProduct->nome,
                'endereco' => $existingClient->endereco,
                'customizavel' => $existingCustom->nome,
                'quantidade' => $data['quantidade'], // ou outro campo relevante
                'total' => $data['total'],
                // adicione outros campos necessários
            ];

            // Salva os dados no banco de dados
            try {
                $this->pedidoModel->insert($pedidoData);
                // Retorna uma resposta com os dados recebidos e salvos
                return $this->respondCreated($pedidoData);
            } catch (\Exception $e) {
                return $this->failServerError($e->getMessage());
            }
        } else {
            // Retorna uma resposta de erro caso os dados estejam vazios
            return $this->failValidationErrors('Dados vazios');
        }
    }

    public function ultimosPedidos($clienteId = null)
    {
        if ($clienteId === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'ID do cliente é necessário.',
            ]);
        }

        // Obtém os últimos 5 pedidos do cliente
        $pedidos = $this->pedidoModel->buscaUltimosPedidos($clienteId);

        if (!empty($pedidos)) {
            return $this->response->setStatusCode(200)->setJSON([
                'message' => 'Pedidos encontrados com sucesso!',
                'data' => $pedidos
            ]);
        } else {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Nenhum pedido encontrado para este cliente.',
            ]);
        }
    }

}

