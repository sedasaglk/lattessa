<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationSettingsController extends Controller
{
    // Tüm event tanımları — kod ile label ve default şablon
    public static function eventDefinitions(): array
    {
        return [
            'appointment_confirmation' => [
                'label' => 'Randevu Onayı',
                'desc'  => 'Müşteri randevu aldığında gönderilir.',
                'default' => "Sayin {musteri_adi}, randevunuz alinmistir.\nTarih: {tarih}\nSaat: {saat}\nHizmet: {hizmet_adi}\nPersonel: {personel_adi}\n- {salon_adi}",
                'vars'  => ['{musteri_adi}', '{tarih}', '{saat}', '{hizmet_adi}', '{personel_adi}', '{salon_adi}'],
            ],
            'appointment_reminder_24h' => [
                'label' => 'Hatırlatma (24 saat önce)',
                'desc'  => 'Randevudan 24 saat önce gönderilir.',
                'default' => "Sayin {musteri_adi}, yarin saat {saat} randevunuz var.\nHizmet: {hizmet_adi}\n- {salon_adi}",
                'vars'  => ['{musteri_adi}', '{tarih}', '{saat}', '{hizmet_adi}', '{salon_adi}'],
            ],
            'appointment_reminder_2h' => [
                'label' => 'Hatırlatma (2 saat önce)',
                'desc'  => 'Randevudan 2 saat önce gönderilir.',
                'default' => "Sayin {musteri_adi}, bugun saat {saat} randevunuz var.\nHizmet: {hizmet_adi}\n- {salon_adi}",
                'vars'  => ['{musteri_adi}', '{tarih}', '{saat}', '{hizmet_adi}', '{salon_adi}'],
            ],
            'review_request' => [
                'label' => 'Değerlendirme İsteği',
                'desc'  => 'Randevu tamamlandığında gönderilir.',
                'default' => "Sayin {musteri_adi}, {hizmet_adi} hizmetimizi aldiginiz icin tesekkur ederiz! Deneyiminizi paylasir misiniz? {link}",
                'vars'  => ['{musteri_adi}', '{hizmet_adi}', '{salon_adi}', '{link}'],
            ],
            'birthday' => [
                'label' => 'Doğum Günü Mesajı',
                'desc'  => 'Müşteri doğum günü sabahı gönderilir.',
                'default' => "Sayin {musteri_adi}, dogum gununuz kutlu olsun! Size ozel bir surprizimiz var, bizi arayabilirsiniz. - {salon_adi}",
                'vars'  => ['{musteri_adi}', '{salon_adi}'],
            ],
        ];
    }

    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        $definitions = self::eventDefinitions();

        // Mevcut ayarları çek
        $saved = DB::table('notification_settings')
            ->where('tenant_id', $tenant->id)
            ->get()
            ->keyBy('event');

        // Her event için mevcut ayarı veya default'u birleştir
        $settings = [];
        foreach ($definitions as $event => $def) {
            $s = $saved[$event] ?? null;
            $settings[$event] = [
                'label'    => $def['label'],
                'desc'     => $def['desc'],
                'default'  => $def['default'],
                'vars'     => $def['vars'],
                'enabled'  => $s ? (bool)$s->enabled : true,
                'channel'  => $s ? $s->channel : 'auto',
                'template' => $s ? ($s->template ?? '') : '',
            ];
        }

        return view('panel.notification-settings.index', compact('tenant', 'settings'));
    }

    public function update(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();
        $definitions = self::eventDefinitions();

        foreach ($definitions as $event => $def) {
            $enabled  = $request->boolean("events.{$event}.enabled");
            $channel  = $request->input("events.{$event}.channel", 'auto');
            $template = trim($request->input("events.{$event}.template", ''));

            // Boş şablon = default kullan (null)
            $templateValue = $template ?: null;

            DB::table('notification_settings')->updateOrInsert(
                ['tenant_id' => $tenant->id, 'event' => $event],
                [
                    'enabled'    => $enabled,
                    'channel'    => $channel,
                    'template'   => $templateValue,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return back()->with('success', 'Bildirim ayarları kaydedildi.');
    }
}
