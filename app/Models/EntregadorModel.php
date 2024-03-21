<?php

namespace App\Models;

use CodeIgniter\Model;

class EntregadorModel extends Model
{
    protected $table            = 'entregadores';
    protected $returnType       = 'App\Entities\Entregador';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['nome','cpf','cnh','email','telefone','imagem','ativo','veiculo','placa','endereco'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    protected $validationRules = [
        'nome'     => 'required|max_length[120]|min_length[4]',
        'email'        => 'required|valid_email|is_unique[entregadores.email]',
        'cpf'        => 'required|exact_length[14]|validaCpf|is_unique[entregadores.cpf]',
        'cnh'        => 'required|exact_length[11]|is_unique[entregadores.cnh]',
        'telefone'        => 'required|exact_length[15]|is_unique[entregadores.telefone]',
        'endereco'        => 'required|max_length[230]',
        'veiculo'        => 'required|max_length[230]',
        'placa'         => 'required|min_length[7]|max_length[8]|is_unique[entregadores.placa]',
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
        'cnh' => [
            'required' => 'O campo CPF é Obrigatório.',
            'is_unique' => 'Desculpe. Essa CNH já existe.',
        ],
        'cnh' => [
            'required' => 'O campo CPF é Obrigatório.',
            'is_unique' => 'Desculpe. Esse telefone já existe.',
        ],
        'veiculo' => [
            'required' => 'O campo veículo é Obrigatório.',
        ],
        'endereco' => [
            'required' => 'O campo endereço é Obrigatório.',
        ],
    ];

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
    public function desfazerExclusao(int $id){
        return $this->protect(false)->where('id',$id)
                                    ->set('deletado_em',null)
                                    ->update();
    }
}
