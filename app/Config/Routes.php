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
$routes->match(['post', 'get'], '/POS/GetItemListMAIN/(:any)/(:any)', 'AdminController::CurrentTransactionListMAIN/$1/$2');

$routes->match(['post', 'get'], '/POS/SubmitOrder', 'CurrentTransactionController::SubmitCurrentTransaction');
$routes->match(['post', 'get'], '/POS/SubmitOrderAdmin', 'CurrentTransactionController::SubmitCurrentTransactionAdmin');
$routes->get('/ClearCurrentTransaction', 'CurrentTransactionController::ClearCurrentTransaction');
$routes->get('ExpiryChecker', 'CurrentTransactionController::ExpirationChecker');


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




$routes->post('/logVisit', 'GuestController::logVisit');
$routes->post('/SearchMed', 'GuestController::SearchMed');

$routes->get('/countTodayNewVisits', 'GuestController::countTodayNewVisits');
$routes->get('/countOverallNewVisits', 'GuestController::countOverallNewVisits');

$routes->get('/countTodayReturnees', 'GuestController::countTodayReturnees');
$routes->get('/countOverallReturnees', 'GuestController::countOverallReturnees');



$routes->post('/addBranch', 'BranchController::addBranch');

//Product
$routes->put('/ProdUpdate', 'ProductController::ProdUpdate');
$routes->get('/ItemCategoryList', 'ProductController::ItemCategoryList');
$routes->match(['get', 'post'], '/AddProd', 'ProductController::AddProd');
$routes->match(['get', 'post'], '/deleteprod', 'ProductController::deleteprod');
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


// $routes->match(['get', 'post'], '/AddNotes', 'MainController::AddNotes');



$routes->get('/ItemList', 'MainController::ItemList');

$routes->get('/NotesList', 'MainController::NotesList');

$routes->match(['delete'], '/notes/(:num)', 'MainController::delete/$1');

//unique items





//branch view
$routes->match(['get', 'post'], 'branch/inventory/(:any)', 'ProductController::branchInventory/$1');





//KPIs key performance indicators
//Expiration
$routes->get('/ExpirationBranchProduct', 'AuditController::ExpirationBranchProduct');
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



//admin

$routes->get('/AdminInventoryFilter', 'AdminController::AdminInventoryFilter');
$routes->get('/ProdInfo', 'AdminController::ProdInfo');
$routes->get('/AdminInventoryTable', 'AdminController::AdminInventoryTable');

$routes->get('/AdminSalesTable', 'AdminController::AdminSalesTable');
$routes->get('/AdminSalesFilter', 'AdminController::AdminSalesFilter');

$routes->get('/AdminProductViewTable', 'AdminController::AdminProductViewTable');
$routes->get('/AdminProductViewFilter', 'AdminController::AdminProductViewFilter');

$routes->get('/AdminOrderViewTable', 'AdminController::AdminOrderViewTable');




//notif
$routes->get('/BranchNotifications', 'NotificationController::BranchNotifications');
$routes->get('/NotificationRead', 'NotificationController::NotificationRead');


//Branch Admin
$routes->get('/Graph', 'BranchAdminController::Graph');