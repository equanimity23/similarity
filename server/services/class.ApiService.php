<?php

	class ApiService extends Service {
		public $routes = [
			'^/api/index'   => 'indexPage',
			'^/api/compare' => 'comparePages'
		];
		
		public function indexPage() {
			$sUrl = Request::param('url');
			ApiLogic::index($sUrl);
		}
		
		public function comparePages() {
			$sUrl1 = Request::param('url1');
			$sUrl2 = Request::param('url2');
			print ApiLogic::compare($sUrl1, $sUrl2);
		}
	}

?>