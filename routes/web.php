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
    ClienteController,
    CompanyController,
    TenantController,
    OnboardingController,
    SubscriptionController,
    DealController,
    DealStatisticsController,
    AutomationRuleController
};

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/teste-upgrade', function () {
    return response('Rota de teste funcionando!');
});

Route::get('/teste-proposta', function () {
    return view('teste_proposta');
});

Route::get('/deals/statistics', function () {
    \Log::info('Rota deals.statistics chamada');
    return app(DealStatisticsController::class)->index(request());
})->name('deals.statistics');

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

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // ===== VIEWS (ROTAS HTML) =====
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
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::view('/settings', 'settings.index')->name('settings.index');

    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/export', [LogController::class, 'export'])->name('export');
        Route::delete('/clear-old', [LogController::class, 'clearOldLogs'])->name('clear-old');
        Route::post('/clear-old', [LogController::class, 'clearOldLogs'])->name('clear-old-post');
    });

    Route::get('/proposals/{id}', function ($id) {
        $proposta = App\Models\Proposal::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return view('proposals.show', compact('proposta'));
    })->name('proposals.show');

    Route::get('/orders/{id}', function ($id) {
        $encomenda = App\Models\Order::with(['client', 'createdBy', 'lines.article'])->findOrFail($id);
        return view('orders.show', compact('encomenda'));
    })->name('orders.show');

    Route::get('/supplier-invoices', function () {
        return view('supplier-invoices');
    })->name('supplier-invoices.index');

    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::put('/update', [CompanyController::class, 'update'])->name('update');
        Route::post('/upload-logo', [CompanyController::class, 'uploadLogo'])->name('upload-logo');
        Route::delete('/delete-logo', [CompanyController::class, 'deleteLogo'])->name('delete-logo');
    });

    Route::prefix('api')->group(function () {
        Route::prefix('entities')->name('api.entities.')->group(function () {
            Route::get('/', [EntityController::class, 'index'])->name('index');
            Route::get('/clients', [EntityController::class, 'clients'])->name('clients');
            Route::get('/suppliers', [EntityController::class, 'suppliers'])->name('suppliers');
            Route::post('/', [EntityController::class, 'store'])->name('store');
            Route::get('/{entity}', [EntityController::class, 'show'])->name('show');
            Route::put('/{entity}', [EntityController::class, 'update'])->name('update');
            Route::delete('/{entity}', [EntityController::class, 'destroy'])->name('destroy');
            Route::post('/{entity}/toggle-status', [EntityController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('contacts')->name('api.contacts.')->group(function () {
            Route::get('/', [ContactController::class, 'index'])->name('index');
            Route::get('/by-entity/{entity}', [ContactController::class, 'byEntity'])->name('by-entity');
            Route::post('/', [ContactController::class, 'store'])->name('store');
            Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
            Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
            Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
            Route::post('/{contact}/toggle-status', [ContactController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('articles')->name('api.articles.')->group(function () {
            Route::get('/', [ArticleController::class, 'apiIndex'])->name('index');
            Route::get('/search', [ArticleController::class, 'search'])->name('search');
            Route::post('/', [ArticleController::class, 'store'])->name('store');
            Route::get('/{article}', [ArticleController::class, 'show'])->name('show');
            Route::get('/{article}/edit', [ArticleController::class, 'edit'])->name('edit');
            Route::put('/{article}', [ArticleController::class, 'update'])->name('update');
            Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
            Route::post('/{article}/upload-photo', [ArticleController::class, 'uploadPhoto'])->name('upload-photo');
            Route::delete('/{article}/delete-photo', [ArticleController::class, 'deletePhoto'])->name('delete-photo');
            Route::post('/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('toggle-status');
        });

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

        Route::apiResource('supplier-invoices', SupplierInvoiceController::class);
        Route::post('supplier-invoices/{id}/mark-as-paid', [SupplierInvoiceController::class, 'markAsPaid']);
        Route::get('supplier-invoices/{id}/download-document', [SupplierInvoiceController::class, 'downloadDocument']);
        Route::get('supplier-invoices/{id}/download-payment-proof', [SupplierInvoiceController::class, 'downloadPaymentProof']);

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

        Route::prefix('archive')->name('api.archive.')->group(function () {
            Route::get('/documents', [ArchiveController::class, 'index'])->name('index');
            Route::post('/upload', [ArchiveController::class, 'upload'])->name('upload');
            Route::get('/documents/{document}', [ArchiveController::class, 'download'])->name('download');
            Route::delete('/documents/{document}', [ArchiveController::class, 'destroy'])->name('destroy');
            Route::post('/documents/{document}/share', [ArchiveController::class, 'share'])->name('share');
            Route::get('/categories', [ArchiveController::class, 'categories'])->name('categories');
            Route::post('/search', [ArchiveController::class, 'search'])->name('search');
        });

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

        Route::prefix('permissions')->name('api.permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'getRoles'])->name('get-roles');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{role}', [PermissionController::class, 'show'])->name('show');
            Route::put('/{role}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{role}', [PermissionController::class, 'destroy'])->name('destroy');
        });

        Route::get('/permissions-list', [PermissionController::class, 'permissionsList'])->name('permissions.list');

        Route::prefix('logs')->name('api.logs.')->group(function () {
            Route::get('/', [LogController::class, 'index'])->name('index');
            Route::get('/export', [LogController::class, 'export'])->name('export');
            Route::get('/filters', [LogController::class, 'filters'])->name('filters');
            Route::delete('/clear', [LogController::class, 'clearOldLogs'])->name('clear-old');
            Route::post('/clear', [LogController::class, 'clearOldLogs'])->name('clear-old-post');
        });

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

        Route::prefix('supplier-orders')->name('api.supplier-orders.')->group(function () {
            Route::get('/', [SupplierOrderController::class, 'index'])->name('index');
        });
    });

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

    Route::get('/user/permissions', function () {
        $user = auth()->user();
        return response()->json([
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    })->name('user.permissions');

    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::match(['GET', 'POST'], '/switch/{id}', [TenantController::class, 'switch'])->name('switch');
        Route::get('/settings', [TenantController::class, 'settings'])->name('settings');
        Route::put('/settings', [TenantController::class, 'updateSettings'])->name('update-settings');
        Route::delete('/{id}', [TenantController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('onboarding')->name('onboarding.')->middleware(['auth'])->group(function () {
        Route::get('/', [OnboardingController::class, 'index'])->name('index');
        Route::get('/step/{step?}', [OnboardingController::class, 'step'])->name('step');
        Route::post('/process/{step}', [OnboardingController::class, 'process'])->name('process');
        Route::get('/completed', [OnboardingController::class, 'completed'])->name('completed');
    });

    Route::prefix('subscription')->name('subscription.')->middleware(['auth'])->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
        Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/change/{plan}', [SubscriptionController::class, 'upgrade'])->name('change');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/logs', [SubscriptionController::class, 'logs'])->name('logs');
        Route::get('/change-get/{plan}', [SubscriptionController::class, 'upgrade'])->name('change-get');
    });

    Route::get('/mudar-plano/{id}', function ($planId) {
        // Buscar o tenant ativo pela sessão
        $tenantId = session('active_tenant');
        if (!$tenantId) {
            return redirect('/subscription')->with('error', 'Nenhum tenant ativo encontrado.');
        }

        $subscription = \App\Models\Subscription::where('tenant_id', $tenantId)->first();
        if (!$subscription) {
            return redirect('/subscription')->with('error', 'Nenhuma subscrição encontrada.');
        }

        $subscription->plan_id = $planId;
        $subscription->save();

        return redirect('/subscription')->with('success', 'Plano alterado com sucesso!');
    })->name('mudar.plano');

    Route::prefix('deals')->name('deals.')->middleware(['auth'])->group(function () {
        Route::get('/', [DealController::class, 'index'])->name('index');
        Route::get('/kanban', [DealController::class, 'kanban'])->name('kanban');
        Route::get('/create', [DealController::class, 'create'])->name('create');
        Route::post('/', [DealController::class, 'store'])->name('store');
        Route::get('/{deal}', [DealController::class, 'show'])->name('show');
        Route::get('/{deal}/edit', [DealController::class, 'edit'])->name('edit');
        Route::put('/{deal}', [DealController::class, 'update'])->name('update');
        Route::delete('/{deal}', [DealController::class, 'destroy'])->name('destroy');
        Route::post('/{deal}/move', [DealController::class, 'move'])->name('move');
        Route::post('/{deal}/send-proposal', [DealController::class, 'sendProposal'])->name('send-proposal');
        Route::post('/{deal}/convert-to-invoice', [DealController::class, 'convertToInvoice'])->name('deals.convert-to-invoice');
        Route::post('/deals/{deal}/activate-follow-up', [DealController::class, 'activateFollowUp'])->name('deals.activate-follow-up');
        Route::post('/deals/{deal}/cancel-follow-up', [DealController::class, 'cancelFollowUp'])->name('deals.cancel-follow-up');
        Route::get('/statistics', [DealStatisticsController::class, 'index'])->name('statistics');
        Route::get('/statistics/{article}/details', [DealStatisticsController::class, 'details'])->name('statistics.details');
        Route::get('/statistics/export', [DealStatisticsController::class, 'export'])->name('statistics.export');
        Route::post('/{deal}/activities', [DealController::class, 'storeActivity'])->name('activities.store');
        Route::post('/{deal}/activate-follow-up', [DealController::class, 'activateFollowUp'])->name('activate-follow-up');
        Route::put('/{deal}/activities/{activity}', [DealController::class, 'updateActivity'])->name('activities.update');
        Route::delete('/{deal}/activities/{activity}', [DealController::class, 'destroyActivity'])->name('activities.destroy');
    });

    Route::get('/permissions', function () {
        return view('permissions.index');
    })->name('permissions.index')->middleware(['auth']);

    Route::prefix('automation')->name('automation.')->middleware(['auth'])->group(function () {
        Route::get('/', [AutomationRuleController::class, 'index'])->name('index');
        Route::get('/create', [AutomationRuleController::class, 'create'])->name('create');
        Route::post('/', [AutomationRuleController::class, 'store'])->name('store');
        Route::get('/{rule}/edit', [AutomationRuleController::class, 'edit'])->name('edit');
        Route::put('/{rule}', [AutomationRuleController::class, 'update'])->name('update');
        Route::delete('/{rule}', [AutomationRuleController::class, 'destroy'])->name('destroy');
        Route::post('/{rule}/toggle-status', [AutomationRuleController::class, 'toggleStatus'])->name('toggle-status');
    });
});


Route::get('/api/all-articles', [ArticleController::class, 'allArticles']);

Route::get('/api/contacts', [ContactController::class, 'apiIndex']);
