<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{

    protected $table            = 'produtos';
    protected $returnType       = 'App\Entities\Produto';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'categoria_id',
        'nome',
        'slug',
        'imagem',
        'ingredientes',
        'ativo',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules = [
        'nome'     => 'required|max_length[120]|min_length[2]|is_unique[produtos.nome]',
        'ingredientes'     => 'required|max_length[1000]|min_length[10]',
        'categoria_id' => 'required|integer'
    ];
    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório.',
            'is_unique' => 'Essa produto já Existe.',
        ],
        'categoria_id' => [
            'required' => 'O campo Categoria é Obrigatório.',
        ],
    ];
    //Eventos Callback
    protected $beforeInsert = ['criaSlug'];
    protected $beforeUpdate = ['criaSlug'];

    protected function criaSlug(array $data){

        if(isset($data['data']['nome'])){

            $data['data']['slug'] = mb_url_title($data['data']['nome'], '-',TRUE);

        }

        return $data;
    }

    public function procurar($term) {
        if($term === null){
            return [];
        }

        return $this->select('id, nome')
                     ->like('nome', $term)
                     ->withDeleted(true)
                     ->get()
                     ->getResult();
    }

    public function desfazerExclusao(int $id){
        return $this->protect(false)->where('id',$id)
                                    ->set('deletado_em',null)
                                    ->update();
    }

    public function buscaProdutosWebHome(){
        return $this->select([
            'produtos.id',
            'produtos.nome',
            'produtos.ingredientes',
            'produtos.slug',
            'produtos.imagem',
            'categorias.id AS categoria_id',
            'categorias.nome AS categoria',
            'categorias.slug As categoria_slug',
        ])
        ->selectMin('produtos_especificacoes.preco')
        ->join('categorias','categorias.id = produtos.categoria_id')
        ->join('produtos_especificacoes','produtos_especificacoes.produto_id = produtos.id')
        ->where('produtos.ativo',true)
        ->groupBy('produtos.nome')
        ->orderBy('categorias.nome','ASC')
        ->findAll();
    }
}
