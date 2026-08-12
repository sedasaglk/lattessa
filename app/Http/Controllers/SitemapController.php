<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $staticUrls = [
            ['loc' => 'https://lattessa.com/',                   'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => 'https://lattessa.com/kayit',             'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => 'https://lattessa.com/giris',             'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => 'https://lattessa.com/gizlilik',          'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => 'https://lattessa.com/kullanim-sartlari', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $tenants = DB::table('tenants')
            ->where('status', 'active')
            ->whereNotNull('slug')
            ->select('slug', 'updated_at')
            ->get();

        $tenantUrls = [];
        foreach ($tenants as $tenant) {
            $tenantUrls[] = [
                'loc'        => 'https://lattessa.com/' . $tenant->slug . '/randevu',
                'lastmod'    => substr($tenant->updated_at ?? now()->toIso8601String(), 0, 10),
                'priority'   => '0.6',
                'changefreq' => 'weekly',
            ];
        }

        $xml = view('sitemap', [
            'staticUrls' => $staticUrls,
            'tenantUrls' => $tenantUrls,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
