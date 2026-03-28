<?php
require 'config.php';
require 'src/Core/Database.php';
require 'src/Core/Session.php';
require 'src/Models/User.php';
require 'src/Models/Transaction.php';
require 'src/Controllers/AuthController.php';
require 'src/Controllers/DashboardController.php';
require 'src/Controllers/TransactionController.php';
require 'src/Controllers/ExportController.php';

Session::init();
$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;
    case 'authenticate':
        $controller = new AuthController();
        $controller->authenticate();
        break;
    case 'register':
        $controller = new AuthController();
        $controller->register();
        break;
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
    case 'transactions':
        $controller = new TransactionController();
        $controller->index();
        break;
    case 'transaction_store':
        $controller = new TransactionController();
        $controller->store();
        break;
    case 'store_from_calendar':
        $controller = new TransactionController();
        $controller->storeFromCalendar();
        break;
    case 'update_transaction':
        $controller = new TransactionController();
        $controller->update();
        break;
    case 'edit_transaction':
        $controller = new TransactionController();
        $controller->edit();
        break;
    case 'transaction_delete':
        $controller = new TransactionController();
        $controller->delete();
        break;
    case 'delete_from_calendar':
        $controller = new TransactionController();
        $controller->deleteFromCalendar();
        break;
    case 'categories':
        $controller = new TransactionController();
        $controller->categories();
        break;
    case 'add_category':
        $controller = new TransactionController();
        $controller->addCategory();
        break;
    case 'edit_category':
        $controller = new TransactionController();
        $controller->editCategory();
        break;
    case 'update_category':
        $controller = new TransactionController();
        $controller->updateCategory();
        break;
    case 'delete_category':
        $controller = new TransactionController();
        $controller->deleteCategory();
        break;
    case 'calendar':
        $controller = new TransactionController();
        $controller->calendar();
        break;
    case 'save_budget':
        $controller = new TransactionController();
        $controller->saveBudget();
        break;
    case 'export_csv':
        $controller = new ExportController();
        $controller->csv();
        break;
    case 'export_pdf':
        $controller = new ExportController();
        $controller->pdf();
        break;
    default:
        header("Location: ?page=login");
        break;
}