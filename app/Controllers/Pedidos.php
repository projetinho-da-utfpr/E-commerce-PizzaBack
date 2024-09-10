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
    
        // Verifica se o produto existe e se `produtos` é um array
        if (!is_array($data['produtos']) || empty($data['produtos'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'Produtos devem ser fornecidos como um array válido.',
            ]);
        }
    
        foreach ($data['produtos'] as $produtoId) {
            $produto = $this->produtoModel->find($produtoId);
            if (!$produto) {
                return $this->failNotFound("Produto com ID {$produtoId} não encontrado.");
            }
        }
    
        // Verifica se a medida existe
        $medida = $this->medidaModel->find($data['medida']);
        if (!$medida) {
            return $this->failNotFound('Medida não permitida.');
        }
    
        // Valida se o produto pode ser customizado
        if (!$this->produto_especificacoesModel->validaCustomizavel($data['produtos'][0])) { // Exemplo: valida o primeiro produto
            return $this->failNotFound('Esse produto não pode ser customizado.');
        }
    
        // Valida os sabores, se fornecidos
        if (isset($data['sabores'])) {
            $validacaoSabores = $this->validaSabores($data['sabores'], $data['produtos'][0]); // Exemplo: valida o primeiro produto
            if ($validacaoSabores['status'] === 'fail') {
                return $this->response->setStatusCode(400)->setJSON([
                    'message' => $validacaoSabores['message'],
                ]);
            }
        }
    
        // Valida os extras, se fornecidos
        if (isset($data['extras'])) {
            $validacaoExtras = $this->validaExtras($data['extras'], $data['produtos']); // Passa todos os produtos
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
            'produtos' => implode(',', $data['produtos']), // Agora produtos são armazenados como string separada por vírgulas
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
// Valida extras para um conjunto de produtos
public function validaExtras($extras, $produtos)
{
    if (count($extras) > 3) {
        return [
            'status' => 'fail',
            'message' => 'Não é permitido mais de 3 extras por produto.'
        ];
    }

    // Loop pelos extras para verificar se pelo menos um produto aceita cada extra
    foreach ($extras as $extraId) {
        $extraPermitido = false; // Flag para verificar se o extra é permitido por algum produto
        
        foreach ($produtos as $produtoId) {
            $permitidos = $this->produtoExtraModel->getExtrasPermitidos($produtoId);
            
            // Extrair IDs dos extras permitidos para o produto atual
            $permitidosIds = array_column($permitidos, 'extra_id');
            
            if (in_array($extraId, $permitidosIds)) {
                $extraPermitido = true; // Se o extra for permitido por algum produto, a flag é marcada
                break; // Para a verificação desse extra, pois já encontramos um produto que o aceita
            }
        }

        // Se nenhum produto permitir esse extra, retornar erro
        if (!$extraPermitido) {
            return [
                'status' => 'fail',
                'message' => 'Um ou mais extras não são permitidos para os produtos selecionados.'
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
