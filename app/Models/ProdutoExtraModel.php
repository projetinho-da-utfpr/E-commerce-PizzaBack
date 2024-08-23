<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoExtraModel extends Model
{

    protected $table            = 'produtos_extras';
    protected $returnType       = 'object';

    protected $allowedFields    = ['produto_id','extra_id'];


    protected $validationRules = [
        'extra_id'     => 'required|integer',
    ];
    protected $validationMessages = [
        'extra_id' => [
            'required' => 'O campo Extra é Obrigatório.',
        ],
    ];

    /*
    * Recupera os Extras ja colocados no produto 
    */
    public function buscaExtrasDoProduto(int $produto_id=null,int $quantidade_paginacao=null){
        return $this->select('extras.nome AS extra, extras.preco,produtos_extras.*')
                    ->join('extras','extras.id = produtos_extras.extra_id')
                    ->join('produtos','produtos.id = produtos_extras.produto_id')
                    ->where('produtos_extras.produto_id',$produto_id)
                    ->paginate($quantidade_paginacao);
    }
    public function validaExtraProduto($produto_id, $extra_id)
    {
        // Verifica se o extra está associado ao produto na tabela produtos_extras
        $extraProduto = $this->db->table('produtos_extras')
                                ->where('produto_id', $produto_id)
                                ->where('extra_id', $extra_id)
                                ->get()
                                ->getRow();

        if ($extraProduto) {
            return true; // O extra pode ser inserido no produto
        } else {
            return false; // O extra não pode ser inserido no produto
        }
    }
    public function getExtrasPermitidos($produtoId)
    {
        return $this->where('produto_id', $produtoId)
                    ->findAll();
    }
}
