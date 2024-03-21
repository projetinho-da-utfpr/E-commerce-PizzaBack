<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Entities\FormaPagamento;
class FormasPagamento extends BaseController
{
    private $formaPagamentoModel;

    public function __construct(){
        $this->formaPagamentoModel = new \App\Models\FormaPagamentoModel();
    }
    public function index()
    {
        $data = [
            'titulo' => 'Forma de Pagamento',
            'formas' => $this->formaPagamentoModel->withDeleted(true)->paginate(10),
            'pager' => $this->formaPagamentoModel->pager,
        ];

        return view('Admin/FormasPagamento/index',$data);
    }

    public function procurar(){

        if(!$this->request->isAJAX()){
            exit('Pagina não encontrada');
        }

        $formas =  $this->formaPagamentoModel->procurar($this->request->getGet('term'));

        $retorno = [];

        foreach($formas as $forma){
            $data['id'] = $forma->id;
            $data['value'] = $forma->nome;
            
            $retorno[] = $data;
        }

        return $this->response->setJSON($retorno);
        
    }

    public function criar(){

        $formaPagamento = new FormaPagamento();
       
        $data= [

            'titulo' => "$formaPagamento->nome",
            'forma' => $formaPagamento,
        ];
        return view('Admin/FormasPagamento/criar',$data);
    }

    public function cadastrar(){
        if($this->request->getMethod() === 'post'){

            $formaPagamento = new FormaPagamento($this->request->getPost());

            if($this->formaPagamentoModel->save($formaPagamento)){
                return redirect()->to(site_url("admin/formas/show/".$this->formaPagamentoModel->getInsertID()))
                    ->with('sucesso',"A forma de pagamento $formaPagamento->nome foi cadastrada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->formaPagamentoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }
        }else{
            return redirect()->back();
        }
    }

    public function show($id=null){

        $formaPagamento = $this->buscaformaPagamentoOu404($id);

        $data= [

            'titulo' => "$formaPagamento->nome",
            'forma' => $formaPagamento,
        ];
        return view('Admin/FormasPagamento/show',$data);
    }
    public function editar($id=null){

        $formaPagamento = $this->buscaformaPagamentoOu404($id);

        if($formaPagamento->deletado_em != null){
            return redirect()->back()->with('info',"A forma de pagamento $formaPagamento->nome encontra-se excluída. Portanto, não é possível editá-la.");
        }
       
        $data= [

            'titulo' => "Editando a forma de pagamento $formaPagamento->nome",
            'forma' => $formaPagamento,
        ];
        return view('Admin/FormasPagamento/editar',$data);
    }

    public function atualizar($id = null){
        if($this->request->getMethod() === 'post'){

            $formaPagamento = $this->buscaformaPagamentoOu404($id);
            $formaPagamento->fill($this->request->getPost());

            if(!$formaPagamento->hasChanged()){
                return redirect()->back()->with('info','Não há dados para atualizar');
            }

            if($this->formaPagamentoModel->save($formaPagamento)){
                return redirect()->to(site_url("admin/formas/show/$formaPagamento->id"))
                    ->with('sucesso',"A forma de pagamento $formaPagamento->nome foi atualizada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->formaPagamentoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }
        }else{
            return redirect()->back();
        }
    }

    public function excluir($id=null){

        $formaPagamento = $this->buscaformaPagamentoOu404($id);

        if($formaPagamento->deletado_em != null){
            return redirect()->back()->with('info',"A forma de pagamento $formaPagamento->nome encontra-se ja excluído.");
        }

        if ($this->request->getMethod() === 'post') {

            $this->formaPagamentoModel->delete($id);
            return redirect()->to(site_url('admin/formas'))->with('sucesso',"A forma de pagamento $formaPagamento->nome foi excluida com Sucesso!");
        }
       
        $data= [

            'titulo' => "$formaPagamento->nome",
            'forma' => $formaPagamento,
        ];
        return view('Admin/FormasPagamento/excluir',$data);
    }

    public function desfazerexclusao($id=null){

        $formaPagamento = $this->buscaformaPagamentoOu404($id);
        
        if($formaPagamento->deletado_em == null){
            return redirect()->back()->with('info','Apenas formas de pagamento já excluidas podem ser recuperada.');
        }

        if($this->formaPagamentoModel->desfazerExclusao($id)){
            return redirect()->back()->with('sucesso','Exclusão desfeita com Sucesso!');
        }else{
            return redirect()->back()
                    ->with('errors_model', $this->formaPagamentoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
        }

    }

    private function buscaformaPagamentoOu404(int $id=null){
        if(!$id || !$forma = $this->formaPagamentoModel->withDeleted(true)->where('id',$id)->first()){
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a forma de pagamento $id");
        }
        return $forma;
    }
}
