<?php

	class HttpService extends Service {
		public $routes = [
			'^/api'    => '#ApiService',
			'^/client' => 'serveClientFile',
			'^/'       => 'serveMainPage'
		];
		
		public function serveMainPage() {
			print 'Welcome to Minimum!';
		}
		
		public function serveClientFile() {
			return Response::sendFile(Request::path());
		}
	}

?>