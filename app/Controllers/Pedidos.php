<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\ClienteModel;
use App\Models\MedidaModel;
use App\Models\ProdutoEspecificacaoModel;
use App\Models\ProdutoExtraModel;

class Pedidos extends ResourceController
{
    protected $pedidoModel;
    protected $produtoModel;
    protected $clienteModel;
    protected $medidaModel;
    protected $produto_especificacoesModel;
    protected $produtoExtraModel; // Correção no nome do modelo

    public function __construct()
    {
        $this->pedidoModel = new PedidoModel();
        $this->produtoModel = new ProdutoModel();
        $this->clienteModel = new ClienteModel();
        $this->produto_especificacoesModel = new ProdutoEspecificacaoModel();
        $this->medidaModel = new MedidaModel();
        $this->produtoExtraModel = new ProdutoExtraModel(); // Correção na instância do modelo
    }

    public function index()
{
    // Obtém os dados enviados pelo cliente como array associativo
    $data = $this->request->getJSON(true);

    // Validação básica dos campos necessários
    if (!isset($data['cliente_id']) || !isset($data['endereco']) || !isset($data['produtos']) || !isset($data['total'])) {
        return $this->response->setStatusCode(400)->setJSON([
            'message' => 'Campos obrigatórios estão faltando.',
        ]);
    }

    // Verifica se o cliente existe
    $cliente = $this->clienteModel->find($data['cliente_id']);
    if (!$cliente) {
        return $this->failNotFound('Cliente não encontrado.');
    }

    // Verifica se o produto existe
    $produto = $this->produtoModel->find($data['produtos']);
    if (!$produto) {
        return $this->failNotFound('Produto não encontrado.');
    }

    // Verifica se a medida existe
    $medida = $this->medidaModel->find($data['medida']);
    if (!$medida) {
        return $this->failNotFound('Medida não permitida.');
    }

    // Valida se o produto pode ser customizado
    if (!$this->produto_especificacoesModel->validaCustomizavel($produto->id)) {
        return $this->failNotFound('Esse produto não pode ser customizado.');
    }

    // Valida os sabores, se fornecidos
    if (isset($data['sabores'])) {
        $validacaoSabores = $this->validaSabores($data['sabores'], $produto->id);
        if ($validacaoSabores['status'] === 'fail') {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => $validacaoSabores['message'],
            ]);
        }
    }

    // Valida os extras, se fornecidos
    if (isset($data['extras'])) {
        $validacaoExtras = $this->validaExtras($data['extras'], $produto->id);
        if ($validacaoExtras['status'] === 'fail') {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => $validacaoExtras['message'],
            ]);
        }
    }

    // Preparação dos dados para inserção
    $pedidoData = [
        'cliente_id' => $data['cliente_id'],
        'endereco' => $data['endereco'],
        'status' => isset($data['status']) ? $data['status'] : 'Pendente',
        'total' => $data['total'],
        'produtos' => $data['produtos'], // Supõe que seja uma string ou ID de produto
        'sabores' => isset($data['sabores']) ? implode(',', $data['sabores']) : null,
        'extras' => isset($data['extras']) ? implode(',', $data['extras']) : null,
        'medida_id' => $data['medida'],
        'quantidade' => $data['quantidade'],
    ];

    // Inserção no banco de dados
    if ($this->pedidoModel->insert($pedidoData)) {
        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Pedido criado com sucesso!',
            'pedido_id' => $this->pedidoModel->getInsertID(),
        ]);
    } else {
        return $this->response->setStatusCode(500)->setJSON([
            'message' => 'Erro ao criar o pedido.',
        ]);
    }
}

// Valida extras para um produto específico
public function validaExtras($extras, $produtoId)
{
    if (count($extras) > 3) {
        return [
            'status' => 'fail',
            'message' => 'Não é permitido mais de 3 extras por produto.'
        ];
    }

    $permitidos = $this->produtoExtraModel->getExtrasPermitidos($produtoId);
    
    // Extrair IDs dos extras permitidos
    $permitidosIds = array_column($permitidos, 'extra_id');

    // Validar cada extra
    foreach ($extras as $extraId) {
        if (!in_array($extraId, $permitidosIds)) {
            return [
                'status' => 'fail',
                'message' => 'Um ou mais extras não são permitidos.'
            ];
        }
    }

    return [
        'status' => 'success',
        'message' => 'Todos os extras são válidos.'
    ];
}

// Valida sabores para um produto específico
public function validaSabores(array $sabores, $produtoId)
{
    if (count($sabores) > 3) {
        return [
            'status'  => 'fail',
            'message' => 'Não é permitido mais de 3 sabores por pizza.',
        ];
    }

    // Obtém o produto para verificar a categoria
    $produto = $this->produtoModel->find($produtoId);

    // Verifica se o produto foi encontrado
    if (!$produto) {
        return [
            'status'  => 'fail',
            'message' => 'Produto não encontrado.',
        ];
    }

    // Obtém todos os produtos (sabores) da mesma categoria
    $saboresPermitidos = $this->produtoModel
        ->where('categoria_id', $produto->categoria_id)
        ->findAll();

    // Extrai os IDs dos sabores permitidos
    $saboresPermitidosIds = array_map(function($sabor) {
        return $sabor->id;
    }, $saboresPermitidos);

    // Verifica se todos os sabores fornecidos estão entre os permitidos
    foreach ($sabores as $saborId) {
        if (!in_array($saborId, $saboresPermitidosIds)) {
            return [
                'status'  => 'fail',
                'message' => 'Um ou mais sabores são inválidos para este produto.',
            ];
        }
    }

    // Todos os sabores são válidos
    return [
        'status'  => 'success',
        'message' => 'Todos os sabores são válidos.',
    ];
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
