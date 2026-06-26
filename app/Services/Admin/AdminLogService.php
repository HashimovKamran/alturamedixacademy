<?php

namespace App\Services\Admin;

use App\Models\AdminLog;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Throwable;

class AdminLogService
{
    public function write(
        Request $request,
        string $module,
        string $action,
        string $description,
        ?string $objectType = null,
        ?int $objectId = null,
    ): void {
        $admin = $request->session()->get('admin_user_id')
            ? AdminUser::query()->find($request->session()->get('admin_user_id'))
            : null;

        if (function_exists('activity')) {
            try {
                $activity = activity($module)
                    ->event($action)
                    ->withProperties([
                        'module' => $module,
                        'action' => $action,
                        'object_type' => $objectType,
                        'object_id' => $objectId,
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 700),
                    ]);

                if ($admin) {
                    $activity->causedBy($admin);
                }

                if ($objectType !== null && $objectId !== null) {
                    $activity->performedOn(new class($objectType, $objectId) extends \Illuminate\Database\Eloquent\Model {
                        public function __construct(private readonly string $activityType, private readonly int $activityId)
                        {
                            parent::__construct();
                            $this->exists = true;
                            $this->setRawAttributes(['id' => $this->activityId], true);
                        }

                        public function getMorphClass()
                        {
                            return $this->activityType;
                        }

                        public function getKey()
                        {
                            return $this->activityId;
                        }
                    });
                }

                $activity->log($description);
            } catch (Throwable) {
                // Spatie activitylog is optional in this project; the local log remains authoritative.
            }
        }

        AdminLog::query()->create([
            'admin_user_id' => $admin?->id,
            'admin_name' => $admin?->full_name,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 700),
            'created_at' => now(),
        ]);
    }
}
