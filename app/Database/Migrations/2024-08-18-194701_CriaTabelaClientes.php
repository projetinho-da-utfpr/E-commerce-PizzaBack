<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaClientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'=>'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' =>[
                'type' => 'VARCHAR',
                'constraint' => '128',
            ],
            'email' =>[
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'cpf' =>[
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'unique' => true,
            ],
            'telefone' =>[
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'endereco' =>[
                'type' => 'VARCHAR',
                'constraint' => '240',
            ],
            'ativo' =>[
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => false,
            ],
            'password_hash' =>[
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'ativação_hash' =>[
                'type' => 'VARCHAR',
                'constraint' => '64',
                'null' => true,
                'unique' => true,
            ],
            'reset_hash' =>[
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'unique' => true,
            ],
            'reset_expira_em' =>[
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'criado_em' =>[
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'atualizado_em' =>[
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'deletado_em' =>[
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id')->addUniqueKey('email');
        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
