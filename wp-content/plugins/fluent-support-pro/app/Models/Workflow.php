<?php

namespace FluentSupportPro\App\Models;

use FluentSupport\App\Models\Model;

class Workflow extends Model
{
    protected $table = 'fs_workflows';

    protected $fillable = ['title', 'trigger_key', 'trigger_type', 'created_by', 'settings', 'status', 'last_ran_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = get_current_user_id();
            $model->status = 'draft';
        });
    }

    public function setSettingsAttribute($settings)
    {
        $this->attributes['settings'] = \maybe_serialize($settings);
    }

    public function getSettingsAttribute($value)
    {
        return \maybe_unserialize($this->attributes['settings']);
    }

}