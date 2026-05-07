<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(
        string $event,
        ?Model $subject = null,
        array $properties = [],
        ?string $description = null,
        ?Model $causer = null,
        ?int $storeId = null,
    ): ActivityLog {
        try {
            $causer ??= auth()->user();

            if (is_null($storeId)) {
                $storeId = $this->resolveStoreId($subject, $causer);
            }

            $ipAddress = request()?->ip();
            $userAgent = request()?->userAgent();

            return ActivityLog::create([
                'event_type' => $event,
                'description' => $description,
                'causer_type' => $causer ? $causer::class : null,
                'causer_id' => $causer?->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->id,
                'store_id' => $storeId,
                'properties' => empty($properties) ? null : $properties,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);
        } catch (\Exception) {
            return new ActivityLog;
        }
    }

    private function resolveStoreId(?Model $subject, ?Model $causer): ?int
    {
        if ($subject) {
            if ($subject instanceof Store) {
                return $subject->id;
            }

            if ($subject->hasAttribute('store_id')) {
                return $subject->store_id;
            }
        }

        if ($causer && $causer instanceof User) {
            return $causer->store_id;
        }

        return null;
    }
}
