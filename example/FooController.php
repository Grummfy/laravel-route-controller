<?php

namespace App\Http\Controllers\Merchants;

use Illuminate\Routing\Controller;

class FooController extends Controller
{
	use BarTrait;

	public function index()
	{
		return __METHOD__;
	}

	public function getStatus(string $status)
	{
		return __METHOD__ . ': ' . $status;
	}

	public function postStatus(string $status)
	{
		return __METHOD__ . ': ' . $status;
	}

	public function foo()
	{
		return __METHOD__;
	}
}
