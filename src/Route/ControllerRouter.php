<?php

namespace Grummfy\LaravelRouteController\Route;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;

/**
 * Inspired by other legacy stuff
 *
 * @see https://github.com/laravel/framework/blob/5.2/src/Illuminate/Routing/ControllerInspector.php
 * @see https://github.com/laravel/framework/blob/5.2/src/Illuminate/Routing/Router.php#L244
 * @see https://gist.github.com/cotcotquedec/df15f9111e5c8d118ac270f6a157c460
 */
class ControllerRouter
{
	/**
	 * An array of HTTP verbs.
	 *
	 * @var array
	 */
	protected $_verbs = [
		'any',
		'get',
		'post',
		'put',
		'patch',
		'delete',
		'head',
		'options',
	];

	/**
	 * The name of index action
	 *
	 * @var string
	 */
	protected $_indexAction = 'index';

	/**
	 * @var string
	 */
	protected $_baseController;

	/**
	 * @var string
	 */
	protected $_illuminateController = 'Illuminate\\Routing\\Controller';

	/**
	 * Skipp inherited methods or not
	 * @var bool
	 */
	protected $_skippInheritedMethods;

	public function __construct(bool $skippInheritedMethods, ?string $baseController = null, ?string $illuminateController = null)
	{
		if ($baseController)
		{
			$this->_baseController = $baseController;
		}

		if ($illuminateController)
		{
			$this->_illuminateController = $illuminateController;
		}

		$this->_skippInheritedMethods = $skippInheritedMethods;
	}

	/**
	 * Get a full routable list from the controller based on method name
	 *
	 * @param string $controllerClass
	 * @param string|null $prefix
	 * @return array
	 * @throws \ReflectionException
	 */
	public function listRoutableActionFromController(string $controllerClass, string $prefix = null): array
	{
		$reflection = new ReflectionClass($controllerClass);
		$controllerName = Str::slug(Str::replaceLast('Controller', '', $reflection->getShortName()));

		$prefix = (empty($prefix) ? '' : ($prefix . '.')) . $controllerName;

		// only public method that start with specific keywords will be loaded
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

		$routable = array();
		foreach ($methods as $method)
		{
			// skipp inherited methods
			if ($this->isMethodSkipped($method, $controllerClass))
			{
				continue;
			}

			$parts = $this->methodToRouteParts($method);

			// check if the method is routable
			if (!$this->isRoutable($method, $parts['verb']))
			{
				continue;
			}

			$parameters = $this->_buildUrlPartParameters($method);
			$routeName = $this->_buildRouteName($prefix, $parts);
			$controllerAction = sprintf('%s@%s', '\\' . $controllerClass, $parts['full']);

			$routable[ $controllerAction ] = $parts + [
					'name' => $routeName,
					'uri' => $this->_buildUrlPartAction($parts) . (empty($parameters) ? '' : ('/' . $parameters)),
				];
		}

		return $routable;
	}

	/**
	 * Determine if the given controller method is routable.
	 */
	public function isRoutable(ReflectionMethod $method, string $verb): bool
	{
		return in_array($verb, $this->_verbs);
	}

	/**
	 * Split the method names into multi-parts
	 */
	public function methodToRouteParts(ReflectionMethod $method): array
	{
		$name = $method->getName();
		$parts = explode('_', Str::snake($name));
		$verb = $parts[0];
		$parts = array_slice($parts, 1);
		if (!in_array($verb, $this->_verbs))
		{
			array_unshift($parts, $verb);
			$verb = 'any';
		}

		return [
			'full' => $name,
			'verb' => $verb,
			'action' => Str::slug(implode('-', $parts)),
		];
	}

	/**
	 * Should we skipp this method
	 */
	public function isMethodSkipped(ReflectionMethod $method, string $controllerClass): bool
	{
		if ($method->class == $this->_illuminateController || ($this->_baseController && $method->class == $this->_baseController))
		{
			return true;
		}

		if ($this->_skippInheritedMethods)
		{
			return $method->getDeclaringClass()->name != $controllerClass;
		}

		return false;
	}

	/**
	 * build the url part for the action name
	 */
	protected function _buildUrlPartAction(array $parts): string
	{
		$isIndex = $parts[ 'action' ] == $this->_indexAction;

		// build uri parts
		$uri = ($isIndex ? '' : '/' . $parts[ 'action' ]);

		return $uri;
	}

	/**
	 * build the url part for the parameter
	 */
	protected function _buildUrlPartParameters(ReflectionMethod $method): string
	{
		$uri = array();
		foreach ($method->getParameters() as $parameter)
		{
			$parameterName = $parameter->getName();
			$isOptional = $parameter->isDefaultValueAvailable() || ($parameter->hasType() && $parameter->getType()->allowsNull());
			$uri[] = sprintf('{%s%s}', $parameterName, $isOptional ? '?' : '');
		}

		return implode('/', $uri);
	}

	/**
	 * @param string $prefix
	 * @param $parts
	 *
	 * @return string
	 */
	protected function _buildRouteName(string $prefix, $parts): string
	{
		$routeNames = [$prefix, $parts[ 'action' ]];
		if ($parts[ 'verb' ] != 'any')
		{
			$routeNames[] = $parts[ 'verb' ];
		}
		$routeName = implode('.', $routeNames);

		return $routeName;
	}
}
