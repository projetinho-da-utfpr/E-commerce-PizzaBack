<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Extra;

class Extras extends BaseController
{

    private $extraModel;

    public function __construct()
    {
        $this->extraModel = new \App\Models\ExtraModel();
    }
    public function index()
    {
        $data = [
            'titulo' => 'Listando os extras de produtos',
            'extras' => $this->extraModel->withDeleted(true)->paginate(10),
            'pager' => $this->extraModel->pager,
        ];

        return view('Admin/Extras/index',$data);
    }

    public function criar(){

        $extra = new Extra();

        $data= [

            'titulo' => "$extra->nome",
            'extra' => $extra,
        ];
        return view('Admin/extras/criar',$data);
    }

    public function cadastrar (){

        if($this->request->getPost()){

            $extra = new extra($this->request->getPost());


            if($this->extraModel->save($extra)){

                return redirect()->to(site_url("admin/extras/show/".$this->extraModel->getInsertID()))
                    ->with('sucesso',"extra $extra->nome cadastrado com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->extraModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }

        }else{
            /* Não e post */
            return redirect()->back();
        }

    }

    public function editar($id=null){

        $extra = $this->buscaExtraOu404($id);

        if($extra->deletado_em != null){
            return redirect()->back()->with('info',"O extra $extra->nome encontra-se excluído. Portanto, não é possível editá-lo.");
        }
       
        $data= [

            'titulo' => "Editando o extra $extra->nome",
            'extra' => $extra,
        ];
        return view('Admin/Extras/editar',$data);
    }

    public function atualizar ($id = null){

        if($this->request->getMethod() === 'post'){

            $extra=$this->buscaExtraOu404($id);

            if($extra->deletado_em != null){
                return redirect()->back()->with('info',"O extra $extra->nome encontra-se excluído. Portanto, não é possível editá-lo.");
            }
            

            $extra->fill($this->request->getPost());

            if(!$extra->hasChanged()){

                return redirect()->back()->with('info','Não há dados para atualizar');
            }

            if($this->extraModel->save($extra)){

                return redirect()->to(site_url("admin/extras/show/$extra->id"))
                    ->with('sucesso',"extra $extra->nome atualizada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->extraModel->errors())
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

        $extras =  $this->extraModel->procurar($this->request->getGet('term'));

        $retorno = [];

        foreach($extras as $extra){
            $data['id'] = $extra->id;
            $data['value'] = $extra->nome;
            
            $retorno[] = $data;
        }

        return $this->response->setJSON($retorno);
        
    }
    private function buscaextraOu404(int $id=null){
        if(!$id || !$extra = $this->extraModel->withDeleted(true)->where('id',$id)->first()){
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o extra $id");
        }
        return $extra;
    }

    public function excluir($id=null){

        $extra = $this->buscaExtraOu404($id);

        if($extra->deletado_em != null){
            return redirect()->back()->with('info',"A extra $extra->nome encontra-se ja excluído.");
        }

        if ($this->request->getMethod() === 'post') {

            $this->extraModel->delete($id);
            return redirect()->to(site_url('admin/extras'))->with('sucesso',"A extra $extra->nome excluido com Sucesso!");
        }
       
        $data= [

            'titulo' => "Excluindo o extra $extra->nome",
            'extra' => $extra,
        ];
        return view('Admin/Extras/excluir',$data);
    }

    public function desfazerexclusao($id=null){

        $extra = $this->buscaExtraOu404($id);
        
        if($extra->deletado_em == null){
            return redirect()->back()->with('info','Apenas extras excluidas podem ser recuperada.');
        }

        if($this->extraModel->desfazerExclusao($id)){
            return redirect()->back()->with('sucesso','Exclusão desfeita com Sucesso!');
        }else{
            return redirect()->back()
                    ->with('errors_model', $this->extraModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
        }

    }

    public function show($id=null){

        $extra = $this->buscaExtraOu404($id);

        $data= [

            'titulo' => "$extra->nome",
            'extra' => $extra,
        ];
        return view('Admin/extras/show',$data);
    }
}
