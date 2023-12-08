<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/act', 'ActivityLogsController::index');

$routes->get('/audit', 'AuditController::index');

$routes->get('/branch', 'BranchController::index');



//NOTES
$routes->put('/UpdateNoteStatus/(:num)', 'NotesController::updateNoteStatus/$1');
$routes->delete('/DeleteNote/(:num)', 'NotesController::deleteNote/$1');
$routes->get('/NotesList', 'NotesController::index');
$routes->match(['get', 'post'], '/AddNotes', 'NotesController::AddNotes');

$routes->get('/notif', 'NotificationController::index');





$routes->get('main/count-unique-items', 'ProductController::countUniqueItems');
$routes->get('/ItemCategoryList', 'ProductController::ItemCategoryList');
$routes->get('/prod', 'ProductController::index');
$routes->match(['get', 'post'], '/AddProd', 'ProductController::AddProd');
$routes->get('/ProdList', 'ProductController::index');

$routes->match(['get', 'post'], '/AddSched', 'ScheduleController::AddSched');
$routes->get('/ScheduleList', 'ScheduleController::SchedList');





$routes->get('/UserList', 'UserController::index');
$routes->match(['post', 'get'], '/register', 'UserController::register');
$routes->match(['post', 'get'], '/login', 'UserController::login');

//


$routes->match(['get', 'post'], '/AddNotes', 'MainController::AddNotes');



$routes->get('/ItemList', 'MainController::ItemList');

$routes->get('/NotesList', 'MainController::NotesList');

$routes->match(['delete'], '/notes/(:num)', 'MainController::delete/$1');

//unique items