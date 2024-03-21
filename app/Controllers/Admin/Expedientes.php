<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;


class Expedientes extends BaseController
{
    private $expedienteModel;

    public function __construct(){
        $this->expedienteModel = new \App\Models\ExpedienteModel();
    }
    public function Expedientes()
    {
        if($this->request->getMethod() === 'post'){
            
            $postExpedientes = $this->request->getPost();

            $arrayExpedientes = [];
            for($cont = 0;$cont<count($postExpedientes['dia_descricao']);$cont++){
                array_push($arrayExpedientes,[
                    'dia_descricao' => $postExpedientes['dia_descricao'][$cont],
                    'abertura' => $postExpedientes['abertura'][$cont],
                    'fechamento' => $postExpedientes['fechamento'][$cont],
                    'situacao' => $postExpedientes['situacao'][$cont],
                ]);
            }
            $this->expedienteModel->updateBatch($arrayExpedientes,'dia_descricao');
            return redirect()->back()->with('sucesso','Expedientes atualizados com sucesso');
        }
        $data = [
            'titulo' => 'Expediente',
            'expedientes' => $this->expedienteModel->findall(),
        ];
        return view('Admin/Expedientes/expedientes',$data);
    }
}
