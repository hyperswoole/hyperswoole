<?php
namespace HyperswooleTest\Controllers;

class TestController extends BaseController {
	public function onIndexAction() {
		return \Hyperframework\Db\DbClient("SELECT * FROM user");
		return [
			'data' => 'test swoole'
		];
	}
}
