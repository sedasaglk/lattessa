<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\AppointmentController;
use App\Http\Controllers\Panel\CustomerController;
use App\Http\Controllers\Panel\ServiceController;
use App\Http\Controllers\Panel\StaffController;
use App\Http\Controllers\Panel\CashController;
use App\Http\Controllers\Panel\ReportController;
use App\Http\Controllers\Panel\SettingsController;
use App\Http\Controllers\Panel\SubscriptionController;
use App\Http\Controllers\Panel\InventoryController;
use App\Http\Controllers\Panel\SaleController;
use App\Http\Controllers\Panel\LoyaltyController;
use App\Http\Controllers\Panel\MarketingController;
use App\Http\Controllers\Panel\BranchController;
use App\Http\Controllers\Panel\CrmController;
use App\Http\Controllers\Panel\WaitingListController;
use App\Http\Controllers\Panel\PayrollController;
use App\Http\Controllers\Panel\ServicePackageController;
use App\Http\Controllers\Panel\SupportController;
use App\Http\Controllers\Panel\NotificationController;
use App\Http\Controllers\Panel\WhatsAppConnectionController;
use App\Http\Controllers\Panel\ClientFileController;
use App\Http\Controllers\Booking\OnlineBookingController;

Route::get('/', function () { return view('welcome'); });
Route::get('/kayit', [RegisterController::class, 'create'])->name('register.form');
Route::post('/kayit', [RegisterController::class, 'store'])->name('register.store');

Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/giris', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'login'])->name('login.post');
    Route::post('/cikis', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'logout'])->name('logout');
    Route::middleware('super.admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/firmalar', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'index'])->name('tenants.index');
        Route::get('/firmalar/{id}', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'show'])->name('tenants.show');
        Route::patch('/firmalar/{id}/durum', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'updateStatus'])->name('tenants.status');

        // Paket yonetimi
        Route::get('/paketler', [\App\Http\Controllers\SuperAdmin\PackageController::class, 'index'])->name('packages.index');
        Route::get('/paketler/{id}/duzenle', [\App\Http\Controllers\SuperAdmin\PackageController::class, 'edit'])->name('packages.edit');
        Route::put('/paketler/{id}', [\App\Http\Controllers\SuperAdmin\PackageController::class, 'update'])->name('packages.update');
        Route::post('/paketler', [\App\Http\Controllers\SuperAdmin\PackageController::class, 'store'])->name('packages.store');

        // Destek talepleri
        Route::get('/destek', [\App\Http\Controllers\SuperAdmin\SupportTicketController::class, 'index'])->name('support.index');
        Route::get('/destek/{id}', [\App\Http\Controllers\SuperAdmin\SupportTicketController::class, 'show'])->name('support.show');
        Route::post('/destek/{id}/yanitla', [\App\Http\Controllers\SuperAdmin\SupportTicketController::class, 'reply'])->name('support.reply');
        Route::post('/destek/{id}/oncelik', [\App\Http\Controllers\SuperAdmin\SupportTicketController::class, 'updatePriority'])->name('support.priority');

        // SMS saglayici
        Route::get('/sms', [\App\Http\Controllers\SuperAdmin\SmsProviderController::class, 'index'])->name('sms.index');
        Route::post('/sms', [\App\Http\Controllers\SuperAdmin\SmsProviderController::class, 'store'])->name('sms.store');
        Route::post('/sms/{id}/varsayilan', [\App\Http\Controllers\SuperAdmin\SmsProviderController::class, 'setDefault'])->name('sms.default');
        Route::post('/sms/{id}/toggle', [\App\Http\Controllers\SuperAdmin\SmsProviderController::class, 'toggle'])->name('sms.toggle');
        Route::delete('/sms/{id}', [\App\Http\Controllers\SuperAdmin\SmsProviderController::class, 'destroy'])->name('sms.destroy');

        // Sistem loglari
        Route::get('/loglar', [\App\Http\Controllers\SuperAdmin\SystemLogController::class, 'index'])->name('logs.index');

        // WhatsApp yonetimi
        Route::get('/whatsapp', [\App\Http\Controllers\SuperAdmin\WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::post('/whatsapp/giris/telefon', [\App\Http\Controllers\SuperAdmin\WhatsAppController::class, 'startPhoneLogin'])->name('whatsapp.login.phone');
        Route::post('/whatsapp/giris/qr', [\App\Http\Controllers\SuperAdmin\WhatsAppController::class, 'startQrLogin'])->name('whatsapp.login.qr');
        Route::get('/whatsapp/{id}/durum', [\App\Http\Controllers\SuperAdmin\WhatsAppController::class, 'checkStatus'])->name('whatsapp.check');
        Route::post('/whatsapp/{id}/baglanti-kes', [\App\Http\Controllers\SuperAdmin\WhatsAppController::class, 'disconnect'])->name('whatsapp.disconnect');

        // 2FA yonetimi
        Route::get('/2fa', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'setup'])->name('2fa.setup');
        Route::post('/2fa/etkinlestir', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::post('/2fa/devre-disi', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'disable'])->name('2fa.disable');
    });
});

