<?php

namespace App\Models;

use App\Entities\Cliente;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $returnType       = 'App\Entities\Cliente';
    protected $allowedFields    = ['nome','email','endereco','cpf','telefone','password','reset_hash','reset_expira_em'];


    //Datas
    protected $useTimestamps    = true;
    protected $createdField     = 'criado_em'; // Nome da coluna no banco de dados
    protected $updatedField     = 'atualizado_em'; // Nome da coluna no banco de dados
    protected $dateFormat       = 'datetime';//para uso com o $useSoftDeletes
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deletado_em'; // Nome da coluna no banco de dados

    //Validações
    protected $validationRules = [
        'nome'     => 'required|max_length[120]|min_length[4]',
        'email'        => 'required|valid_email|is_unique[clientes.email]',
        'cpf'        => 'required|exact_length[14]|validaCpf|is_unique[clientes.cpf]',
        'endereco' => 'required|min_length[4]',
        'telefone'        => 'required',
        'password'     => 'required|min_length[6]',
        'password_confirmation' => 'required_with[password]|matches[password]',
    ];
    protected $validationMessages = [
        'email' => [
            'required' => 'O campo E-mail é Obrigatório.',
            'is_unique' => 'Desculpe. Esse email já existe.',
        ],
        'cpf' => [
            'required' => 'O campo CPF é Obrigatório.',
            'is_unique' => 'Desculpe. Esse CPF já existe.',
        ],
        'nome' => [
            'required' => 'O campo Nome é Obrigatório.',
        ],
    ];
    //Eventos Callback
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
{
    if (isset($data['data']['password'])) {
        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);
        unset($data['data']['password_confirmation']);
    }
    if (!isset($data['data']['ativo'])) {
        $data['data']['ativo'] = 1; // Definindo como ativo
    }
    return $data;
}


    public function procurar($term) {
        if($term === null){
            return [];
        }

        return $this->select('id, nome')
                     ->like('nome', $term)
                     ->withDeleted(true)
                     ->get()
                     ->getResult();
    }

    public function desabilitaValidacaoSenha(){

        unset($this->validationRules['password']);
        unset($this->validationRules['password_confirmation']);
    }
    

    public function desfazerExclusao(int $id){
        return $this->protect(false)->where('id',$id)
                                    ->set('deletado_em',null)
                                    ->update();
    }

    /**
     * @uso Classe Autenticacao
     * @param string $email
     * @return objeto $usuarios
     */
    public function buscaUsuarioPorEmail($email){
        return $this->where('email',$email)->first();
    }

    public function buscaInformacoesCliente($id) {
        return $this->select('nome, email, telefone,cpf,endereco') // Coloque aqui as colunas que deseja selecionar
                    ->where('id', $id)
                    ->first();
    }
    public function buscaUsuarioParaResetarSenha(string $token){

        $token = new Token($token);

        $tokenHash = $token->getHash();


        $usuario = $this->where('reset_hash',$tokenHash)->first();

        if($usuario != null){

            /**
             * Verificamos se o token não está expirado de acordo com a data e hora atuais
             */
            if($usuario->reset_expira_em < date('Y-m-d H:i:s')){

                /**
                 * Token está expirado, então setamos o $usuario = null;
                 */
                $usuario=null;
            }

            return $usuario;
        }

    }
}
