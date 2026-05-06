<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);
$routes->get('/', 'Home::index');
$routes->get('/admin/login', 'Home::adminLogin');
$routes->post('/admin/login', 'Home::adminLoginPost');
$routes->get('/dashboard', 'Home::dashboard', ['filter' => 'auth']);
$routes->get('/lettuce-records', 'Home::records', ['filter' => 'auth']);
$routes->get('/predictions', 'Home::predictions', ['filter' => 'auth']);
$routes->get('/analytics', 'Home::analytics', ['filter' => 'auth']);
$routes->post('/plants', 'Home::addPlant', ['filter' => 'auth']);
$routes->post('/plants/(:num)/update', 'Home::updatePlant/$1', ['filter' => 'auth']);
$routes->post('/plants/(:num)/delete', 'Home::deletePlant/$1', ['filter' => 'auth']);
$routes->post('/plants/(:num)/predict', 'Home::updatePredictedHarvest/$1', ['filter' => 'auth']);
$routes->post('/weather', 'Home::weather', ['filter' => 'auth']);
$routes->post('/environment', 'Home::updateEnvironment', ['filter' => 'auth']);
$routes->get('/admin', 'Home::adminDashboard', ['filter' => 'admin']);
$routes->get('/admin/dashboard', 'Home::adminDashboard', ['filter' => 'admin']);
$routes->get('/admin/users', 'Home::adminUsers', ['filter' => 'admin']);
$routes->post('/admin/users/(:num)/delete', 'Home::deleteUser/$1', ['filter' => 'admin']);
$routes->post('/register', 'Home::register');
$routes->post('/login', 'Home::loginPost');
$routes->get('/logout', 'Home::logout');