// 2FA challenge (auth oncesi)
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/2fa-dogrula', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa-dogrula', [\App\Http\Controllers\SuperAdmin\TwoFactorController::class, 'verify'])->name('2fa.verify');
});

Route::prefix('{tenant_slug}')->middleware('tenant')->group(function () {

    Route::get('/giris', [LoginController::class, 'create'])->name('login.form');
    Route::post('/giris', [LoginController::class, 'store'])->name('login.store');
    Route::post('/cikis', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/randevu', [OnlineBookingController::class, 'show'])->name('booking.show');
    Route::post('/randevu', [OnlineBookingController::class, 'store'])->name('booking.store');
    Route::get('/randevu/basarili', [OnlineBookingController::class, 'success'])->name('booking.success');
    Route::get('/randevu/personel', [OnlineBookingController::class, 'getStaff'])->name('booking.staff');
    Route::get('/randevu/saatler', [OnlineBookingController::class, 'getAvailableSlots'])->name('booking.slots');

    Route::middleware('tenant.auth')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('tenant.home');

        Route::get('/randevular', [AppointmentController::class, 'index'])->name('panel.appointments.index');
        Route::get('/randevular/events', [AppointmentController::class, 'calendarEvents'])->name('panel.appointments.events');
        Route::get('/randevular/yeni', [AppointmentController::class, 'create'])->name('panel.appointments.create');
        Route::post('/randevular', [AppointmentController::class, 'store'])->name('panel.appointments.store');
        Route::get('/randevular/{id}', [AppointmentController::class, 'show'])->name('panel.appointments.show');
        Route::patch('/randevular/{id}/durum', [AppointmentController::class, 'updateStatus'])->name('panel.appointments.status');
        Route::delete('/randevular/{id}', [AppointmentController::class, 'destroy'])->name('panel.appointments.destroy');

        Route::get('/musteriler', [CustomerController::class, 'index'])->name('panel.customers.index');
        Route::get('/musteriler/yeni', [CustomerController::class, 'create'])->name('panel.customers.create');
        Route::post('/musteriler', [CustomerController::class, 'store'])->name('panel.customers.store');
        Route::get('/musteriler/{id}', [CustomerController::class, 'show'])->name('panel.customers.show');
        Route::get('/musteriler/{id}/duzenle', [CustomerController::class, 'edit'])->name('panel.customers.edit');
        Route::put('/musteriler/{id}', [CustomerController::class, 'update'])->name('panel.customers.update');
        Route::delete('/musteriler/{id}', [CustomerController::class, 'destroy'])->name('panel.customers.destroy');
        Route::post('/musteriler/{customer_id}/mesaj-gonder', [NotificationController::class, 'sendToCustomer'])->name('panel.notifications.send');

        Route::get('/whatsapp-baglanti', [WhatsAppConnectionController::class, 'index'])->name('panel.whatsapp.index');
        Route::post('/whatsapp-baglanti/giris/telefon', [WhatsAppConnectionController::class, 'startPhoneLogin'])->name('panel.whatsapp.login.phone');
        Route::post('/whatsapp-baglanti/giris/qr', [WhatsAppConnectionController::class, 'startQrLogin'])->name('panel.whatsapp.login.qr');
        Route::get('/whatsapp-baglanti/{id}/durum', [WhatsAppConnectionController::class, 'checkStatus'])->name('panel.whatsapp.check');
        Route::post('/whatsapp-baglanti/{id}/baglanti-kes', [WhatsAppConnectionController::class, 'disconnect'])->name('panel.whatsapp.disconnect');

        Route::get('/hizmetler', [ServiceController::class, 'index'])->name('panel.services.index');
        Route::get('/hizmetler/yeni', [ServiceController::class, 'create'])->name('panel.services.create');
        Route::post('/hizmetler', [ServiceController::class, 'store'])->name('panel.services.store');
        Route::get('/hizmetler/{id}/duzenle', [ServiceController::class, 'edit'])->name('panel.services.edit');
        Route::put('/hizmetler/{id}', [ServiceController::class, 'update'])->name('panel.services.update');
        Route::delete('/hizmetler/{id}', [ServiceController::class, 'destroy'])->name('panel.services.destroy');

        Route::get('/personel', [StaffController::class, 'index'])->name('panel.staff.index');
        Route::get('/personel/yeni', [StaffController::class, 'create'])->name('panel.staff.create');
        Route::post('/personel', [StaffController::class, 'store'])->name('panel.staff.store');
        Route::get('/personel/{id}', [StaffController::class, 'show'])->name('panel.staff.show');
        Route::get('/personel/{id}/duzenle', [StaffController::class, 'edit'])->name('panel.staff.edit');
        Route::put('/personel/{id}', [StaffController::class, 'update'])->name('panel.staff.update');
        Route::post('/personel/{id}/takvim', [StaffController::class, 'updateSchedule'])->name('panel.staff.schedule');
        Route::post('/personel/{id}/izin', [StaffController::class, 'storeLeave'])->name('panel.staff.leave.store');
        Route::delete('/personel/{id}/izin/{leave_id}', [StaffController::class, 'destroyLeave'])->name('panel.staff.leave.destroy');
        Route::delete('/personel/{id}', [StaffController::class, 'destroy'])->name('panel.staff.destroy');

        Route::get('/bordro', [PayrollController::class, 'index'])->name('panel.payroll.index');
        Route::get('/bordro/personel/{user_id}', [PayrollController::class, 'show'])->name('panel.payroll.show');
        Route::post('/bordro/hesapla', [PayrollController::class, 'generate'])->name('panel.payroll.generate');
        Route::put('/bordro/{id}', [PayrollController::class, 'update'])->name('panel.payroll.update');
        Route::post('/bordro/{id}/odendi', [PayrollController::class, 'markPaid'])->name('panel.payroll.paid');

        Route::get('/paketler', [ServicePackageController::class, 'index'])->name('panel.packages.index');
        Route::post('/paketler', [ServicePackageController::class, 'store'])->name('panel.packages.store');
        Route::delete('/paketler/{id}', [ServicePackageController::class, 'destroy'])->name('panel.packages.destroy');
        Route::get('/paketler/musteri/{customer_id}', [ServicePackageController::class, 'customerPackages'])->name('panel.packages.customer');
        Route::post('/paketler/musteri/{customer_id}/tani', [ServicePackageController::class, 'sellToCustomer'])->name('panel.packages.sell');
        Route::post('/paketler/kullanim/{customer_package_id}', [ServicePackageController::class, 'useSession'])->name('panel.packages.use');

        Route::get('/satislar', [SaleController::class, 'index'])->name('panel.sales.index');
        Route::get('/satislar/yeni', [SaleController::class, 'create'])->name('panel.sales.create');
        Route::post('/satislar', [SaleController::class, 'store'])->name('panel.sales.store');
        Route::get('/satislar/{id}', [SaleController::class, 'show'])->name('panel.sales.show');
        Route::post('/satislar/{id}/iade', [SaleController::class, 'refund'])->name('panel.sales.refund');

        Route::get('/stok', [InventoryController::class, 'index'])->name('panel.inventory.index');
        Route::get('/stok/kategoriler', [InventoryController::class, 'categories'])->name('panel.inventory.categories');
        Route::post('/stok/kategoriler', [InventoryController::class, 'storeCategory'])->name('panel.inventory.categories.store');
        Route::delete('/stok/kategoriler/{id}', [InventoryController::class, 'destroyCategory'])->name('panel.inventory.categories.destroy');
        Route::get('/stok/tedarikciler', [InventoryController::class, 'suppliers'])->name('panel.inventory.suppliers');
        Route::post('/stok/tedarikciler', [InventoryController::class, 'storeSupplier'])->name('panel.inventory.suppliers.store');
        Route::delete('/stok/tedarikciler/{id}', [InventoryController::class, 'destroySupplier'])->name('panel.inventory.suppliers.destroy');
        Route::get('/stok/urun/yeni', [InventoryController::class, 'create'])->name('panel.inventory.create');
        Route::post('/stok/urun', [InventoryController::class, 'store'])->name('panel.inventory.store');
        Route::get('/stok/urun/{id}', [InventoryController::class, 'show'])->name('panel.inventory.show');
        Route::get('/stok/urun/{id}/duzenle', [InventoryController::class, 'edit'])->name('panel.inventory.edit');
        Route::put('/stok/urun/{id}', [InventoryController::class, 'update'])->name('panel.inventory.update');
        Route::post('/stok/urun/{id}/stok', [InventoryController::class, 'addStock'])->name('panel.inventory.stock');
        Route::delete('/stok/urun/{id}', [InventoryController::class, 'destroy'])->name('panel.inventory.destroy');

        Route::get('/kasa', [CashController::class, 'index'])->name('panel.cash.index');
        Route::post('/kasa', [CashController::class, 'store'])->name('panel.cash.store');
        Route::delete('/kasa/{id}', [CashController::class, 'destroy'])->name('panel.cash.destroy');
        Route::post('/kasa/kategori', [CashController::class, 'storeCategory'])->name('panel.cash.category.store');

        Route::get('/sadakat', [LoyaltyController::class, 'index'])->name('panel.loyalty.index');
        Route::post('/sadakat/seviyeler', [LoyaltyController::class, 'storeTier'])->name('panel.loyalty.tiers.store');
        Route::delete('/sadakat/seviyeler/{id}', [LoyaltyController::class, 'destroyTier'])->name('panel.loyalty.tiers.destroy');
        Route::get('/sadakat/musteri/{customer_id}', [LoyaltyController::class, 'customerPoints'])->name('panel.loyalty.customer');
        Route::post('/sadakat/musteri/{customer_id}/ekle', [LoyaltyController::class, 'addPoints'])->name('panel.loyalty.add');
        Route::post('/sadakat/musteri/{customer_id}/kullan', [LoyaltyController::class, 'redeemPoints'])->name('panel.loyalty.redeem');

        Route::get('/pazarlama', [MarketingController::class, 'index'])->name('panel.marketing.index');
        Route::get('/pazarlama/kampanya/yeni', [MarketingController::class, 'createCampaign'])->name('panel.marketing.campaign.create');
        Route::post('/pazarlama/kampanya', [MarketingController::class, 'storeCampaign'])->name('panel.marketing.campaign.store');
        Route::post('/pazarlama/kampanya/{id}/gonder', [MarketingController::class, 'sendCampaign'])->name('panel.marketing.campaign.send');
        Route::delete('/pazarlama/kampanya/{id}', [MarketingController::class, 'destroyCampaign'])->name('panel.marketing.campaign.destroy');
        Route::post('/pazarlama/kupon', [MarketingController::class, 'storeCoupon'])->name('panel.marketing.coupon.store');
        Route::post('/pazarlama/kupon/{id}/toggle', [MarketingController::class, 'toggleCoupon'])->name('panel.marketing.coupon.toggle');
        Route::delete('/pazarlama/kupon/{id}', [MarketingController::class, 'destroyCoupon'])->name('panel.marketing.coupon.destroy');

        Route::get('/subeler', [BranchController::class, 'index'])->name('panel.branches.index');
        Route::post('/subeler', [BranchController::class, 'store'])->name('panel.branches.store');
        Route::put('/subeler/{id}', [BranchController::class, 'update'])->name('panel.branches.update');
        Route::delete('/subeler/{id}', [BranchController::class, 'destroy'])->name('panel.branches.destroy');

        Route::get('/crm', [CrmController::class, 'index'])->name('panel.crm.index');
        Route::get('/crm/musteriler', [CrmController::class, 'customers'])->name('panel.crm.customers');
        Route::post('/crm/etiket', [CrmController::class, 'storeTag'])->name('panel.crm.tags.store');
        Route::delete('/crm/etiket/{id}', [CrmController::class, 'destroyTag'])->name('panel.crm.tags.destroy');
        Route::post('/crm/musteri/{customer_id}/etiket', [CrmController::class, 'updateCustomerTags'])->name('panel.crm.customer.tags');
        Route::post('/crm/musteri/{customer_id}/not', [CrmController::class, 'addNote'])->name('panel.crm.customer.note');

        Route::get('/danisan/{customer_id}', [ClientFileController::class, 'show'])->name('panel.client-files.show');
        Route::put('/danisan/{customer_id}', [ClientFileController::class, 'updateFile'])->name('panel.client-files.update');
        Route::post('/danisan/{customer_id}/not', [ClientFileController::class, 'storeNote'])->name('panel.client-files.notes.store');
        Route::delete('/danisan/{customer_id}/not/{note_id}', [ClientFileController::class, 'destroyNote'])->name('panel.client-files.notes.destroy');

        Route::get('/bekleme', [WaitingListController::class, 'index'])->name('panel.waiting.index');
        Route::post('/bekleme', [WaitingListController::class, 'store'])->name('panel.waiting.store');
        Route::post('/bekleme/{id}/bilgilendir', [WaitingListController::class, 'notify'])->name('panel.waiting.notify');
        Route::post('/bekleme/{id}/randevu', [WaitingListController::class, 'book'])->name('panel.waiting.book');
        Route::post('/bekleme/{id}/iptal', [WaitingListController::class, 'cancel'])->name('panel.waiting.cancel');

        Route::get('/destek', [SupportController::class, 'index'])->name('panel.support.index');
        Route::get('/faturalar', [\App\Http\Controllers\Panel\InvoiceController::class, 'index'])->name('panel.invoices.index');
        Route::get('/faturalar/{id}/goruntule', [\App\Http\Controllers\Panel\InvoiceController::class, 'show'])->name('panel.invoices.show');
        Route::get('/faturalar/{id}/indir', [\App\Http\Controllers\Panel\InvoiceController::class, 'download'])->name('panel.invoices.download');
        Route::get('/destek/yeni', [SupportController::class, 'create'])->name('panel.support.create');
        Route::post('/destek', [SupportController::class, 'store'])->name('panel.support.store');
        Route::get('/destek/{id}', [SupportController::class, 'show'])->name('panel.support.show');
        Route::post('/destek/{id}/yanitla', [SupportController::class, 'reply'])->name('panel.support.reply');
        Route::post('/destek/{id}/kapat', [SupportController::class, 'close'])->name('panel.support.close');

        Route::get('/raporlar', [ReportController::class, 'index'])->name('panel.reports.index');
        Route::get('/abonelik', [SubscriptionController::class, 'index'])->name('panel.subscription.index');
        Route::post('/abonelik/checkout', [\App\Http\Controllers\Panel\CheckoutController::class, 'redirect'])->name('panel.checkout');
        Route::get('/ayarlar', [SettingsController::class, 'index'])->name('panel.settings.index');
        Route::put('/ayarlar/isletme', [SettingsController::class, 'updateBusiness'])->name('panel.settings.business');
        Route::put('/ayarlar/sube', [SettingsController::class, 'updateBranch'])->name('panel.settings.branch');
        Route::put('/ayarlar/sifre', [SettingsController::class, 'updatePassword'])->name('panel.settings.password');

    });

});

Route::get('/{tenant_slug}/abonelik-suresi-doldu', function ($tenant_slug) {
    return "Aboneliginizin suresi doldu. (Firma: {$tenant_slug})";
})->name('subscription.expired');

Route::post('/webhooks/lemonsqueezy', [\App\Http\Controllers\Webhooks\LemonSqueezyWebhookController::class, 'handle'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
