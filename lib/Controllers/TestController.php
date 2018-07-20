<?php
namespace HyperswooleTest\Controllers;

class TestController extends BaseController {
	public function onIndexAction() {
		return \Hyperframework\Db\DbClient::findRow("SELECT * FROM user");
		return [
			'data' => 'test swoole'
		];
	}
}
