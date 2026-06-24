<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'company_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'in:kuafor,berber,guzellik_merkezi,diyetisyen,psikolog,spa,estetik,klinik'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_name.required' => 'Ad soyad alanı zorunludur.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'company_name.required' => 'Şirket/işletme adı zorunludur.',
            'business_type.required' => 'İşletme türü seçmelisiniz.',
            'business_type.in' => 'Geçersiz işletme türü.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ];
    }
}
