<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin', 'Admin::index');
$routes->get('admin/avaliations', 'Admin::index');
$routes->get('admin/usuarios', 'Admin::usuariosCadastrados');
$routes->get('admin/avaliations/new', 'Admin::novaAvaliacao');
$routes->post('admin/avaliations/store', 'Admin::storeAvaliacao');
$routes->get('admin/avaliations/(:num)/edit', 'Admin::editarAvaliacao/$1');
$routes->post('admin/avaliations/(:num)/update', 'Admin::updateAvaliacao/$1');
$routes->post('admin/avaliations/(:num)/delete', 'Admin::excluirAvaliacao/$1');
$routes->get('admin/importar-cdd-xml', 'Admin::importarCddXml');
$routes->get('admin/inport/users', 'Admin::inportUsers');
$routes->get('atividade/(:num)', 'Atividade::show/$1');
$routes->post('atividade/(:num)/responder', 'Atividade::responder/$1');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::autenticar');
$routes->get('login/primeiro-acesso', 'Auth::primeiroAcesso');
$routes->post('login/primeiro-acesso', 'Auth::salvarPrimeiroAcesso');
$routes->get('logout', 'Auth::logout');
