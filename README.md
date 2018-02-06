# Laravel route controller

This library like other existing, give a way to deal with `Router::controller`.
The key features:
* allow caching of route
* doesn't require extra manipulation
* allow inheritance of route group
* namespace independent

Compatible with laravel 5.5. (probably compatible with older version too).

## Install

Install the dependencies
```
composer require grummfy/laravel-route-controller
```

## Usage

Once the package is install and autodiscover (or you have added the provider manually), the package is ready to use.

In your route file, just do this:
```
Route::controller('/foo', \App\Http\Controllers\FooController::class);
```
It will take all the public method from the class (including traits) and convert it to route.

There is a third argument that you can use to add extra option, like a middleware or anything else.
```
Route::controller('/foo', \App\Http\Controllers\FooController::class, ['middleware' => 'can:baz']);
```

### Example

Imagine that we have the class FooController (see [example](example/FooController.php)), in this case we will have a series of method that will be converted to routes:
* index() -> /foo, foo.index
* getStatus(string $status) -> /foo/status/{status}, foo.status.post
* postStatus(string $status) -> /foo/status/{status}, foo.status.get
* foo() -> /foo/foo, foo.foo
* my() -> /foo/my, foo.my

## TODO
* unit test
* QA tools
  * travis
  * styleci
  * scrutinizer
  * ...

## Alternative

* https://github.com/lesichkovm/laravel-advanced-route
* https://github.com/themsaid/laravel-routes-publisher
* https://github.com/shrimpwagon/laravel-route-controller
* https://www.larablocks.com/package/eightyfive/laravel-autoroute
* https://gist.github.com/cotcotquedec/df15f9111e5c8d118ac270f6a157c460
* ...
