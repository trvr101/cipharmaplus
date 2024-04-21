<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/act', 'ActivityLogsController::index');

$routes->get('/ProductAudit/(:alphanum)/(:num)', 'AuditController::ProductAudit/$1/$2');
$routes->get('/audit', 'AuditController::index');
$routes->post('/AddQuantity', 'AuditController::AddQuantity');



//Current Transaction SalesTransactionList
$routes->match(['post', 'get'], '/SalesTransactionList', 'CurrentTransactionController::SalesTransactionList');

$routes->get('/BranchOrderView', 'CurrentTransactionController::BranchOrderView');
$routes->match(['post', 'get'], '/TransactionTotalAmount/(:any)', 'CurrentTransactionController::TransactionTotalAmount/$1');
$routes->match(['post', 'get'], '/POS/AddItem/(:any)/(:any)', 'CurrentTransactionController::AddItemToCurrentTransaction/$1/$2');
//
$routes->match(['post', 'get'], '/POS/GetItemList/(:any)/(:any)', 'CurrentTransactionController::CurrentTransactionList/$1/$2');
$routes->match(['post', 'get'], '/POS/SubmitOrder/(:any)/(:any)/(:any)', 'CurrentTransactionController::SubmitCurrentTransaction/$1/$2/$3');



//Branch
$routes->match(['post', 'get'], '/branch', 'BranchController::index');
$routes->get('/countStocksPerBranch', 'BranchController::countStocksPerBranch');
$routes->get('/BranchSalesPerWeek', 'BranchController::BranchSalesPerWeek');
$routes->get('/SalesPredictionPerWeek', 'BranchController::SalesPredictionPerWeek');
$routes->get('/SalesPredictionPerDay', 'BranchController::SalesPredictionPerDay');
$routes->get('/BranchInfo', 'BranchController::BranchInfo');
$routes->match(['post', 'get', 'put', 'patch'], '/UpdateBranchInfo', 'BranchController::UpdateBranchInfo');
$routes->match(['post', 'get', 'put', 'patch'], '/IsOpenForInvitation', 'BranchController::IsOpenForInvitation');
$routes->match(['post', 'get', 'put', 'patch'], '/toggleInvitation', 'BranchController::toggleInvitation');
$routes->match(['post', 'get', 'put', 'patch'], '/RegenerateInvitationCode', 'BranchController::RegenerateInvitationCode');
$routes->get('/Branchlocator', 'BranchController::Branchlocator');
$routes->get('/MedicineLocator', 'BranchController::MedicineLocator');




//NOTES
$routes->put('/UpdateNoteStatus/(:num)', 'NotesController::updateNoteStatus/$1');
$routes->delete('/DeleteNote/(:num)', 'NotesController::deleteNote/$1');
$routes->get('/notesList/(:any)', 'NotesController::notesList/$1');
$routes->match(['get', 'post'], '/AddNotes', 'NotesController::AddNotes');

$routes->get('/notif', 'NotificationController::index');







$routes->post('/addBranch', 'BranchController::addBranch');

//Product
$routes->get('/ItemCategoryList', 'ProductController::ItemCategoryList');
$routes->match(['get', 'post'], '/AddProd', 'ProductController::AddProd');
//for table
$routes->get('/BranchProductListFilter', 'ProductController::BranchProductListFilter');
//for filter model(completed product)
$routes->get('/BranchProductList', 'ProductController::BranchProductList');
// for filter
$routes->get('/AdminProductList', 'ProductController::AdminProductList');
//for table
$routes->get('/AdminProductListFilter', 'ProductController::AdminProductListFilter');

$routes->get('/BranchProduct/(:any)', 'ProductController::BranchProduct/$1');

$routes->match(['get', 'post'], '/AddSched', 'ScheduleController::AddSched');
$routes->get('/SchedList', 'ScheduleController::SchedList');

//Sales
$routes->get('/SalesList', 'SalesController::index');


//order
$routes->get('/OrderList', 'OrderController::index');
$routes->get('/SalesTransaction', 'OrderController::SalesTransaction');
$routes->get('/HoldSalesTransaction', 'OrderController::HoldSalesTransaction');


$routes->get('/CurrentTransaction', 'CurrentTransactionController::index');

//User
$routes->match(['post', 'get', 'put', 'patch'], '/UpdatedProfile', 'UserController::UpdatedProfile');
$routes->match(['post', 'get', 'put', 'patch'], '/UpdatedPassword', 'UserController::UpdatedPassword');

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
$routes->match(['get', 'post'], 'branch/inventory/(:any)', 'ProductController::branchInventory/$1');



//KPIs key performance indicators
//Expiration
$routes->get('/ExpirationAlertPerProduct', 'AuditController::ExpirationAlertPerProduct');
//Earnings
$routes->get('/EarningsPerWeek', 'OrderController::EarningsPerWeek');
//Top Selling Products
$routes->get('/TopSellingProductPerWeek', 'AuditController::TopSellingProductPerWeek');

//AOV (Average Order Value)
$routes->get('/AverageOrderValuePerWeek', 'OrderController::AverageOrderValuePerWeek');

//Total Unique Items
$routes->get('/countBranchUniqueItems', 'ProductController::countBranchUniqueItems/');
//Orders Today
$routes->get('/TransactionToday', 'OrderController::TransactionToday');
//Total Branch Worker Count
$routes->get('/TotalBranchWorker', 'BranchController::TotalBranchWorker');