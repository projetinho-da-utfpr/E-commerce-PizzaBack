
<div class="form-row">

    <div class="form-group col-md-5">
        <label for="nome">Pedido</label>
        <input type="text" class="form-control" name="nome" id="nome" value="<?php echo old('nome', esc($pedido->nome)); ?>">
    </div>
    
    <div class="form-group col-md-5">
        <label for="cliente">Cliente</label>
        <input type="cliente" class="form-control" name="cliente" id="cliente" value="<?php echo old('cliente', esc($pedido->cliente)); ?>">
    </div>
    <div class="form-group col-md-5">
        <label for="preco">Preço</label>
        <input type="text" class="money form-control" name="preco" id="preco" value="<?php echo old('preco', esc($pedido->preco)); ?>">
    </div>
    <div class="form-group col-md-5">
        <label for="endereco">Endereço</label>
        <input type="endereco" class="form-control" name="endereco" id="endereco" value="<?php echo old('endereco', esc($pedido->endereco)); ?>">
    </div>

</div>
<div class="form-group col-md-12">
        <label for="ingredientes">Ingredientes</label>
        <textarea class="form-control" name="ingredientes" rows="3" id="ingredientes"><?php echo old('ingredientes', esc($pedido->ingredientes)); ?></textarea>
</div>


<div class="form-check form-check-flat form-check-primary mb-2">
    <label for="is_admin" class="form-check-label">


        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" <?php if(old('ativo',$pedido->ativo)): ?> checked="" <?php endif; ?>>
            Ativo
    </label>
</div>

                    
<button type="submit" class="btn btn-secondary mr-2 btn-sm">
    <i class="mdi mdi-checkbox-marked-circle btn-icon-prepend"></i>
    Salvar
</button>
