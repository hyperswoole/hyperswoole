<?php
namespace Hyperswoole\Web;

use Hyperframework\Web\Request as Base;

class Request extends Base {
	public static function removeRequest() {
		return static::getEngine()->removeRequest();
	}    
}
