<?php

namespace Example\Api;

use Oni;

class IndexApi extends Oni\Api
{
	public function getAction()
	{
		Oni\Res::html('index', [
			'title' => 'Oni - Simple REST Framework'
		]);
	}

}