<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Medidas extends ResourceController
{
    private $medidasModel;
    private $especificacaoModel;

    private $extrasModel;
    public function __construct(){
        $this->medidasModel = new \App\Models\MedidaModel();
        $this->especificacaoModel = new \App\Models\ProdutoEspecificacaoModel();
        $this->extrasModel = new \App\Models\ProdutoExtraModel();
    }
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
        public function index($id = null){
        // Busca as medidas ativas no banco de dados
        $medidas =$this->especificacaoModel->buscaEspecificacoesProdutoSemPaginacao($id);
        // Retorna a resposta JSON com status 200
        return $this->response->setStatusCode(200)->setJSON($medidas);

    }
    public function extrasProduto($id = null){
        $extras = $this->extrasModel->buscaExtrasDoProdutoSemPaginacao($id);
        if(!$extras){
            return $this->failNotFound('Não encontrado extras para esse produto.');
        }
        return $this->response->setStatusCode(200)->setJSON($extras);
    }
}
