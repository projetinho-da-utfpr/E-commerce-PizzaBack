<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Categoria;

class Categorias extends BaseController
{
    private $categoriaModel;

    public function __construct(){
        $this->categoriaModel= new \App\Models\CategoriaModel();
    }
    public function index()
    {
        
        $data = [
            'titulo'=> 'Listando as Categorias',
            'categorias' => $this->categoriaModel->withDeleted(true)->paginate(10),
            'pager' => $this->categoriaModel->pager
        ];

        return view('Admin/Categorias/index',$data);
    }

    public function criar(){

        $categoria= new Categoria();
       
        $data= [

            'titulo' => "Criando novo categoria",
            'categoria' => $categoria,
        ];
        return view('Admin/categorias/criar',$data);
    }

    public function cadastrar (){

        if($this->request->getPost()){

            $categoria = new categoria($this->request->getPost());


            if($this->categoriaModel->save($categoria)){

                return redirect()->to(site_url("admin/categorias/show/".$this->categoriaModel->getInsertID()))
                    ->with('sucesso',"categoria $categoria->nome cadastrada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->categoriaModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }

        }else{
            /* Não e post */
            return redirect()->back();
        }

    }

    public function procurar(){

        if(!$this->request->isAJAX()){
            exit('Pagina não encontrada');
        }

        $categorias =  $this->categoriaModel->procurar($this->request->getGet('term'));

        $retorno = [];

        foreach($categorias as $categoria){
            $data['id'] = $categoria->id;
            $data['value'] = $categoria->nome;
            
            $retorno[] = $data;
        }

        return $this->response->setJSON($retorno);
        
    }

    public function excluir($id=null){

        $categoria = $this->buscaCategoriaOu404($id);

        if($categoria->deletado_em != null){
            return redirect()->back()->with('info',"A categoria $categoria->nome encontra-se ja excluída.");
        }

        if ($this->request->getMethod() === 'post') {

            $this->categoriaModel->delete($id);
            return redirect()->to(site_url('admin/categorias'))->with('sucesso',"A categoria $categoria->nome excluido com Sucesso!");
        }
       
        $data= [

            'titulo' => "Excluindo a categoria $categoria->nome",
            'categoria' => $categoria,
        ];
        return view('Admin/Categorias/excluir',$data);
    }

    public function desfazerexclusao($id=null){

        $categoria = $this->buscaCategoriaOu404($id);
        
        if($categoria->deletado_em == null){
            return redirect()->back()->with('info','Apenas Categorias excluidas podem ser recuperada.');
        }

        if($this->categoriaModel->desfazerExclusao($id)){
            return redirect()->back()->with('sucesso','Exclusão desfeita com Sucesso!');
        }else{
            return redirect()->back()
                    ->with('errors_model', $this->categoriaModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
        }

    }

    public function show($id=null){

        $categoria = $this->buscaCategoriaOu404($id);
       
        $data= [

            'titulo' => "$categoria->nome",
            'categoria' => $categoria,
        ];
        return view('Admin/Categorias/show',$data);
    }
    
    private function buscaCategoriaOu404(int $id=null){
        if(!$id || !$categoria = $this->categoriaModel->withDeleted(true)->where('id',$id)->first()){
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o categoria $id");
        }
        return $categoria;
    }

    public function editar($id=null){

        $categoria = $this->buscaCategoriaOu404($id);

        if($categoria->deletado_em != null){
            return redirect()->back()->with('info',"A categoria $categoria->nome encontra-se excluída. Portanto, não é possível editá-la.");
        }
       
        $data= [

            'titulo' => "Editando a categoria $categoria->nome",
            'categoria' => $categoria,
        ];
        return view('Admin/Categorias/editar',$data);
    }

    public function atualizar ($id = null){

        if($this->request->getMethod() === 'post'){

            $categoria=$this->buscaCategoriaOu404($id);

            if($categoria->deletado_em != null){
                return redirect()->back()->with('info',"A categoria $categoria->nome encontra-se excluída. Portanto, não é possível editá-la.");
            }
            

            $categoria->fill($this->request->getPost());

            if(!$categoria->hasChanged()){

                return redirect()->back()->with('info','Não há dados para atualizar');
            }

            if($this->categoriaModel->save($categoria)){

                return redirect()->to(site_url("admin/categorias/show/$categoria->id"))
                    ->with('sucesso',"categoria $categoria->nome atualizada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->categoriaModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }

        }else{
            /* Não e post */
            return redirect()->back();
        }

    }
}
