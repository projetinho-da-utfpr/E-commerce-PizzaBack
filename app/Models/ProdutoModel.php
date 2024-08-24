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
                'produtos_especificacoes.customizavel' // Seleciona se o produto é customizável
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
            
            // Adiciona a medida, o preço e a informação de customização ao array de especificações do produto
            $produtosAgrupados[$produtoId]['especificacoes'][] = [
                'medida' => $produto->medida,
                'preco' => $produto->preco,
                'customizavel' => $produto->customizavel
            ];
        }
    
        // Retorna os produtos agrupados
        return array_values($produtosAgrupados); // Retorna como um array numericamente indexado
    }
    

    public function buscaProdutoPorId($id)
    {
        // Executa a consulta para buscar os dados do produto específico
        $resultados = $this->select([
                'p.id',
                'p.nome',
                'p.ingredientes',
                'c.id AS categoria_id',
                'c.nome AS categoria',
                'm.nome AS medida',
                'pe.preco',
                'pe.customizavel'
            ])
            ->from('produtos AS p')
            ->join('categorias AS c', 'c.id = p.categoria_id')
            ->join('produtos_especificacoes AS pe', 'pe.produto_id = p.id')
            ->join('medidas AS m', 'm.id = pe.medida_id')
            ->where('p.ativo', true)
            ->where('p.id', $id)
            ->groupBy(['p.id', 'pe.id']) // Agrupa pelos IDs únicos
            ->orderBy('c.nome', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->findAll();
    
        // Processa os resultados para agrupar as medidas e preços por produto
        $produtoDetalhes = null;
    
        if (!empty($resultados)) {
            $produtoDetalhes = [
                'id' => $resultados[0]->id,
                'nome' => $resultados[0]->nome,
                'ingredientes' => $resultados[0]->ingredientes,
                'categoria_id' => $resultados[0]->categoria_id,
                'categoria' => $resultados[0]->categoria,
                'especificacoes' => [],
                'extras' => []
            ];
    
            foreach ($resultados as $produto) {
                $produtoDetalhes['especificacoes'][] = [
                    'medida' => $produto->medida,
                    'preco' => $produto->preco,
                    'customizavel' => $produto->customizavel
                ];
            }
    
            // Busca os extras disponíveis para o produto
            $extras = $this->select('e.nome AS extra_nome, e.preco AS extra_preco')
                ->from('produtos_extras AS pe')
                ->join('extras AS e', 'e.id = pe.extra_id')
                ->where('pe.produto_id', $id)
                ->groupBy(['e.id']) // Agrupa pelos IDs dos extras
                ->findAll();
    
            foreach ($extras as $extra) {
                $produtoDetalhes['extras'][] = [
                    'nome' => $extra->extra_nome,
                    'preco' => $extra->extra_preco
                ];
            }
        }
    
        return $produtoDetalhes;
    }
 
    
}
