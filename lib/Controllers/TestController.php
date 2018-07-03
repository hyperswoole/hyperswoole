<?php
namespace HyperswooleTest\Controllers;

class TestController extends BaseController {
	public function onIndexAction() {
		return [
			'data' => 'test swoole'
		];
	}
}
