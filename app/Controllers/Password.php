<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Usuario;

class Password extends BaseController
{

    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
    }

    public function esqueci(){

        $data = [
            'titulo' => 'Esqueci a minha senha',
        ];

        return view('Password/esqueci',$data);
    }

    public function processaEsqueci(){

        if($this->request->getMethod() === 'post'){

            $usuario = $this->usuarioModel->buscaUsuarioPorEmail($this->request->getPost('email'));

            if($usuario === null || !$usuario->ativo){
                return redirect()->to(\site_url('password/esqueci'))->with('atencao','Usuario não encontrado!')->withInput();
            }


            $usuario->iniciaPasswordReset();

            $this->enviaEmailRedefinicaoSenha($usuario);
            
            return redirect()->to(site_url('login'))->with('sucesso','Email de redefinição de senha enviado para sua caixa de entrada');


        }else{
            /* Não e Post */
            return redirect()->back();
        }
    }

    public function reset($token = null){

        if($token === null){
            return redirect()->to(site_url('password/esqueci'))->with('atencao','Link invalido ou expirado');
        }

        $usuario = $this->usuarioModel->buscaUsuarioParaResetarSenha($token);

        if($usuario != null){

            $data = [
                'titulo' => 'Redefinir Senha',
                'token' => $token,
            ];

            return view('Password/reset',$data);
        }else{

            return redirect()->to(site_url('password/esqueci'))->with('atencao','Link inválido ou expirado');
        }
    }

    public function processaReset($token = null){

        if($token === null){
            return redirect()->to(site_url('password/esqueci'))->with('atencao','Link invalido ou expirado');
        }

        $usuario = $this->usuarioModel->buscaUsuarioParaResetarSenha($token);

        if($usuario != null){

            $usuario->fill($this->request->getPost());

            if($this->usuarioModel->save($usuario)){

                /**
                 * Set das colunas 'reset_hash' e 'reset_expira_em' como null ao invocar o método abaixo, que foi definido em Entidade Usuario.
                 */
                $usuario->completaPasswordReset();

                $this->usuarioModel->save($usuario);

                return redirect()->to(site_url("login"))->with('sucesso','Sua senha foi atualizada!');
            }else{
                return redirect()->to(site_url("password/reset/$token"))
                ->with('errors_model', $this->usuarioModel->errors())
                ->with('atencao','Por favor verifique os erros abaixo')
                ->withInput();
            }
            
        }else{
            
            return redirect()->to(site_url('password/esqueci'))->with('atencao','Link inválido ou expirado');
        }
    }

    private function enviaEmailRedefinicaoSenha (object $usuario){

        $email = service('email');

        $email->setFrom('no-replay@itaipu.com.br', 'Itaipu Engenharia');
        $email->setTo($usuario->email);


        $email->setSubject('Redefinição de Senha');

        $mensagem= view('Password/reset_email',['token' => $usuario->reset_token]);
        $email->setMessage('Testing the email class.');

        $email->send();
    }
}
