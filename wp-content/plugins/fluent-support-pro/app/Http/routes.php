<?php

/**
 * @var $app FluentSupport\Framework\Foundation\Application
 * @var $router FluentSupport\Framework\Http\Router
 */

$router->prefix('ticket-tags')->withPolicy('FluentSupport\App\Http\Policies\AdminSettingsPolicy')->group(function ($router) {
    $router->get('/', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@index');
    $router->post('/', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@create');
    $router->get('/{tag_id}', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@get')->int('tag_id');
    $router->post('/{tag_id}', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@create')->int('tag_id');
    $router->put('/{tag_id}', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@update')->int('tag_id');
    $router->delete('/{tag_id}', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@delete')->int('tag_id');

    $router->get('/options', 'FluentSupportPro\App\Http\Controllers\TicketTagsController@getOptions');

});

$router->prefix('saved-replies')->withPolicy('FluentSupport\App\Http\Policies\AgentTicketPolicy')->group(function ($router) {
    $router->get('/', 'FluentSupportPro\App\Http\Controllers\SavedRepliesController@index');
    $router->post('/', 'FluentSupportPro\App\Http\Controllers\SavedRepliesController@create');
    $router->get('/{id}', 'FluentSupportPro\App\Http\Controllers\SavedRepliesController@get');
    $router->put('/{id}', 'FluentSupportPro\App\Http\Controllers\SavedRepliesController@update');
    $router->delete('/{id}', 'FluentSupportPro\App\Http\Controllers\SavedRepliesController@delete');
});

$router->prefix('ticket-custom-fields')->withPolicy('FluentSupport\App\Http\Policies\AdminSettingsPolicy')->group(function ($router) {
    $router->get('/', 'FluentSupportPro\App\Http\Controllers\CustomFieldsController@index');
    $router->post('/', 'FluentSupportPro\App\Http\Controllers\CustomFieldsController@store');
    $router->post('/{ticket_id}/sync', 'FluentSupportPro\App\Http\Controllers\CustomFieldsController@syncTicketData')->int('ticket_id');
});

$router->prefix('workflows')->withPolicy('FluentSupportPro\App\Http\Policies\WorkflowPolicy')->group(function ($router) {
    $router->get('/', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@index');
    $router->post('/', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@create');
    $router->get('/{workflow_id}', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@getWorkflow')->int('workflow_id');
    $router->post('/{workflow_id}', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@updateWorkflow')->int('workflow_id');
    $router->delete('/{workflow_id}', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@deleteWorkflow')->int('workflow_id');
    $router->get('/{workflow_id}/actions', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@getWorkflowActions')->int('workflow_id');
    $router->post('/{workflow_id}/run', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@runWorkFlow')->int('workflow_id');
    $router->post('/duplicate/{workflow_id}', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@duplicateWorkflow')->int('workflow_id');
    $router->get('/options', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@getOptions');
    $router->post('/action-sequence', 'FluentSupportPro\App\Http\Controllers\WorkflowsController@updateActionSequence');
});

$router->prefix('email-box')->withPolicy('FluentSupport\App\Http\Policies\AdminSettingsPolicy')->group(function ($router) {
    $router->get('/{box_id}/status', 'FluentSupportPro\App\Http\Controllers\EmailBoxController@getPipeStatus')->int('box_id');
    $router->post('/{box_id}/issue-email', 'FluentSupportPro\App\Http\Controllers\EmailBoxController@issueMappedEmail')->int('box_id');
});

$router->prefix('mail-piping')->withPolicy('FluentSupport\App\Http\Policies\PublicPolicy')->group(function ($router) {
    $router->any('/{box_id}/push/{token}', 'FluentSupportPro\App\Http\Controllers\EmailBoxController@pipePayload')->int('box_id')->alphaNumDash('token');
});

$router->get('customer-portal/search-doc', 'FluentSupportPro\App\Http\Controllers\DocSuggestionController@index')->withPolicy('FluentSupport\App\Http\Policies\PublicPolicy');

/*
 * Pro EndPoints
 */
$router->prefix('pro')->withPolicy('FluentSupport\App\Http\Policies\AdminSettingsPolicy')->group(function ($router) {

    $router->get('license', 'FluentSupportPro\App\Http\Controllers\LicenseController@getStatus');
    $router->post('license', 'FluentSupportPro\App\Http\Controllers\LicenseController@saveLicense');
    $router->post('remove-license', 'FluentSupportPro\App\Http\Controllers\LicenseController@deactivateLicense');

    $router->get('form-settings', 'FluentSupportPro\App\Http\Controllers\TicketFormController@getSettings');
    $router->post('form-settings', 'FluentSupportPro\App\Http\Controllers\TicketFormController@saveSettings');
});

$router->prefix('settings')->withPolicy('FluentSupport\App\Http\Policies\AdminSettingsPolicy')->group(function ($router) {
	$router->get('/discord-integration', 'FluentSupportPro\App\Http\Controllers\DiscordController@getSettings');
	$router->post('/discord-integration', 'FluentSupportPro\App\Http\Controllers\DiscordController@saveSettings');
    $router->get('/incoming-webhook', 'FluentSupportPro\App\Http\Controllers\IncomingWebhookController@index');
    $router->put('/incoming-webhook', 'FluentSupportPro\App\Http\Controllers\IncomingWebhookController@updateWebhook');
    $router->get('/twilio-integration', 'FluentSupportPro\App\Http\Controllers\TwilioController@getSettings');
    $router->post('/twilio-integration', 'FluentSupportPro\App\Http\Controllers\TwilioController@saveSettings');

    $router->get('/auto-close', 'FluentSupportPro\App\Http\Controllers\AutoCloseController@getSettings');
    $router->post('/auto-close', 'FluentSupportPro\App\Http\Controllers\AutoCloseController@saveSettings');

    $router->get('/upload_integration', 'FluentSupportPro\App\Http\Controllers\UploadIntegrationController@getSettings');
    $router->post('/upload_integration', 'FluentSupportPro\App\Http\Controllers\UploadIntegrationController@saveSettings');
    $router->delete('/upload_integration', 'FluentSupportPro\App\Http\Controllers\UploadIntegrationController@deleteSettings');
});

$router->prefix('public')->withPolicy('FluentSupport\App\Http\Policies\PublicPolicy')->group(function($router) {

    $router->post('incoming_webhook/{token}', 'FluentSupportPro\App\Hooks\Handlers\IncomingWebhookHandler@handleIncomingWebhook')->alphaNumDash('token');
    $router->post('/twilio-response/{token}', 'FluentSupportPro\App\Http\Controllers\TwilioController@handleResponse')->alphaNumDash('token');
    $router->get('/dropbox_auth', 'FluentSupportPro\App\Http\Controllers\AuthorizeController@handleAuthorizeDropbox');
    $router->get('/google_auth', 'FluentSupportPro\App\Http\Controllers\AuthorizeController@handleAuthorizeGoogleDrive');

});

$router->prefix('tickets')->withPolicy('FluentSupport\App\Http\Policies\AgentTicketPolicy')->group(function ($router) {
    $router->post('/{ticket_id}/sync-watchers', 'FluentSupportPro\App\Http\Controllers\TicketController@syncTicketWatchers')->int('ticket_id');
    $router->post('/{ticket_id}/add_watchers', 'FluentSupportPro\App\Http\Controllers\TicketController@addTicketWatchers')->int('ticket_id');
    $router->post('/{ticket_id}/merge_tickets', 'FluentSupportPro\App\Http\Controllers\TicketController@mergeCustomerTickets')->int('ticket_id');
    $router->get('customer_tickets/{customer_id}', 'FluentSupportPro\App\Http\Controllers\TicketController@getCustomerTickets')->int('customer_id');
    $router->post('/{ticket_id}/split_ticket', 'FluentSupportPro\App\Http\Controllers\TicketController@splitToNewTicket')->int('ticket_id');
});

$router->prefix('time-tracks')->withPolicy('FluentSupport\App\Http\Policies\AgentTicketPolicy')->group(function ($router) {
    $router->post('/{ticket_id}/estimated-time', 'FluentSupportPro\App\Http\Controllers\TimeTrackController@updateEstimatedTime')->int('ticket_id');
    $router->get('/{ticket_id}', 'FluentSupportPro\App\Http\Controllers\TimeTrackController@getTracks')->int('ticket_id');
    $router->post('/{ticket_id}', 'FluentSupportPro\App\Http\Controllers\TimeTrackController@manualCommitTrack')->int('ticket_id');
});

$router->prefix('openai')->withPolicy('FluentSupport\App\Http\Policies\AgentTicketPolicy')->group(function ($router) {
    $router->get('/preset-prompts', 'FluentSupportPro\App\Http\Controllers\OpenAIController@getPresetPrompts')->int('id');
    $router->post('/{id}/generate-response', 'FluentSupportPro\App\Http\Controllers\OpenAIController@generateResponse')->int('id');
    $router->post('/{id}/get-ticket-summary', 'FluentSupportPro\App\Http\Controllers\OpenAIController@getTicketSummary')->int('id');
    $router->post('/{id}/get-ticket-tone', 'FluentSupportPro\App\Http\Controllers\OpenAIController@getTicketTone')->int('id');
});

$router->prefix('reports')->withPolicy('FluentSupport\App\Http\Policies\ReportPolicy')->group(function ($router) {
    $router->get('timesheet/by-tickets', 'FluentSupportPro\App\Http\Controllers\ReportingController@getTimesheetByTickets');
    $router->get('timesheet/by-agents', 'FluentSupportPro\App\Http\Controllers\ReportingController@getTimesheetByAgents');
    $router->get('timesheet/by-customers', 'FluentSupportPro\App\Http\Controllers\ReportingController@getTimesheetByCustomers');
});
