<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Medidas extends ResourceController
{
    private $medidasModel;
    private $especificacaoModel;
    public function __construct(){
        $this->medidasModel = new \App\Models\MedidaModel();
        $this->especificacaoModel = new \App\Models\ProdutoEspecificacaoModel();
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
}
