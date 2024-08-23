<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table            = 'pedidos';
    protected $returnType       = 'App\Entities\Pedido';
    protected $useSoftDeletes   = true;
    protected $allowedFields = ['produtos', 'cliente_id', 'endereco', 'customizavel','status', 'total','quantidade','medida_id','sabores','extras'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation

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
    public function buscaUltimosPedidos($clienteId)
    {
        // Verifica se o ID do cliente é fornecido
        if (empty($clienteId)) {
            return [];
        }

        return $this->select('total, quantidade, produtos, customizavel, status')
                    ->where('cliente_id', $clienteId)
                    ->orderBy('criado_em', 'DESC') // Ordena pelos pedidos mais recentes
                    ->findAll(5); // Limita o resultado aos últimos 5 pedidos
    }
}
