<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/act', 'ActivityLogsController::index');

$routes->get('/ProductAudit/(:alphanum)/(:num)', 'AuditController::ProductAudit/$1/$2');
$routes->get('/audit', 'AuditController::index');
$routes->post('/AddQuantity/(:any)/(:num)', 'AuditController::AddQuantity/$1/$2');


//Current Transaction
$routes->match(['post', 'get'], '/POS/AddItem/(:any)/(:any)', 'CurrentTransactionController::AddItemToCurrentTransaction/$1/$2');
$routes->match(['post', 'get'], '/POS/SubmitOrder/(:any)/(:any)/(:any)', 'CurrentTransactionController::SubmitCurrentTransaction/$1/$2/$3');

$routes->match(['post', 'get'], '/branch', 'BranchController::index');
$routes->get('/countStocksPerBranch', 'BranchController::countStocksPerBranch');



//NOTES
$routes->put('/UpdateNoteStatus/(:num)', 'NotesController::updateNoteStatus/$1');
$routes->delete('/DeleteNote/(:num)', 'NotesController::deleteNote/$1');
$routes->get('/notesList/(:any)', 'NotesController::notesList/$1');
$routes->match(['get', 'post'], '/AddNotes', 'NotesController::AddNotes');

$routes->get('/notif', 'NotificationController::index');







$routes->post('/addBranch', 'BranchController::addBranch');

//Product
$routes->get('main/count-unique-items', 'ProductController::countUniqueItems');
$routes->get('/ItemCategoryList', 'ProductController::ItemCategoryList');
$routes->match(['get', 'post'], '/AddProd', 'ProductController::AddProd');
$routes->get('/ProdList', 'ProductController::index');
$routes->get('/ProdList/(:any)', 'ProductController::BranchProductList/$1');

$routes->match(['get', 'post'], '/AddSched', 'ScheduleController::AddSched');
$routes->get('/ScheduleList', 'ScheduleController::SchedList');

//Sales
$routes->get('/SalesList', 'SalesController::index');



$routes->get('/OrderList', 'OrderController::index');


$routes->get('/CurrentTransaction', 'CurrentTransactionController::index');

//User
$routes->get('/BranchUserList/(:any)', 'UserController::BranchUserList/$1');
$routes->get('/UserList', 'UserController::index');
$routes->match(['post', 'get', 'put'], '/profile/(:any)', 'UserController::profile/$1');
$routes->match(['post', 'get'], '/user_verify/(:any)', 'UserController::userVerify/$1');
$routes->match(['post', 'get'], '/register', 'UserController::register');
$routes->match(['post', 'get'], '/login', 'UserController::login');

//


$routes->match(['get', 'post'], '/AddNotes', 'MainController::AddNotes');



$routes->get('/ItemList', 'MainController::ItemList');

$routes->get('/NotesList', 'MainController::NotesList');

$routes->match(['delete'], '/notes/(:num)', 'MainController::delete/$1');

//unique items





//branch view
$routes->match(['get', 'post'], 'branch/count-unique-items/(:any)', 'ProductController::countBranchUniqueItems/$1');
$routes->match(['get', 'post'], 'branch/inventory/(:any)', 'ProductController::branchInventory/$1');