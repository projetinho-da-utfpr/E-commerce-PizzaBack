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

    public function buscaProdutosWebHome() {
        // Executa a consulta para buscar os dados
        $resultados = $this->select([
                'produtos.id',
                'produtos.nome',
                'produtos.ingredientes',
                'produtos.slug',
                'produtos.imagem',
                'categorias.id AS categoria_id',
                'categorias.nome AS categoria',
                'categorias.slug AS categoria_slug',
                'medidas.nome AS medida', // Supondo que "nome" seja a coluna para o nome da medida
                'produtos_especificacoes.preco',
            ])
            ->join('categorias', 'categorias.id = produtos.categoria_id')
            ->join('produtos_especificacoes', 'produtos_especificacoes.produto_id = produtos.id')
            ->join('medidas', 'medidas.id = produtos_especificacoes.medida_id') // Supondo que "medida_id" seja a coluna de referência
            ->where('produtos.ativo', true)
            ->orderBy('categorias.nome', 'ASC')
            ->orderBy('produtos.id', 'ASC')  // Ordena por produto para facilitar a visualização
            ->findAll();
    
        // Processa os resultados para agrupar as medidas e preços por produto
        $produtosAgrupados = [];
        
        foreach ($resultados as $produto) {
            $produtoId = $produto->id;
            
            // Se o produto ainda não foi adicionado ao array agrupado, inicializa com as informações básicas
            if (!isset($produtosAgrupados[$produtoId])) {
                $produtosAgrupados[$produtoId] = [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'ingredientes' => $produto->ingredientes,
                    'slug' => $produto->slug,
                    'imagem' => $produto->imagem,
                    'categoria_id' => $produto->categoria_id,
                    'categoria' => $produto->categoria,
                    'categoria_slug' => $produto->categoria_slug,
                    'especificacoes' => [] // Inicializa o array para as medidas e preços
                ];
            }
            
            // Adiciona a medida e o preço ao array de especificações do produto
            $produtosAgrupados[$produtoId]['especificacoes'][] = [
                'medida' => $produto->medida,
                'preco' => $produto->preco
            ];
        }
    
        // Retorna os produtos agrupados
        return array_values($produtosAgrupados); // Retorna como um array numericamente indexado
    }

    public function buscaProdutoPorId($id) {
        // Executa a consulta para buscar os dados do produto específico
        $resultados = $this->select([
                'produtos.id',
                'produtos.nome',
                'produtos.ingredientes',
                'produtos.slug',
                'produtos.imagem',
                'categorias.id AS categoria_id',
                'categorias.nome AS categoria',
                'categorias.slug AS categoria_slug',
                'medidas.nome AS medida', // Supondo que "nome" seja a coluna para o nome da medida
                'produtos_especificacoes.preco',
            ])
            ->join('categorias', 'categorias.id = produtos.categoria_id')
            ->join('produtos_especificacoes', 'produtos_especificacoes.produto_id = produtos.id')
            ->join('medidas', 'medidas.id = produtos_especificacoes.medida_id') // Supondo que "medida_id" seja a coluna de referência
            ->where('produtos.ativo', true)
            ->where('produtos.id', $id) // Filtra pelo ID do produto passado como parâmetro
            ->orderBy('categorias.nome', 'ASC')
            ->orderBy('produtos.id', 'ASC')
            ->findAll();
    
        // Processa os resultados para agrupar as medidas e preços por produto
        $produtoDetalhes = null;
        
        if (!empty($resultados)) {
            // Inicializa o array com as informações básicas do produto
            $produtoDetalhes = [
                'id' => $resultados[0]->id,
                'nome' => $resultados[0]->nome,
                'ingredientes' => $resultados[0]->ingredientes,
                'slug' => $resultados[0]->slug,
                'imagem' => $resultados[0]->imagem,
                'categoria_id' => $resultados[0]->categoria_id,
                'categoria' => $resultados[0]->categoria,
                'categoria_slug' => $resultados[0]->categoria_slug,
                'especificacoes' => [] // Inicializa o array para as medidas e preços
            ];
    
            // Adiciona as medidas e os preços ao array de especificações do produto
            foreach ($resultados as $produto) {
                $produtoDetalhes['especificacoes'][] = [
                    'medida' => $produto->medida,
                    'preco' => $produto->preco
                ];
            }
        }
    
        // Retorna os detalhes do produto
        return $produtoDetalhes;
    }
    
    
    
    
}
