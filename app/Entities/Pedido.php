<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Pedido extends Entity
{
    protected $dates   = ['criado_em', 'atualizado_em', 'deletado_em'];
}
