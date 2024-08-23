<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\ClienteModel;
use App\Models\MedidaModel;
use App\Models\ProdutoEspecificacaoModel;

class Pedidos extends ResourceController
{
    protected $pedidoModel;
    protected $produtoModel;
    protected $clienteModel;
    protected $medidaModel;
    protected $produto_especificacoesModel;
    public function __construct(){
        $this->pedidoModel = new PedidoModel();
        $this->produtoModel = new ProdutoModel();
        $this->clienteModel = new ClienteModel();
        $this->produto_especificacoesModel = new ProdutoEspecificacaoModel();
        $this->medidaModel = new MedidaModel();
    }
        public function index()
    {
        // Captura os dados do POST ou JSON
        $data = $this->request->getPost() ?: (array) $this->request->getJSON(true);

        if (empty($data)) {
            return $this->failValidationErrors('Dados vazios');
        }

        // Verifica se campos obrigatórios estão presentes
        $requiredFields = ['cliente_id', 'medida_id', 'produtos', 'quantidade', 'total','endereco'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->failValidationErrors("O campo \"$field\" é obrigatório");
            }
        }

        // Verifica se o produto, cliente e medida existem no banco de dados
        $existingProduct = $this->produtoModel->where('nome', $data['produtos'])->first();
        $existingClient = $this->clienteModel->where('id',$data['cliente_id'])->first();
        $existingMedida = $this->medidaModel->where('id',$data['medida_id'])->first();

        if (!$existingProduct) {
            return $this->failValidationErrors('O produto especificado não existe');
        }
        if (!$existingClient) {
            return $this->failValidationErrors('Cliente especificado não existe');
        }
        if (!$existingMedida) {
            return $this->failValidationErrors('A medida selecionada não existe');
        }

        // Verifica se os customizáveis existem, se foram passados
        $customizaveisIds = ['customizavel_id', 'customizavelDois_id', 'customizavelTres_id'];
        foreach ($customizaveisIds as $customizavelId) {
            if (!empty($data[$customizavelId]) && !$this->produto_especificacoesModel->buscaEspecificacoesProdutoSemPaginacao($data[$customizavelId])) {
                return $this->failValidationErrors("Customizável não existe: $customizavelId");
            }
        }

        // Prepara os dados para inserção
        $pedidoData = [
            'cliente_id' => $data['cliente_id'],
            'produtos' => $existingProduct->nome,
            'endereco' => $existingClient->endereco,
            'quantidade' => $data['quantidade'],
            'total' => $data['total'],
            'medida_id' =>  $existingMedida->id,
            'customizavel_id' => $data['customizavel_id'] ?? null,
            'customizavelDois_id' => $data['customizavelDois_id'] ?? null,
            'customizavelTres_id' => $data['customizavelTres_id'] ?? null
        ];

        // Salva os dados no banco de dados
        try {
            $this->pedidoModel->insert($pedidoData);
            return $this->respondCreated($pedidoData);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
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

