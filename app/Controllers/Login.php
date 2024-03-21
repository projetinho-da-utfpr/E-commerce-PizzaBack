<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function novo()
    {
        $data = [
            'titulo' => 'Realize o Login',
        ];

        return view('Login/novo',$data);
    }

    public function criar(){

        if($this->request->getMethod() === 'post'){

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $autenticacao = service('autenticacao');

            if($autenticacao->login($email,$password)){
                $usuario= $autenticacao->pegaUsuarioLogado();

                if(!$usuario->is_admin){
                    return redirect()->to(site_url('/'));
                }
                
                return redirect()->to(\site_url('admin/home'))->with('sucesso',"Olá $usuario->nome, que bom que esta de volta");

                
            }else{

                return redirect()->back()->with('atencao','Não encontramos suas credenciais de acesso');
            }

        }else{
            return redirect()->back();
        }
    }

    public function logout(){
        service('autenticacao')->logout();
        return redirect()->to(site_url('login/mostraMensagemLogout'));
    }

    public function mostraMensagemLogout(){
        return redirect()->to(site_url("login/novo"))->with('info','Esperamos você novamente!');
    }
}
