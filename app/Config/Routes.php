<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('produtos','ProdutoWeb::index');
$routes->get('foto/mostrar/(:any)', 'ProdutoWeb::imagem/$1');
$routes->get('umProduto/(:num)', 'ProdutoWeb::buscaPorId/$1');
$routes->post('pedidos','Pedidos::index');
$routes->post('clienteNovo','Clientes::cadastrar');
$routes->get('buscarCliente/(:num)','Clientes::buscarCliente/$1');
$routes->post('alterarCliente/(:num)','Clientes::alteraDadosCliente/$1');
$routes->get('pedidosCliente/(:num)','Pedidos::ultimosPedidos/$1');
$routes->post('clienteLogin','LoginCliente::index');
$routes->get('especificacoes/(:num)','Medidas::index/$1');


$routes->get('login', 'Login::novo',['filter' => 'visitante']);
$routes->group('admin',function($routes){
    $routes->add('formas','Admin\FormasPagamento::index');
    $routes->add('formas/criar','Admin\FormasPagamento::criar');
    $routes->add('formas/show/(:num)','Admin\FormasPagamento::show/$1');
    $routes->add('formas/editar/(:num)','Admin\FormasPagamento::editar/$1');
    $routes->add('formas/desfazerExclusao/(:num)','Admin\FormasPagamento::desfazerexclusao/$1');

    $routes->post('formas/atualizar/(:num)','Admin\FormasPagamento::atualizar/$1');
    $routes->post('formas/cadastrar','Admin\FormasPagamento::cadastrar');

    $routes->match(['get','post'],'formas/excluir/(:num)','Admin\FormasPagamento::excluir/$1');
    $routes->match(['get','post'],'expedientes','Admin\Expedientes::expedientes');
});


