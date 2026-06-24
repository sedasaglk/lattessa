<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lattessa - Salon ve Klinik Yonetim Yazilimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

{{-- NAVBAR --}}
<nav class="border-b border-gray-100 px-6 py-4 sticky top-0 bg-white/95 backdrop-blur z-50">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <span class="text-xl font-semibold">Lattessa</span>
        <div class="hidden md:flex items-center gap-8">
            <a href="#ozellikler" class="text-sm text-gray-600 hover:text-gray-900">Ozellikler</a>
            <a href="#fiyatlandirma" class="text-sm text-gray-600 hover:text-gray-900">Fiyatlandirma</a>
            <a href="#sektorler" class="text-sm text-gray-600 hover:text-gray-900">Sektorler</a>
        </div>
        <a href="/kayit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition font-medium">
            Ucretsiz Baslat
        </a>
    </div>
</nav>

{{-- HERO --}}
<section class="max-w-6xl mx-auto px-6 pt-20 pb-24 text-center">
    <div class="inline-block bg-amber-50 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full mb-6">
        14 gun ucretsiz deneme — kredi karti gerekmez
    </div>
    <h1 class="text-5xl md:text-6xl font-semibold text-gray-900 leading-tight mb-6">
        Salonunuzu<br>
        <span class="text-gray-400">dijitale tasiyin</span>
    </h1>
    <p class="text-xl text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
        Randevu, musteri, personel, kasa ve raporlarinizi tek platformda yonetin.
        Online randevu sayfaniz aninda hazir.
    </p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="/kayit" class="bg-gray-900 text-white px-8 py-3.5 rounded-xl font-medium text-base hover:bg-gray-800 transition w-full sm:w-auto">
            Hemen Ucretsiz Baslat
        </a>
        <a href="#ozellikler" class="border border-gray-200 text-gray-700 px-8 py-3.5 rounded-xl font-medium text-base hover:bg-gray-50 transition w-full sm:w-auto">
            Ozellikleri Incele
        </a>
    </div>
    <p class="text-sm text-gray-400 mt-4">14 gun deneme • Kredi karti gerekmez • Iptal kolayligi</p>
</section>

{{-- MOCK PANEL --}}
<section class="max-w-5xl mx-auto px-6 pb-24">
    <div class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden">
        <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
            <div class="flex gap-1.5">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
            </div>
            <div class="flex-1 bg-gray-100 rounded-md px-3 py-1 text-xs text-gray-400">
                lattessa.com/sizin-salonunuz
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-4 gap-3 mb-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Bugunun Randevusu</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">12</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Toplam Musteri</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">248</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Aylik Ciro</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">18.500 TL</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-xs text-gray-500">Aktif Hizmet</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">8</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-gray-900">Bugunun Randevulari</p>
                    <span class="text-xs text-gray-400">17 Haziran 2026</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900 w-12">09:00</span>
                        <div>
                            <p class="text-sm text-gray-900">Ayse Kaya</p>
                            <p class="text-xs text-gray-400">Sac Kesimi • Zeynep H.</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Onaylandi</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900 w-12">10:30</span>
                        <div>
                            <p class="text-sm text-gray-900">Fatma Demir</p>
                            <p class="text-xs text-gray-400">Boya • Ayse T.</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Onaylandi</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900 w-12">11:00</span>
                        <div>
                            <p class="text-sm text-gray-900">Elif Sahin</p>
                            <p class="text-xs text-gray-400">Manikur • Zeynep H.</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Bekliyor</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900 w-12">14:00</span>
                        <div>
                            <p class="text-sm text-gray-900">Selin Yilmaz</p>
                            <p class="text-xs text-gray-400">Cilt Bakimi • Ayse T.</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Tamamlandi</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- SEKTORLER --}}
<section id="sektorler" class="bg-gray-50 py-20">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">Her sektore ozel</h2>
            <p class="text-gray-500">Hangi alanda hizmet verirseniz verin, Lattessa size uygun</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Kuafor</p>
                <p class="text-xs text-gray-500">Randevu, musteri ve kasa yonetimi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Berber</p>
                <p class="text-xs text-gray-500">Hizli randevu ve personel takibi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Guzellik Merkezi</p>
                <p class="text-xs text-gray-500">Paket satis ve sadakat programi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Spa</p>
                <p class="text-xs text-gray-500">Online rezervasyon ve raporlama</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Estetik Klinik</p>
                <p class="text-xs text-gray-500">Danisan dosyasi ve seans takibi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Diyetisyen</p>
                <p class="text-xs text-gray-500">Danisan yonetimi ve program takibi</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Psikolog</p>
                <p class="text-xs text-gray-500">Seans planlama ve notlar</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition">
                <p class="font-medium text-gray-900 mb-1">Klinik</p>
                <p class="text-xs text-gray-500">Hasta kaydi ve randevu sistemi</p>
            </div>
        </div>
    </div>
