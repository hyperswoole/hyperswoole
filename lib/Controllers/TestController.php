<?php
namespace HyperswooleTest\Controllers;

use Hyperswoole\Db\DbClient;

class TestController extends BaseController {
	public function onIndexAction() {
		return DbClient::findRow("SELECT * FROM user");
	}

	public function onIndex1Action() {
		return [
			'data' => 'test swoole'
		];
	}
}
