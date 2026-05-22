<?php

namespace App\Traits;

use App\Models\HistoryStor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasHistory
{
    protected static function bootHasHistory()
    {
        static::created(function (Model $model) {
            self::logHistory($model, 'created', null, $model->toArray());
        });

        static::updated(function (Model $model) {
            $changes = $model->getDirty();
            $dataBefore = $model->getOriginal();
            $dataAfter = array_merge($dataBefore, $changes);

            self::logHistory($model, 'updated', $dataBefore, $dataAfter);
        });

        static::deleted(function (Model $model) {
            self::logHistory($model, 'deleted', $model->toArray(), null);
        });
    }

    protected static function logHistory(Model $model, string $actionType, ?array $dataBefore, ?array $dataAfter)
    {
        $userId = Auth::id() ?? 1; // افتراضيًا المستخدم 1 إذا لم يكن هناك مستخدم مسجل
        HistoryStor::create([
            'user_id' => $userId,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action_type' => $actionType,
            'data_before' => $dataBefore ? json_encode($dataBefore) : null,
            'data_after' => $dataAfter ? json_encode($dataAfter) : null,
        ]);
    }
}
