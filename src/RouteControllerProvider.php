<?php

namespace Grummfy\LaravelRouteController;

use Illuminate\Support\ServiceProvider;

class RouteControllerProvider extends ServiceProvider
{
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../config/route-controller.php' => config_path('route-controller.php'),
		], 'config');
	}

	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/route-controller.php', 'route-controller');

		$controllerRouterClass = $this->app['config']->get('route-controller.controller_router');
		$appBaseController = $this->app['config']->get('route-controller.app_base_controller');

		$this->_registerMacro($controllerRouterClass, $appBaseController);
	}

	protected function _registerMacro($controllerRouterClass, $appBaseController)
	{
		\Route::macro(
			'controller',
			function($uri, $controller, array $options = array()) use ($controllerRouterClass, $appBaseController)
			{
				$namespace = '';

				// first we check namespace and prefix for the roads
				// get prefix on route by group
				if (!empty($this->groupStack))
				{
					$group = end($this->groupStack);
					$namespace = isset($group[ 'namespace' ]) ? ($group[ 'namespace' ] . '\\') : $namespace;
				}

				$fullControllerName = $controller;
				if (!class_exists($controller) && strpos($controller, '\\') !== 0)
				{
					$fullControllerName = $namespace . $controller;
				}

				$heritage = false;
				if (isset($options['heritage']))
				{
					$heritage = $options['heritage'];
				}

				$cr = new $controllerRouterClass($heritage, $appBaseController);
				$routables = $cr->listRoutableActionFromController($fullControllerName);

				foreach ($routables as $actionController => $potentialRoute)
				{
					// inherited : middleware, prefix, ...
					$action = $options + [
							'uses' => $actionController,
							'as' => $potentialRoute[ 'name' ],
						];

					if (method_exists($this, $potentialRoute[ 'verb' ]))
					{
						$this->{$potentialRoute[ 'verb' ]}($uri . $potentialRoute[ 'uri' ], $action);
					}
				}
			}
		);
	}
}
