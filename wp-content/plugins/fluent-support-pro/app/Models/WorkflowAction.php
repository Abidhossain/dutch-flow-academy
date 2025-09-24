<?php

namespace FluentSupportPro\App\Models;

use FluentSupport\App\Models\Model;

class WorkflowAction extends Model
{
    protected $table = 'fs_workflow_actions';

    protected $fillable = ['action_name', 'title', 'workflow_id', 'settings'];

    public function setSettingsAttribute($settings)
    {
        $this->attributes['settings'] = \maybe_serialize($settings);
    }

    public function getSettingsAttribute($value)
    {
        return \maybe_unserialize($this->attributes['settings']);
    }

}