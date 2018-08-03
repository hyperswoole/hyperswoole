<?php
namespace HyperswooleTest\Controllers;

use Hyperswoole\Db\CoDbClient;

class TestController extends BaseController {
	public function onIndexAction() {
		return CoDbClient::findRow("SELECT * FROM user");
	}

	public function onIndex1Action() {
		return [
			'data' => 'test swoole'
		];
	}
}
