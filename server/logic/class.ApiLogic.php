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
		
		private static function _getSiteId($sUrl) {
			return Mysql::getValue('SELECT id FROM site WHERE url = $1', [$sUrl]);
		}
		
		public static function index($sUrl) {
			$sContent = file_get_contents($sUrl);
			$nSiteId  = null;
			
			if ($sContent !== false) {
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
			}
			
			return $nSiteId;
			
			print '<pre>';
			print_r($aWordsCount);
			print '</pre>';
		}
		
		public static function compare($sUrl1, $sUrl2) {
			$nResult  = 0;
			$nSiteId1 = self::_getSiteId($sUrl1);
			$nSiteId2 = self::_getSiteId($sUrl2);

			if (is_null($nSiteId1)) { self::index($sUrl1); }
			if (is_null($nSiteId2)) { self::index($sUrl2); }
			
			if (!is_null($nSiteId1) && !is_null($nSiteId2)) {
				$nResult = Mysql::getValue(
					'SELECT
						SUM(LEAST(t1.count, t2.count))
						/
						(
							SELECT SUM(s)
							FROM (
									SELECT 1 AS my_group, MAX(site_word.count) AS s
									FROM site_word
									WHERE site_id = $1 OR site_id = $2
									GROUP BY word_id
								) AS t
							GROUP BY my_group
						)
						AS result, 1 AS c
					FROM     site_word AS t1
					JOIN     site_word AS t2
					ON       t1.word_id = t2.word_id
					WHERE    t1.site_id = $1 AND t2.site_id = $2
					GROUP BY c',
					[$nSiteId1, $nSiteId2]);
			}
			return $nResult;
		}
		
		public static function match($sUrl1, $sUrl2) {
			$nResult  = 0;
			$nSiteId1 = self::_getSiteId($sUrl1);
			$nSiteId2 = self::_getSiteId($sUrl2);

			if (is_null($nSiteId1)) { self::index($sUrl1); }
			if (is_null($nSiteId2)) { self::index($sUrl2); }
			
			if (!is_null($nSiteId1) && !is_null($nSiteId2)) {
				$nResult = Mysql::getValue(
					'SELECT SUM(LEAST(t1.count, t2.count)) AS result, 1 AS c
						FROM     site_word AS t1
						JOIN     site_word AS t2
						ON       t1.word_id = t2.word_id
						WHERE    t1.site_id = $1 AND t2.site_id = $2
						GROUP BY c',
					[$nSiteId1, $nSiteId2]);
			}
			return $nResult;
		}
	}

?>