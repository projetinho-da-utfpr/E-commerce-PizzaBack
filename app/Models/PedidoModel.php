<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table            = 'pedidos';
    protected $returnType       = 'App\Entities\Pedido';
    protected $useSoftDeletes   = true;
    protected $allowedFields = ['produtos', 'cliente_id', 'endereco','medida_id', 'customizavel','status', 'total','quantidade','sabores','extras'];

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
    
        return $this->select('pedidos.total, pedidos.quantidade, pedidos.status, 
                              pedidos.endereco, medidas.nome as medida, 
                              GROUP_CONCAT(DISTINCT produtos.nome SEPARATOR ", ") as produtos, 
                              GROUP_CONCAT(DISTINCT extras.nome SEPARATOR ", ") as extras, 
                              GROUP_CONCAT(DISTINCT sabores.nome SEPARATOR ", ") as sabores')
                    ->join('produtos AS produtos', 'FIND_IN_SET(produtos.id, pedidos.produtos)', 'left')
                    ->join('produtos AS sabores', 'FIND_IN_SET(sabores.id, pedidos.sabores) AND sabores.categoria_id = produtos.categoria_id', 'left')
                    ->join('extras AS extras', 'FIND_IN_SET(extras.id, pedidos.extras)', 'left')
                    ->join('medidas AS medidas', 'medidas.id = pedidos.medida_id', 'left')
                    ->where('pedidos.cliente_id', $clienteId)
                    ->groupBy('pedidos.id')
                    ->orderBy('pedidos.criado_em', 'DESC') // Ordena pelos pedidos mais recentes
                    ->findAll(5); // Limita o resultado aos últimos 5 pedidos
    }
    
    

}