</section>

{{-- OZELLIKLER --}}
<section id="ozellikler" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">Her seyi tek platformda</h2>
            <p class="text-gray-500 max-w-xl mx-auto">Birden fazla uygulama kullanmaya son. Lattessa ile isletmenizin tum ihtiyaclari tek yerde.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl mb-4">📅</div>
                <h3 class="font-semibold text-gray-900 mb-2">Online Randevu</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Musteri randevularini 7/24 online alabilir. Otomatik onay ve hatirlatma.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-xl mb-4">👥</div>
                <h3 class="font-semibold text-gray-900 mb-2">Musteri Yonetimi</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Musteri gecmisi, harcama takibi ve notlar. Hizmet kalitesini artirin.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-xl mb-4">👤</div>
                <h3 class="font-semibold text-gray-900 mb-2">Personel Takibi</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Mesai, izin, prim ve performans yonetimi tek ekrandan.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl mb-4">💰</div>
                <h3 class="font-semibold text-gray-900 mb-2">Kasa ve Finans</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Gelir, gider ve net bakiye takibi. Odeme yontemine gore filtreleme.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-red-50 text-red-600 rounded-lg flex items-center justify-center text-xl mb-4">📊</div>
                <h3 class="font-semibold text-gray-900 mb-2">Detayli Raporlar</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Gunluk, haftalik, aylik ciro raporlari. Personel ve hizmet performansi.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-sm transition">
                <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center text-xl mb-4">🗓️</div>
                <h3 class="font-semibold text-gray-900 mb-2">Takvim Gorunumu</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Gunluk, haftalik ve aylik takvim. Tek tikla yeni randevu olusturma.</p>
            </div>
        </div>
    </div>
</section>

