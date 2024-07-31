<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;

class Pedidos extends ResourceController
{
    protected $pedidoModel;
    protected $produtoModel;
    public function __construct(){
        $this->pedidoModel = new PedidoModel();
        $this->produtoModel = new ProdutoModel();
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
            // Verifica se o campo 'nome' está presente nos dados
            if (!isset($data['nome'])) {
                return $this->failValidationErrors('O campo "nome" é obrigatório');
            }

            // Verifica se o produto com o nome especificado existe no banco de dados
            $existingProduct = $this->produtoModel->where('nome', $data['nome'])->first();
            if (!$existingProduct) {
                return $this->failValidationErrors('O produto especificado não existe');
            }

            // Salva os dados no banco de dados
            try {
                $this->pedidoModel->insert($data);
                // Retorna uma resposta com os dados recebidos e salvos
                return $this->respondCreated($data);
            } catch (\Exception $e) {
                return $this->failServerError($e->getMessage());
            }
        } else {
            // Retorna uma resposta de erro caso os dados estejam vazios
            return $this->failValidationErrors('Dados vazios');
        }
    }
}

