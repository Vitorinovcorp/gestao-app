<?php

use App\Http\Controllers\{
    DashboardController,
    EntityController,
    ContactController,
    ArticleController,
    ProposalController,
    OrderController,
    SupplierInvoiceController,
    SupplierOrderController,
    FinancialController,
    CalendarController,
    PermissionController,
    UserController,
    LogController,
    SettingController,
    ArchiveController,
    ClienteController
};

use Illuminate\Support\Facades\Route;

// ==================== ROTAS PÚBLICAS ====================
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/teste-proposta', function () {
    return view('teste_proposta');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::post('/cliente/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('cliente.logout');

Route::get('/vat-rates', function () {
    return App\Models\VatRate::all();
});

// ==================== ROTAS PROTEGIDAS ====================
Route::middleware(['auth'])->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // ==================== VIEWS (DEVEM VIR PRIMEIRO) ====================
    Route::view('/articles', 'articles.index')->name('articles.index');
    Route::view('/entities', 'entities.index')->name('entities.index');
    Route::view('/contacts', 'contacts.index')->name('contacts.index');
    Route::view('/proposals', 'proposals.index')->name('proposals.index');
    Route::view('/orders', 'orders.index')->name('orders.index');
    Route::view('/supplier-orders', 'supplier-orders.index')->name('supplier-orders.index');
    Route::view('/calendar', 'calendar.index')->name('calendar.index');
    Route::view('/archive', 'archive.index')->name('archive.index');
    Route::view('/users', 'users.index')->name('users.index');
    Route::view('/permissions', 'permissions.index')->name('permissions.index');
    Route::view('/logs', 'logs.index')->name('logs.index');
    Route::view('/settings', 'settings.index')->name('settings.index');

    // VIEWS com parâmetros
    Route::get('/proposals/{id}', function ($id) {
        $proposta = App\Models\Proposal::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return view('proposals.show', compact('proposta'));
    })->name('proposals.show');

    Route::get('/orders/{id}', function ($id) {
        $encomenda = App\Models\Order::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return view('orders.show', compact('encomenda'));
    })->name('orders.show');

    // Faturas de Fornecedor - VIEW
    Route::get('/supplier-invoices', function () {
        return view('supplier-invoices');
    })->name('supplier-invoices.index');

    // ==================== ROTAS DE API (DEVEM VIR DEPOIS) ====================
    Route::prefix('api')->group(function () {

        // Entities API
        Route::prefix('entities')->name('api.entities.')->group(function () {
            Route::get('/', [EntityController::class, 'index'])->name('index');
            Route::get('/clients', [EntityController::class, 'clients'])->name('clients');
            Route::get('/suppliers', [EntityController::class, 'suppliers'])->name('suppliers');
            Route::post('/', [EntityController::class, 'store'])->name('store');
            Route::get('/{entity}', [EntityController::class, 'show'])->name('show');
            Route::put('/{entity}', [EntityController::class, 'update'])->name('update');
            Route::delete('/{entity}', [EntityController::class, 'destroy'])->name('destroy');
            Route::post('/{entity}/toggle-status', [EntityController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/validate-nif', [EntityController::class, 'validateNif'])->name('validate-nif');
            Route::post('/vies-check', [EntityController::class, 'viesCheck'])->name('vies-check');
        });

        // Contacts API
        Route::prefix('contacts')->name('api.contacts.')->group(function () {
            Route::get('/', [ContactController::class, 'index'])->name('index');
            Route::get('/by-entity/{entity}', [ContactController::class, 'byEntity'])->name('by-entity');
            Route::post('/', [ContactController::class, 'store'])->name('store');
            Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
            Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
            Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
            Route::post('/{contact}/toggle-status', [ContactController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Articles API
        Route::prefix('articles')->name('api.articles.')->group(function () {
            Route::get('/', [ArticleController::class, 'index'])->name('index');
            Route::get('/search', [ArticleController::class, 'search'])->name('search');
            Route::post('/', [ArticleController::class, 'store'])->name('store');
            Route::get('/{article}', [ArticleController::class, 'show'])->name('show');
            Route::put('/{article}', [ArticleController::class, 'update'])->name('update');
            Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
            Route::post('/{article}/upload-photo', [ArticleController::class, 'uploadPhoto'])->name('upload-photo');
            Route::delete('/{article}/delete-photo', [ArticleController::class, 'deletePhoto'])->name('delete-photo');
            Route::post('/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Proposals API
        Route::prefix('proposals')->name('api.proposals.')->group(function () {
            Route::get('/', [ProposalController::class, 'index'])->name('index');
            Route::post('/', [ProposalController::class, 'store'])->name('store');
            Route::get('/{proposal}', [ProposalController::class, 'show'])->name('show');
            Route::put('/{proposal}', [ProposalController::class, 'update'])->name('update');
            Route::delete('/{proposal}', [ProposalController::class, 'destroy'])->name('destroy');
            Route::post('/{proposal}/close', [ProposalController::class, 'close'])->name('close');
            Route::post('/{proposal}/convert-to-order', [ProposalController::class, 'convertToOrder'])->name('convert-to-order');
            Route::get('/{proposal}/download-pdf', [ProposalController::class, 'downloadPdf'])->name('download-pdf');
            Route::post('/{proposal}/add-line', [ProposalController::class, 'addLine'])->name('add-line');
            Route::put('/{proposal}/line/{line}', [ProposalController::class, 'updateLine'])->name('update-line');
            Route::delete('/{proposal}/line/{line}', [ProposalController::class, 'deleteLine'])->name('delete-line');
            Route::get('/generate-number', [ProposalController::class, 'generateNumber'])->name('generate-number');
        });

        // Orders API
        Route::prefix('orders')->name('api.orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
            Route::post('/{order}/close', [OrderController::class, 'close'])->name('close');
            Route::post('/{order}/convert-to-supplier-orders', [OrderController::class, 'convertToSupplierOrders'])->name('convert-to-supplier-orders');
            Route::get('/{order}/download-pdf', [OrderController::class, 'downloadPdf'])->name('download-pdf');
            Route::post('/{order}/add-line', [OrderController::class, 'addLine'])->name('add-line');
            Route::put('/{order}/line/{line}', [OrderController::class, 'updateLine'])->name('update-line');
            Route::delete('/{order}/line/{line}', [OrderController::class, 'deleteLine'])->name('delete-line');
            Route::get('/generate-number', [OrderController::class, 'generateNumber'])->name('generate-number');
        });

        // Financial API (incluindo supplier-invoices)
        Route::prefix('financial')->name('api.financial.')->group(function () {
            Route::get('/bank-accounts', [FinancialController::class, 'bankAccounts'])->name('bank-accounts');
            Route::post('/bank-accounts', [FinancialController::class, 'storeBankAccount'])->name('store-bank-account');
            Route::put('/bank-accounts/{account}', [FinancialController::class, 'updateBankAccount'])->name('update-bank-account');
            Route::delete('/bank-accounts/{account}', [FinancialController::class, 'deleteBankAccount'])->name('delete-bank-account');
            Route::get('/client-balances', [FinancialController::class, 'clientBalances'])->name('client-balances');
            Route::get('/client-balances/{client}', [FinancialController::class, 'clientBalanceDetail'])->name('client-balance-detail');
            Route::post('/client-transactions', [FinancialController::class, 'recordClientTransaction'])->name('record-client-transaction');
            Route::get('/vat-rates', [FinancialController::class, 'vatRates'])->name('vat-rates');
            Route::post('/vat-rates', [FinancialController::class, 'storeVatRate'])->name('store-vat-rate');
            Route::put('/vat-rates/{vat}', [FinancialController::class, 'updateVatRate'])->name('update-vat-rate');
            Route::delete('/vat-rates/{vat}', [FinancialController::class, 'deleteVatRate'])->name('delete-vat-rate');
        });

        // Supplier Invoices API (fora do prefixo financial)
        Route::apiResource('supplier-invoices', SupplierInvoiceController::class);
        Route::post('supplier-invoices/{id}/mark-as-paid', [SupplierInvoiceController::class, 'markAsPaid']);
        Route::get('supplier-invoices/{id}/download-document', [SupplierInvoiceController::class, 'downloadDocument']);
        Route::get('supplier-invoices/{id}/download-payment-proof', [SupplierInvoiceController::class, 'downloadPaymentProof']);

        // Calendar API
        Route::prefix('calendar')->name('api.calendar.')->group(function () {
            Route::get('/events', [CalendarController::class, 'events'])->name('events');
            Route::post('/events', [CalendarController::class, 'store'])->name('store');
            Route::get('/events/{event}', [CalendarController::class, 'show'])->name('show');
            Route::put('/events/{event}', [CalendarController::class, 'update'])->name('update');
            Route::delete('/events/{event}', [CalendarController::class, 'destroy'])->name('destroy');
            Route::get('/types', [CalendarController::class, 'types'])->name('types');
            Route::post('/types', [CalendarController::class, 'storeType'])->name('store-type');
            Route::put('/types/{type}', [CalendarController::class, 'updateType'])->name('update-type');
            Route::delete('/types/{type}', [CalendarController::class, 'deleteType'])->name('delete-type');
            Route::get('/actions', [CalendarController::class, 'actions'])->name('actions');
            Route::post('/actions', [CalendarController::class, 'storeAction'])->name('store-action');
            Route::put('/actions/{action}', [CalendarController::class, 'updateAction'])->name('update-action');
            Route::delete('/actions/{action}', [CalendarController::class, 'deleteAction'])->name('delete-action');
        });

        // Archive API
        Route::prefix('archive')->name('api.archive.')->group(function () {
            Route::get('/documents', [ArchiveController::class, 'index'])->name('index');
            Route::post('/upload', [ArchiveController::class, 'upload'])->name('upload');
            Route::get('/documents/{document}', [ArchiveController::class, 'download'])->name('download');
            Route::delete('/documents/{document}', [ArchiveController::class, 'destroy'])->name('destroy');
            Route::post('/documents/{document}/share', [ArchiveController::class, 'share'])->name('share');
            Route::get('/categories', [ArchiveController::class, 'categories'])->name('categories');
            Route::post('/search', [ArchiveController::class, 'search'])->name('search');
        });

        // Users API
        Route::prefix('users')->name('api.users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/reset-2fa', [UserController::class, 'reset2FA'])->name('reset-2fa');
            Route::post('/{user}/send-welcome-email', [UserController::class, 'sendWelcomeEmail'])->name('send-welcome-email');
        });

        // Permissions API
        Route::prefix('permissions')->name('api.permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::post('/groups', [PermissionController::class, 'storeGroup'])->name('store-group');
            Route::get('/groups/{group}', [PermissionController::class, 'showGroup'])->name('show-group');
            Route::put('/groups/{group}', [PermissionController::class, 'updateGroup'])->name('update-group');
            Route::delete('/groups/{group}', [PermissionController::class, 'deleteGroup'])->name('delete-group');
            Route::post('/groups/{group}/sync-permissions', [PermissionController::class, 'syncPermissions'])->name('sync-permissions');
            Route::get('/all-permissions', [PermissionController::class, 'allPermissions'])->name('all-permissions');
            Route::post('/check-permission', [PermissionController::class, 'checkPermission'])->name('check-permission');
        });

        // Logs API
        Route::prefix('logs')->name('api.logs.')->group(function () {
            Route::get('/', [LogController::class, 'index'])->name('index');
            Route::get('/export', [LogController::class, 'export'])->name('export');
            Route::get('/filters', [LogController::class, 'filters'])->name('filters');
            Route::delete('/clear', [LogController::class, 'clearOldLogs'])->name('clear-old');
        });

        // Settings API
        Route::prefix('settings')->name('api.settings.')->group(function () {
            Route::get('/countries', [SettingController::class, 'countries'])->name('countries');
            Route::post('/countries', [SettingController::class, 'storeCountry'])->name('store-country');
            Route::put('/countries/{country}', [SettingController::class, 'updateCountry'])->name('update-country');
            Route::delete('/countries/{country}', [SettingController::class, 'deleteCountry'])->name('delete-country');
            Route::get('/contact-roles', [SettingController::class, 'contactRoles'])->name('contact-roles');
            Route::post('/contact-roles', [SettingController::class, 'storeContactRole'])->name('store-contact-role');
            Route::put('/contact-roles/{role}', [SettingController::class, 'updateContactRole'])->name('update-contact-role');
            Route::delete('/contact-roles/{role}', [SettingController::class, 'deleteContactRole'])->name('delete-contact-role');
            Route::get('/company', [SettingController::class, 'companySettings'])->name('company');
            Route::put('/company', [SettingController::class, 'updateCompany'])->name('update-company');
            Route::post('/company/upload-logo', [SettingController::class, 'uploadLogo'])->name('upload-logo');
            Route::delete('/company/delete-logo', [SettingController::class, 'deleteLogo'])->name('delete-logo');
            Route::get('/general', [SettingController::class, 'generalSettings'])->name('general');
            Route::put('/general', [SettingController::class, 'updateGeneral'])->name('update-general');
            Route::post('/sync', [SettingController::class, 'syncSettings'])->name('sync');
        });

        // Supplier Orders API (para buscar encomendas do fornecedor)
Route::prefix('supplier-orders')->name('api.supplier-orders.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SupplierOrderController::class, 'index'])->name('index');
});
    });

    // ==================== ÁREA DO CLIENTE ====================
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [ClienteController::class, 'dashboard'])->name('dashboard');
        Route::get('/propostas', [ClienteController::class, 'propostas'])->name('propostas');
        Route::get('/propostas/{id}', [ClienteController::class, 'propostaDetalhe'])->name('propostas.show');
        Route::get('/propostas/{id}/download', [ClienteController::class, 'downloadProposta'])->name('propostas.download');
        Route::get('/encomendas', [ClienteController::class, 'encomendas'])->name('encomendas');
        Route::get('/encomendas/{id}', [ClienteController::class, 'encomendaDetalhe'])->name('encomendas.show');
        Route::get('/perfil', [ClienteController::class, 'perfil'])->name('perfil');
        Route::put('/perfil', [ClienteController::class, 'atualizarPerfil'])->name('perfil.update');
    });
});