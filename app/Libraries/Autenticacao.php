<?php

namespace App\Libraries;
/*
*@descrição essa biblioteca / classe cuidará da parte de autenticação
*/

class Autenticacao{

    private $usuario;

    /**
     * @param string $email
     * @param string @password
     * @return boolean
     */
    public function login(string $email,string $password){

        $usuarioModel= new \App\Models\UsuarioModel();

        $usuario =  $usuarioModel->buscaUsuarioPorEmail($email);

        // verifica se o usuario e Valido, se não for retorna false;
        if($usuario === null){
            return false;
        }

        // Verifica se a senha e Valida com a do BD, se não for retorna false;
        if(!$usuario->verificaPassword($password)){
            return false;
        }
        
        // Verifica se o usuario está ativo. Só loga usuarios ativos no sistema
        if(!$usuario->ativo){
            return false;
        }
        
        // Nesse ponto esta tudo certo e podemos logar o usuario na aplicação invocando o metodo abaixo
        $this->logaUsuario($usuario);

        return true;

    }


    public function logout(){

        session()->destroy();
    }

    public function pegaUsuarioLogado(){


        // Não esquecer de Compartilhar a instancia com services
        if($this->usuario === null){
            $this->usuario = $this->pegaUsuarioDaSessao();
        }

        //Retorno do Usuario que foi definido no inicio da Classe
        return $this->usuario;
       
    }

    /**
     * @descripion O metodo verifica se o Usuario esta logado.
     * @return boolean
     */
    public function estaLogado(){
        return $this->pegaUsuarioLogado() != null;
    }


    


    private function pegaUsuarioDaSessao(){
        if(!session()->has('usuario_id')){
            return null;
        }

        // instanciamos o Model Usuario
        $usuarioModel = new \App\Models\UsuarioModel();
        //Recupero o Usuario de acordo com a chave da Sessao 'usuario_id'
        $usuario = $usuarioModel->find(session()->get('usuario_id'));


        // So retorno se o Usuario estiver logado e Ativo
        if($usuario && $usuario->ativo){
            return $usuario;
        }
    }

    private function logaUsuario(object $usuario){

        //Matheus

        $session = session();
        $session->regenerate();
        $session->set('usuario_id',$usuario->id);
    }

}