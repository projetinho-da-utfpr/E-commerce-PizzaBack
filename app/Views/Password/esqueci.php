<?php echo $this->extend('Admin/layout/principal_autenticacao'); ?>


<?php echo $this->section('titulo'); ?> <?php echo $titulo ?> <?php echo $this->endSection(); ?>




<?php echo $this->section('estilos'); ?>

<!-- Aqui enviamos para o template principal os estilos -->
<?php echo $this->endSection(); ?>





<?php echo $this->section('conteudo'); ?>

<div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-5 mx-auto">
            <div class="auth-form-light text-left py-4 px-4 px-sm-5">

            <?php if(session()->has('sucesso')): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>Perfeito!</strong> <?php echo session('sucesso'); ?>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                  </button>
                </div>

            <?php endif; ?>

            <?php if(session()->has('info')): ?>

              <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Informação!</strong> <?php echo session('info'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

            <?php endif; ?>

            <?php if(session()->has('atencao')): ?>

              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>Informação!</strong> <?php echo session('atencao'); ?>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>

            <?php endif; ?>

          <!-- Captura os erros de CSRF - Ação não Permitida -->
            <?php if(session()->has('error')): ?>

              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Erro!</strong> <?php echo session('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

            <?php endif; ?>


              <div class="brand-logo mb-1">
                <img src="<?php echo site_url('admin/') ?>images/logo_itaipu.png" alt="logo" width="200" height="100">
              </div>
              <h4>Recuperação de Senha!</h4>
              <h6 class="font-weight-light mb-3"><?php echo $titulo; ?></h6>
              <?php  echo form_open('password/processaesqueci'); ?>


                <div class="form-group">
                  <input type="email" name="email" value="<?php echo old('email'); ?>" class="form-control form-control-lg" id="exampleInputEmail1" placeholder="Digite o seu E-mail">
                </div>
              
                  <div class="mt-3 my-2 d-flex justify-content-between align-items-center">
                    <input id="btn-reset-senha" type="submit" class="btn btn-block btn-dark btn-primary btn-lg font-weight-medium auth-form-btn mt-1" value="Enviar">
                    <button href="<?php  echo site_url('login'); ?>"type="submit" class="btn btn-block btn-dark btn-primary btn-lg font-weight-medium auth-form-btn ml-4 mt-1">Voltar</button>
                  </div>
                </div>
                
            
              <?php echo form_close();?>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>

<?php echo $this->endSection(); ?>





<?php echo $this->section('scripts'); ?>

<script>
  $("form").submit(function(){
    $(this).find(":submit").attr('disabled','disabled');

    $("btn-reset-senha").val("Enviando e-mail de recuperação.")

  })
</script>

<?php echo $this->endSection(); ?>
