<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

use App\Libraries\Token;

class Usuario extends Entity
{

    protected $dates   = [
        'criado_em',
        'atualizado_em',
        'deletado_em'
    ];

    public function verificaPassword(string $password){
        return password_verify($password,$this->password_hash);
    }

    public function iniciaPasswordReset(){

        /*Instancio novo objeto da classe Token */
        $token = new Token();

        /** @descricao: atribuimos ao objeto Entities Usuario ($this) o atributo 'reset_token' que contera o token gerado 
         *              para que possamos acesá-lo na view 'Password/reset_email'
        */
        
        $this->reset_token = $token->getValue();

        /**@descricao: Atribuimos ao objeto Usuario ($this) o atributo 'reset_hash' que conterá o hash do token */

        $this->reset_hash = $token->getHash();

        /** @descricao: Atribuimos ao objeto Entities Usuario ($this) o atributo 'reset_expira_em' que conterá a data de expiração do Token gerado */

        $this->reset_expira_em = date('Y-m-d H:i:s',time() + 7200); //Expira em 2 H a partir da data e hora atuais
    }

    public function completaPasswordReset(){
        $this->reset_hash=null;
        $this->reset_expira_em = null;
    }
}
