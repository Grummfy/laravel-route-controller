<?php

namespace Grummfy\LaravelRouteController;

use Illuminate\Support\ServiceProvider;

class RouteControllerProvider extends ServiceProvider
{
	protected $_controllerRouterClass = \Grummfy\LaravelRouteController\Route\ControllerRouter::class;

	public function register()
	{
		$controllerRouterClass = $this->_controllerRouterClass;

		\Router::macro(
			'controller',
			function($uri, $controller, array $options = array()) use ($controllerRouterClass)
			{
				$fullControllerName = $controller;
				$namespace = '';

				// first we check namespace and prefix for the roads
				// get prefix on route by group
				if (!empty($this->groupStack))
				{
					$group = end($this->groupStack);
					$namespace = isset($group[ 'namespace' ]) ? ($group[ 'namespace' ] . '\\') : $namespace;
				}

				if (!class_exists($controller) && strpos($controller, '\\') !== 0)
				{
					$fullControllerName = $namespace . $controller;
				}

				$heritage = false;
				if (isset($options['heritage']))
				{
					$heritage = $options['heritage'];
				}

				$cr = new ($controllerRouterClass)($heritage);
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
