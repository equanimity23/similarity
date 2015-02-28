<?php

	class ApiLogic {
		
		private static function _insertSite($sUrl) {
			$nSiteId = Mysql::getValue('SELECT id FROM site WHERE url = $1', [$sUrl]);
			
			if (!$nSiteId) {
				Mysql::query('INSERT IGNORE INTO site (url) VALUES ($1)', [$sUrl]);
				$nSiteId = Mysql::getInsertId();
			}
			
			return $nSiteId;
		}
		
		private static function _insertWord($sWord) {
			$nWordId = Mysql::getValue('SELECT id FROM word WHERE word = $1', [$sWord]);
			
			if (!$nWordId) {
				Mysql::query('INSERT IGNORE INTO word (word) VALUES ($1)', [$sWord]);
				$nWordId = Mysql::getInsertId();
			}
			
			return $nWordId;
		}
		
		private static function _insertSiteWord($nSiteId, $nWordId, $nCount) {
			$sSiteWord   = $nSiteId . '_' . $nWordId;
			$nSiteWordId = Mysql::getValue('SELECT id FROM site_word WHERE site_word = $1', [$sSiteWord]);
			
			if (!$nSiteWordId) {
				Mysql::query(
					'INSERT INTO site_word (site_id, word_id, count, site_word) 
						VALUES ($1, $2, $3, $4)',
						[$nSiteId, $nWordId, $nCount, $sSiteWord]
				);
			}
		}
		
		public static function index($sUrl) {
			$sContent    = file_get_contents($sUrl);
			$sContent    = strtolower(strip_tags($sContent));
			$aWords      = preg_split('|[^a-z\']|', $sContent);
			$aWordsCount = [];
			
			foreach ($aWords as $sWord) {
				if (strlen($sWord) > 2 && !WordLogic::isStopWord($sWord)) {
					$sWord = preg_replace('|\'s$|', '', $sWord);
					if (isset($aWordsCount[$sWord])) {
						$aWordsCount[$sWord] ++;
					} else {
						$aWordsCount[$sWord] = 1;
					}
				}
			}
			arsort($aWordsCount);
			
			$nSiteId = self::_insertSite($sUrl);
			
			foreach ($aWordsCount as $sWord=>$nCount) {
				$nWordId = self::_insertWord($sWord);
				
				self::_insertSiteWord($nSiteId, $nWordId, $nCount);
			}
			
			print '<pre>';
			print_r($aWordsCount);
			print '</pre>';
		}
		
		public static function compare($sUrl1, $sUrl2) {
			
		}
	}

?>