{{-- FIYATLANDIRMA --}}
<section id="fiyatlandirma" class="bg-gray-50 py-20">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">Seffaf fiyatlandirma</h2>
            <p class="text-gray-500">Gizli ucret yok. Istediginiz zaman iptal edebilirsiniz.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Baslangic --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 mb-1">Baslangic</p>
                <p class="text-3xl font-semibold text-gray-900">299 TL<span class="text-base text-gray-400 font-normal">/ay</span></p>
                <p class="text-xs text-gray-400 mt-1">Yillik odeme ile 2.990 TL</p>
                <hr class="my-4 border-gray-100">
                <ul class="space-y-2.5 text-sm text-gray-600 mb-6">
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> 5 personel</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> 1 sube</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> 100 SMS/ay</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Randevu ve CRM</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Online randevu sayfasi</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Temel raporlar</li>
                </ul>
                <a href="/kayit" class="block w-full text-center border border-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                    14 Gun Dene
                </a>
            </div>

            {{-- Profesyonel --}}
            <div class="bg-gray-900 rounded-2xl p-6 relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-gray-900 text-xs font-semibold px-3 py-1 rounded-full">
                    En Populer
                </div>
                <p class="text-sm font-medium text-gray-400 mb-1">Profesyonel</p>
                <p class="text-3xl font-semibold text-white">799 TL<span class="text-base text-gray-400 font-normal">/ay</span></p>
                <p class="text-xs text-gray-500 mt-1">Yillik odeme ile 7.990 TL</p>
                <hr class="my-4 border-gray-700">
                <ul class="space-y-2.5 text-sm text-gray-300 mb-6">
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> 20 personel</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> 5 sube</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> 500 SMS/ay</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> Tum moduller</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> Sadakat programi</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> Pazarlama araclari</li>
                    <li class="flex items-center gap-2"><span class="text-green-400">✓</span> Gelismis raporlar</li>
                </ul>
                <a href="/kayit" class="block w-full text-center bg-white text-gray-900 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-100 transition">
                    14 Gun Dene
                </a>
            </div>

            {{-- Kurumsal --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 mb-1">Kurumsal</p>
                <p class="text-3xl font-semibold text-gray-900">1.999 TL<span class="text-base text-gray-400 font-normal">/ay</span></p>
                <p class="text-xs text-gray-400 mt-1">Yillik odeme ile 19.990 TL</p>
                <hr class="my-4 border-gray-100">
                <ul class="space-y-2.5 text-sm text-gray-600 mb-6">
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Sinirsiz personel</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Sinirsiz sube</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> 2000 SMS/ay</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Tum moduller</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Affiliate sistemi</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Oncelikli destek</li>
                    <li class="flex items-center gap-2"><span class="text-green-500">✓</span> Ozel entegrasyonlar</li>
                </ul>
                <a href="/kayit" class="block w-full text-center border border-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                    14 Gun Dene
                </a>
            </div>

        </div>
    </div>
</section>

{{-- SSS --}}
<section class="py-20">
    <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-3xl font-semibold text-gray-900 text-center mb-12">Sik sorulan sorular</h2>
        <div class="space-y-4">
            <details class="border border-gray-200 rounded-xl p-5 group">
                <summary class="font-medium text-gray-900 cursor-pointer list-none flex items-center justify-between">
                    14 gun deneme gercekten ucretsiz mi?
                    <span class="text-gray-400">↓</span>
                </summary>
                <p class="text-gray-500 text-sm mt-3 leading-relaxed">Evet, kredi karti gerekmez. 14 gun boyunca tum ozelliklere erisin. Sure dolunca hesabiniz askiya alinir, verileriniz silinmez.</p>
            </details>
            <details class="border border-gray-200 rounded-xl p-5">
                <summary class="font-medium text-gray-900 cursor-pointer list-none flex items-center justify-between">
                    Verilerimi kaybeder miyim?
                    <span class="text-gray-400">↓</span>
                </summary>
                <p class="text-gray-500 text-sm mt-3 leading-relaxed">Hayir. Abonelik sonlandiginda bile verileriniz sistemde saklanir. Paket aldiginizda kaldigi yerden devam edersiniz.</p>
            </details>
            <details class="border border-gray-200 rounded-xl p-5">
                <summary class="font-medium text-gray-900 cursor-pointer list-none flex items-center justify-between">
                    Kac sube ekleyebilirim?
                    <span class="text-gray-400">↓</span>
                </summary>
                <p class="text-gray-500 text-sm mt-3 leading-relaxed">Baslangic paketinde 1 sube, Profesyonel pakette 5 sube, Kurumsal pakette sinirsiz sube ekleyebilirsiniz.</p>
            </details>
            <details class="border border-gray-200 rounded-xl p-5">
                <summary class="font-medium text-gray-900 cursor-pointer list-none flex items-center justify-between">
                    SMS hatirlatmalar nasil calisiyor?
                    <span class="text-gray-400">↓</span>
                </summary>
                <p class="text-gray-500 text-sm mt-3 leading-relaxed">Randevu olusturulunca ve randevudan once otomatik SMS gonderilir. SMS limiti paketinize gore belirlenir.</p>
            </details>
            <details class="border border-gray-200 rounded-xl p-5">
                <summary class="font-medium text-gray-900 cursor-pointer list-none flex items-center justify-between">
                    Istedigim zaman iptal edebilir miyim?
                    <span class="text-gray-400">↓</span>
                </summary>
                <p class="text-gray-500 text-sm mt-3 leading-relaxed">Evet, herhangi bir taahhut yoktur. Istediginiz zaman iptal edebilirsiniz.</p>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-gray-900 py-20">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-semibold text-white mb-4">Bugun baslayin</h2>
        <p class="text-gray-400 mb-8">14 gun ucretsiz deneyin. Kredi karti gerekmez.</p>
        <a href="/kayit" class="inline-block bg-white text-gray-900 px-8 py-3.5 rounded-xl font-medium text-base hover:bg-gray-100 transition">
            Ucretsiz Hesap Olustur
        </a>
    </div>
</section>

{{-- FOOTER --}}
<footer class="border-t border-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <span class="font-semibold text-gray-900">Lattessa</span>
        <div class="flex items-center gap-6 text-sm text-gray-500">
            <a href="#ozellikler" class="hover:text-gray-900">Ozellikler</a>
            <a href="#fiyatlandirma" class="hover:text-gray-900">Fiyatlandirma</a>
            <a href="mailto:destek@lattessa.com" class="hover:text-gray-900">Destek</a>
        </div>
        <p class="text-sm text-gray-400">2026 Lattessa. Tum haklari saklidir.</p>
    </div>
</footer>

</body>
</html>
