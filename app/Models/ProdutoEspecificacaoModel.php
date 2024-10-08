<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoEspecificacaoModel extends Model
{
    protected $table            = 'produtos_especificacoes';
    protected $returnType       = 'object';
    protected $allowedFields    = ['produto_id','medida_id','preco','customizavel'];

    protected $validationRules = [
        'medida_id'     => 'required|integer',
        'preco'     => 'required|greater_than[0]',
        'customizavel' => 'required|integer',
    ];
    protected $validationMessages = [
        'extra_id' => [
            'required' => 'O campo Extra é Obrigatório.',
        ],
    ];

    /*
    * Recupera as medidas ja colocadas no produto 
    */
    public function buscaEspecificacoesProduto(int $produto_id,int $quantidade_paginacao){

        return $this->select('medidas.nome AS medida, produtos_especificacoes.*')
                    ->join('medidas','medidas.id = produtos_especificacoes.medida_id')
                    ->join('produtos','produtos.id = produtos_especificacoes.produto_id')
                    ->where('produtos_especificacoes.produto_id',$produto_id)
                    ->paginate($quantidade_paginacao);
    }
    public function buscaEspecificacoesProdutoSemPaginacao(int $produto_id)
    {
        return $this->select('medidas.nome AS medida, 
                              produtos_especificacoes.*, 
                              produtos.nome AS produto_nome') // Adicione aqui outros nomes que deseja retornar
                    ->join('medidas', 'medidas.id = produtos_especificacoes.medida_id')
                    ->join('produtos', 'produtos.id = produtos_especificacoes.produto_id')
                    ->where('produtos_especificacoes.produto_id', $produto_id)
                    ->findAll(); // Utiliza findAll() para retornar todos os resultados sem paginação
    }
    
    public function validaCustomizavel($id_produto)
{
    // Busca a especificação do produto pelo ID
    $produtoEspecificacao = $this->where('produto_id', $id_produto)->first();

    // Verifica se o produto foi encontrado e se é customizável
    if ($produtoEspecificacao && $produtoEspecificacao->customizavel) {
        return true;
    }

    return false;
}

}
