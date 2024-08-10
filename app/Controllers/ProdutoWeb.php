<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class ProdutoWeb extends ResourceController
{
    use ResponseTrait;

    protected $produtoModel;
    protected $fomat = 'json';
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */

     public function __construct(){
        $this->produtoModel =   new \App\Models\ProdutoModel();
     }
    public function index()
    {
        $data = [ 
            'mensagem' => 'sucesso',
            'produtos' => $this->produtoModel->buscaProdutosWebHome(),
        ];
        
        return $this->respond($data,200);
    }

    public function buscaPorId($id){
        $produtoDetalhes = $this->produtoModel->buscaProdutoPorId($id);

        // Retorna os dados para a view ou para uma API
        return $this->response->setJSON($produtoDetalhes);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function imagem(string $imagem = null){
        // Caminho para a pasta onde as imagens estão armazenadas
        $caminho = WRITEPATH . 'uploads/produtos/';

        // Verificar se o arquivo existe
        if (file_exists($caminho . $imagem)) {
            // Obter o tipo de conteúdo da imagem
            $tipoConteudo = mime_content_type($caminho . $imagem);

            // Definir o tipo de conteúdo da resposta como imagem
            header('Content-Type: ' . $tipoConteudo);

            // Exibir a imagem
            readfile($caminho . $imagem);
        } else {
            // Se a imagem não for encontrada, exibir uma imagem padrão ou retornar um erro
            // Exemplo: echo file_get_contents('caminho/para/imagem_padrao.jpg');
            echo "Imagem não encontrada";
        }
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    }
