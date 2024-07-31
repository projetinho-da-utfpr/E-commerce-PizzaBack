<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PedidoModel;
use App\Entities\Pedido;

class Pedidos extends BaseController
{
    private $pedidoModel;

    public function __construct(){
        $this->pedidoModel = new PedidoModel();
    }
    public function index()
    {

        $data =[
            'titulo' => 'Listando os Pedidos',
            'pedidos' => $this->pedidoModel->withDeleted(true)->paginate(10),
            'pager' => $this->pedidoModel->pager,

        ];

       return view('Admin/Pedidos/index',$data);

    }

    public function criar(){

        $pedido = new Pedido();

        $data= [

            'titulo' => "$pedido->nome",
            'pedido' => $pedido,
        ];
        return view('Admin/Pedidos/criar',$data);
    }

    public function cadastrar (){

        if($this->request->getPost()){

            $pedido = new Pedido($this->request->getPost());


            if($this->pedidoModel->save($pedido)){

                return redirect()->to(site_url("admin/pedidos/show/".$this->pedidoModel->getInsertID()))
                    ->with('sucesso',"pedido $pedido->nome cadastrado com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->pedidoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }

        }else{
            /* Não e post */
            return redirect()->back();
        }

    }

    public function editar($id=null){

        $pedido = $this->buscaPedidoOu404($id);

        if($pedido->deletado_em != null){
            return redirect()->back()->with('info',"O pedido $pedido->nome encontra-se excluído. Portanto, não é possível editá-lo.");
        }
       
        $data= [

            'titulo' => "$pedido->nome",
            'pedido' => $pedido,
        ];
        return view('Admin/Pedidos/editar',$data);
    }
    public function atualizar ($id = null){

        if($this->request->getMethod() === 'post'){

            $pedido=$this->buscaPedidoOu404($id);

            if($pedido->deletado_em != null){
                return redirect()->back()->with('info',"O pedido $pedido->nome encontra-se excluído. Portanto, não é possível editá-lo.");
            }
            

            $pedido->fill($this->request->getPost());

            if(!$pedido->hasChanged()){

                return redirect()->back()->with('info','Não há dados para atualizar');
            }

            if($this->pedidoModel->save($pedido)){

                return redirect()->to(site_url("admin/pedidos/show/$pedido->id"))
                    ->with('sucesso',"pedido $pedido->nome atualizada com Sucesso.");
            }else{

                return redirect()->back()
                    ->with('errors_model', $this->pedidoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
            }

        }else{
            /* Não e post */
            return redirect()->back();
        }

    }

    public function show($id=null){

        $pedido = $this->buscapedidoOu404($id);

        $data= [

            'titulo' => "$pedido->nome",
            'pedido' => $pedido,
        ];
        return view('Admin/Pedidos/show',$data);
    }
    public function procurar(){

        if(!$this->request->isAJAX()){
            exit('Pagina não encontrada');
        }

        $pedidos =  $this->pedidoModel->procurar($this->request->getGet('term'));

        $retorno = [];

        foreach($pedidos as $pedido){
            $data['id'] = $pedido->id;
            $data['value'] = $pedido->nome;
            
            $retorno[] = $data;
        }

        return $this->response->setJSON($retorno);
        
    }
    
    public function excluir($id=null){

        $pedido = $this->buscaPedidoOu404($id);

        if($pedido->deletado_em != null){
            return redirect()->back()->with('info',"A pedido $pedido->nome encontra-se ja excluído.");
        }

        if ($this->request->getMethod() === 'post') {

            $this->pedidoModel->delete($id);
            return redirect()->to(site_url('admin/pedidos'))->with('sucesso',"O pedido $pedido->nome excluido com Sucesso!");
        }
       
        $data= [

            'titulo' => "Excluindo o pedido $pedido->nome",
            'pedido' => $pedido,
        ];
        return view('Admin/Pedidos/excluir',$data);
    }

    public function desfazerexclusao($id=null){

        $pedido = $this->buscaPedidoOu404($id);
        
        if($pedido->deletado_em == null){
            return redirect()->back()->with('info','Apenas pedidos excluidos podem ser recuperada.');
        }

        if($this->pedidoModel->desfazerExclusao($id)){
            return redirect()->back()->with('sucesso','Exclusão desfeita com Sucesso!');
        }else{
            return redirect()->back()
                    ->with('errors_model', $this->pedidoModel->errors())
                    ->with('atencao','Por favor verifique os erros abaixo')
                    ->withInput();
        }

    }
    private function buscaPedidoOu404(int $id=null){
        if(!$id || !$pedido=$this->pedidoModel->withDeleted(true)->where('id',$id)->first()){
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o pedido $id");
        }
        return $pedido;
    }
}
