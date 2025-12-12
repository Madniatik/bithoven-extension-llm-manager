<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Bithoven\LLMManager\Models\LLMProviderConfiguration;

// Admin > LLM Manager > Models > {model}
Breadcrumbs::for('admin.llm.models.show', function (BreadcrumbTrail $trail, LLMProviderConfiguration $model) {
    $trail->parent('admin.llm.configurations.index');
    $trail->push($model->name, route('admin.llm.models.show', $model));
});

// Admin > LLM > Streaming Test
Breadcrumbs::for('admin.llm.stream.test', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.llm.dashboard');
    $trail->push('Streaming Test', route('admin.llm.stream.test'));
});

// Admin > LLM > Activity
Breadcrumbs::for('admin.llm.activity', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.llm.dashboard');
    $trail->push('Activity Logs', route('admin.llm.activity.index'));
});

// Admin > LLM > Activity > Show
Breadcrumbs::for('admin.llm.activity.show', function (BreadcrumbTrail $trail, $log) {
    $trail->parent('admin.llm.activity');
    $trail->push('Log #' . $log->id, route('admin.llm.activity.show', $log));
});
