<?php

namespace FluentSupportPro\App\Http\Policies;

use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\Framework\Request\Request;
use FluentSupport\Framework\Foundation\Policy;

class WorkflowPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentSupport\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return PermissionManager::currentUserCan('fst_manage_workflows');
    }

    public function getWorkflowActions(Request $request)
    {
        return PermissionManager::currentUserCan('fst_manage_workflows') || PermissionManager::currentUserCan('fst_run_workflows');
    }

    public function runWorkFlow(Request $request)
    {
        return PermissionManager::currentUserCan('fst_manage_workflows') || PermissionManager::currentUserCan('fst_run_workflows');
    }

}
