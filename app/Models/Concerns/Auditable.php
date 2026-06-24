<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            // Sadece onemli alanlari kaydet, timestamp'leri atla
            unset($changes['updated_at'], $changes['last_login_at']);
            if (!empty($changes)) {
                $old = array_intersect_key($model->getOriginal(), $changes);
                self::logAudit('updated', $model, $old, $changes);
            }
        });

        static::deleted(function ($model) {
            self::logAudit('deleted', $model, $model->getOriginal(), null);
        });
    }

    protected static function logAudit(string $action, $model, ?array $old, ?array $new): void
    {
        try {
            // Sifre gibi hassas alanlari gizle
            $sensitiveFields = ['password', 'remember_token', 'two_factor_secret'];
            if ($old) {
                foreach ($sensitiveFields as $field) {
                    if (isset($old[$field])) $old[$field] = '***';
                }
            }
            if ($new) {
                foreach ($sensitiveFields as $field) {
                    if (isset($new[$field])) $new[$field] = '***';
                }
            }

            DB::table('audit_logs')->insert([
                'tenant_id' => $model->tenant_id ?? null,
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => class_basename($model),
                'model_id' => $model->id,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Audit log hatasi ana islemi engellemesin
            \Illuminate\Support\Facades\Log::warning('Audit log hatasi: ' . $e->getMessage());
        }
    }
}
