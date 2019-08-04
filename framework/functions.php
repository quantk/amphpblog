<?php

namespace QuantFrame;

function root_path(string $path = ''): string
{
    return __DIR__ . '/../' . $path;
}

function config_path(string $path = ''): string
{
    return root_path('config') . '/' . $path;
}

function app_env(): string
{
    return getenv('APP_ENV') ?: 'local';
}

function is_production(): bool
{
    return app_env() === 'production';
}