<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteActionIntegrityTest extends TestCase
{
    public function test_every_controller_route_references_an_existing_action(): void
    {
        $brokenActions = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (!str_contains($action, '@')) {
                continue;
            }

            [$controller, $method] = explode('@', $action, 2);
            if (!class_exists($controller) || !method_exists($controller, $method)) {
                $brokenActions[] = sprintf('%s -> %s', $route->uri(), $action);
            }
        }

        $this->assertSame([], $brokenActions, implode(PHP_EOL, $brokenActions));
    }
}
