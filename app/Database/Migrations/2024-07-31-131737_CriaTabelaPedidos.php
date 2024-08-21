<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaPedidos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'endereco' =>[
                'type' => 'VARCHAR',
                'constraint' => '240',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'Pendente', // Exemplo de status: 'Pendente', 'Em andamento', 'Concluído'
                'null' => false,
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
	        'produtos' =>[
                'type' => 'VARCHAR',
                'constraint' => '128',
            ],
	        'quantidade' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
	        'customizavel' =>[
                'type' => 'TEXT',
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'atualizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'deletado_em' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        // Chave primária
        $this->forge->addPrimaryKey('id');

        // Chave estrangeira para referenciar a tabela de clientes
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE');

        // Cria a tabela
        $this->forge->createTable('pedidos');
    }

    public function down()
    {
        // Remove a tabela
        $this->forge->dropTable('pedidos');
    }
}
