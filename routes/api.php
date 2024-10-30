<?php

use App\Models\V1\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\FormDataMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('register', 'AuthController@register');
});

Route::post('/deploy', 'App\Http\Controllers\DeployController@deploy');

Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('v1')->namespace('App\Http\Controllers\API\V1')->group(function () {
        Route::prefix('products')->group(function () {
            Route::get('check_sku', 'ProductController@checkSku');
            Route::post('merge', 'ProductController@merge');
            Route::post('bulk_store', 'ProductController@bulkStore');
            Route::get('products_export', 'ProductController@products_export');
            Route::get('store_positions_export', 'ProductController@store_positions_export');
            Route::get('/{product}/transactions_export', 'ProductController@transactionsExport');
            Route::post('bulk_search', 'ProductController@bulkSearch');
            Route::get('search', 'ProductController@search');
            Route::put('correct/{product}', 'ProductController@correct');
            Route::get('/{product}/transactions', 'ProductController@getTransactions');
        });

        Route::apiResource('products', ProductController::class)->only(['index', 'show', 'update', 'store', 'destroy']);
        Route::apiResource('stocks', StockController::class);

        Route::prefix('inventories')->group(function () {
            Route::put('correct', 'InventoryController@correct');
            Route::get('export', 'InventoryController@export');
            Route::get('completed-export', 'InventoryController@exportCompleted');
        });
        Route::apiResource('inventories', InventoryController::class);

        // Счета
        Route::prefix('invoices')->group(function () {
            Route::get('{invoiceID}/refunds', 'InvoiceController@getRefunds');
            Route::get('{invoiceID}/products/available-for-refund', 'InvoiceProductController@refunds');
            Route::get('export', 'InvoiceController@export');
            Route::prefix('products/export')->group(function () {
                Route::get('/', 'InvoiceProductController@export');
                Route::get('for-receive', 'InvoiceProductController@exportForReceive');
                Route::get('for-control', 'InvoiceProductController@exportForControl');
            });
            Route::prefix('bulk')->group(function () {
                Route::put('update', 'InvoiceController@bulkUpdate');
                Route::post('delete', 'InvoiceController@bulkDestroy');
            });
        });
        Route::apiResource('invoices', InvoiceController::class)->middleware(FormDataMiddleware::class);

        Route::apiResource('roles', RoleController::class);

        Route::get('product_refunds/export', 'ProductRefundController@export');
        Route::get('product_refunds/export-products', 'ProductRefundController@exportProducts');
        Route::apiResource('product_refunds', ProductRefundController::class)->middleware(FormDataMiddleware::class);

        Route::prefix('money_refundables')->group(function () {
            Route::get('export', 'MoneyRefundableController@export');
            Route::get('export-incomes', 'MoneyRefundableController@exportIncomes');
            Route::get('export-products', 'MoneyRefundableController@exportProducts');
            Route::post('upload-doc-file/{id}', 'MoneyRefundableController@uploadDocFile');
            Route::post('convert-to-expense/{id}', 'MoneyRefundableController@convertToExpense');
        });
        Route::apiResource('money_refundables', MoneyRefundableController::class);

        Route::get('brands/export', 'BrandController@export');
        Route::apiResource('brands', BrandController::class);

        Route::get('contractors/export', 'ContractorController@export');
        Route::post('contractors/check_symbolic_code', 'ContractorController@checkSymbolicCode');
        Route::apiResource('contractors', ContractorController::class);

        Route::apiResource('permissions', PermissionController::class)->only(['index']);

        Route::apiResource('user-filter-templates', UserFilterTemplateController::class);

        Route::post('users/{id}/delete_avatar', 'UserController@delete_avatar');
        Route::apiResource('users', UserController::class);

        Route::apiResource('settings', SettingController::class);

        Route::apiResource('users', UserController::class);

        Route::apiResource('settings', SettingController::class);

        Route::apiResource('users', UserController::class);

        Route::apiResource('employees', EmployeeController::class);

        // Route::get('orders/statuses', 'OrderController@getStatuses');
        // Route::get('orders/status_groups', 'OrderController@getStatusGroups');
        // Route::get('orders/update_order_statuses', 'OrderController@updateOrderStatuses');

        // Route::get('/orders', [OrderController::class, 'index']);
        // Route::put('/orders', [OrderController::class, 'store']);
        // Route::get('/orders/{id}', [OrderController::class, 'show']);
        // Route::patch('/orders/{id}', [OrderController::class, 'update']); // переделанный маршрут для update
        Route::post('/orders/{id}', 'OrderController@destroy'); // переделанный маршрут для destroy
        Route::apiResource('orders', OrderController::class);

        Route::get('/order-statuses', 'OrderStatusController@index');

        Route::apiResource('price_monitorings', 'PriceMonitoringController');

        Route::apiResource('legal_entities', 'LegalEntityController');
        Route::apiResource('payment_methods', 'PaymentMethodController');

        Route::prefix('defects')->group(function () {
            Route::get('defect-products-export', 'DefectController@exportDefectProducts');
            Route::post('defect-load-files/{defect}', 'DefectController@loadFiles');
            Route::post('defect-delete-file/{defect}', 'DefectController@deleteFile');
        });
        Route::apiResource('defects', DefectController::class);

        Route::get('financial_controls/{payment_method_id}/{payment_date}', 'FinancialControlController@show');
        Route::get('financial_controls/export', 'FinancialControlController@export');
        Route::get('financial_controls/export_all_transactions', 'FinancialControlController@exportAllTransactions');
        Route::post('financial_controls/import', 'FinancialControlController@import');
        Route::apiResource('financial_controls', FinancialControlController::class)->except(['show']);

        Route::get('contractor_refunds/export', 'ContractorRefundController@export');
        Route::get('contractor_refunds/export_products', 'ContractorRefundController@exportProducts');
        Route::apiResource('contractor_refunds', ContractorRefundController::class)->middleware(FormDataMiddleware::class);

        Route::prefix('expenses')->group(function () {
            Route::post('/{expense}/file', 'ExpenseController@uploadFile');
            Route::delete('/{expense}/file', 'ExpenseController@deleteFile');

            Route::post('/{expense}/invoice-file', 'ExpenseController@uploadInvoiceFile');
            Route::delete('/{expense}/invoice-file', 'ExpenseController@deleteInvoiceFile');

            Route::get('/expense-items-export', 'ExpenseController@exportExpenseItems');
            Route::get('/expense-sorted-types-export', 'ExpenseController@exportSortedExpenseItems');

            Route::post('/fast', 'ExpenseController@createFastExpense');
            Route::post('/convert-to-money-refund', 'ExpenseController@convertToMoneyRefund');
            // Добавляем новые маршруты для типов расходов и сводки расходов
            Route::get('expense-types', 'ExpenseController@getExpenseTypes');
        });

        Route::apiResource('expenses', 'ExpenseController');

        // Маршруты для ExpenseSummary
        Route::prefix('expense-summaries')->group(function () {
            Route::get('/', 'ExpenseSummaryController@index');
            Route::patch('/{id}', 'ExpenseSummaryController@update');
            Route::get('/{id}', 'ExpenseSummaryController@show');
            Route::post('/generate', 'ExpenseSummaryController@generate');
            Route::get('/export/regular', 'ExpenseSummaryController@exportRegular');
            Route::get('/export/detailed', 'ExpenseSummaryController@exportDetailed');
        });

        Route::post('expense_types/update_order', 'ExpenseTypeController@updateOrder');
        Route::apiResource('expense_types', 'ExpenseTypeController');

        Route::apiResource('expense_contragents', 'ExpenseContragentController');

        Route::group(['prefix' => 'xml_catalogs'], function () {
            Route::get('/', 'XMLTemplatesController@index');
            Route::post('/manual_generate', 'XMLTemplatesController@manual_generate');
            Route::post('/store', 'XMLTemplatesController@store');
            Route::post('/update/{xmlTemplate}', 'XMLTemplatesController@update');
            Route::delete('/destroy/{xmlTemplate}', 'XMLTemplatesController@destroy');
        });

        Route::group(['prefix' => 'csv-compare'], function () {
            Route::post('upload-file', 'CSVCompareController@uploadFile');
            Route::get('get-data', 'CSVCompareController@getData');
            Route::post('update-comment', 'CSVCompareController@updateComment');
            Route::post('remove-all', 'CSVCompareController@removeAll');
            Route::get('get-uploads', 'CSVCompareController@getUploads');
            Route::get('get-actual', 'CSVCompareController@getActual');
            Route::post('check', 'CSVCompareController@checkUpload');
        });
    });

    Route::prefix('crm')->namespace('App\Http\Controllers')->group(function () {
        Route::post('/create_order', 'CRMController@createOrder');
        Route::post('/update_order', 'CRMController@updateOrder');
        Route::post('/close_order', 'CRMController@closeOrder');
        Route::post('/cancel_order', 'CRMController@cancelOrder');
        Route::post('/defect_order', 'CRMController@defectOrder');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'store'], function () {
        Route::post('/', 'StoreController@index');
        Route::post('/download', 'StoreController@index_download');
        Route::post('/download_group', 'StoreController@index_download_group');
        Route::post('/update/{id}', 'StoreController@update');
        Route::post('/crm/update', 'StoreController@crm_update');
        Route::post('/crm/sync', 'StoreController@crm_sync');
        Route::post('/crm/check_last_sync', 'StoreController@check_last_sync');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'money_refund'], function () {
        Route::post('/', 'MoneyRefundController@index');
        Route::post('/show/{id}', 'MoneyRefundController@show');
        Route::post('/update/{id}', 'MoneyRefundController@update');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'product_refund'], function () {
        Route::post('/', 'ProductRefundsController@index');
        Route::post('store', 'ProductRefundsController@store');
        Route::post('/update/{id}', 'ProductRefundsController@update');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'defects'], function () {
        Route::post('/', 'DefectController@index');
        Route::post('/show/{id}', 'DefectController@show');
        Route::post('store', 'DefectController@store');
        Route::post('/update/{id}', 'DefectController@update');
    });

    Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'financial_control'], function () {
        //     Route::post('/', 'FinancialControl@index');
        //     Route::post('store', 'FinancialControl@store');
        //     Route::post('/update/{id}', 'FinancialControl@update');
    });
});
