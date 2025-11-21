<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Bithoven\LLMManager\Models\LLMConfiguration;

// Admin > LLM > Models > {Model}
Breadcrumbs::for('admin.llm.models.show', function (BreadcrumbTrail $trail, LLMConfiguration $model) {
    $trail->parent('admin.llm.configurations.index');
    $trail->push($model->name, route('admin.llm.models.show', $model));
});

// Admin > LLM > Streaming Test
Breadcrumbs::for('admin.llm.stream.test', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.llm.dashboard');
    $trail->push('Streaming Test', route('admin.llm.stream.test'));
});
