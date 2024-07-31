<?php echo $this->extend('Admin/layout/principal'); ?>


<?php echo $this->section('titulo'); ?>
<?php echo $titulo ?>
<?php echo $this->endSection(); ?>




<?php echo $this->section('estilos'); ?>


<?php echo $this->endSection(); ?>





<?php echo $this->section('conteudo'); ?>
<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-header bg-secondary pb-0 pt-4">
                <h4 class="card-title text-white"><?php echo esc($titulo); ?></h4>
            </div>

            <div class="card-body">
                
                <p class="card-text">
                    <span class= "font-weight-bold"> Pedido:</span>
                    <?php echo esc($pedido->nome); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> Cliente:</span>
                    <?php echo esc($pedido->cliente); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> endereco:</span>
                    <?php echo esc($pedido->endereco); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> Preço:</span>
                    <?php echo esc($pedido->preco); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> ingredientes:</span>
                    <?php echo esc($pedido->ingredientes); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> Ativo:</span>
                    <?php echo ($pedido->ativo ? 'Sim' : 'Não'); ?>
                </p>
                <p class="card-text">
                    <span class= "font-weight-bold"> Criado:</span>
                    <?php echo $pedido->criado_em->humanize(); ?>
                </p>

                <?php if($pedido->deletado_em == null): ?>
                    <p class="card-text">
                        <span class= "font-weight-bold"> Atualizado:</span>
                        <?php echo $pedido->atualizado_em->humanize(); ?>
                    </p>
                <?php else: ?>

                    <p class="card-text">
                        <span class= "font-weight-bold text-danger"> Excluido:</span>
                        <?php echo $pedido->deletado_em->humanize(); ?>
                    </p>

                <?php endif; ?>

                <div class="mt-4">


                <?php if($pedido->deletado_em == null): ?>
                    <a href= "<?php echo site_url("admin/pedidos/editar/$pedido->id"); ?>"class="btn btn-dark btn-sm btn-icon-text mr-2 btn-sm">
                        <i class="mdi mdi-file-check btn-icon-prepend"></i>
                        Editar
                    </a>

                    <a href= "<?php echo site_url("admin/pedidos/excluir/$pedido->id"); ?>"class="btn btn-danger btn-sm mr-2 btn-sm">
                        <i class="mdi mdi-delete btn-icon-prepend"></i>
                        Excluir
                    </a>

                    <a href= "<?php echo site_url("admin/pedidos"); ?>"class="btn btn-light text-dark btn-sm">
                        <i class="mdi mdi mdi-keyboard-return btn-icon-prepend"></i>
                        Voltar
                    </a>
                <?php else: ?>

                    <a  title="Desfazer Exclusão" href= "<?php echo site_url("admin/pedidos/desfazerexclusao/$pedido->id"); ?>"class="btn btn-dark btn-sm">
                        <i class="mdi mdi-undo btn-icon-prepend"></i>
                        Desfazer
                    </a>

                    <a href= "<?php echo site_url("admin/pedidos"); ?>"class="btn btn-light text-dark btn-sm">
                        <i class="mdi mdi mdi-keyboard-return btn-icon-prepend"></i>
                        Voltar
                    </a>

                <?php endif; ?>



                </div>

            </div>
        </div>
    </div>
    
</div>


<?php echo $this->endSection(); ?>





<?php echo $this->section('scripts'); ?>


<?php echo $this->endSection(); ?>