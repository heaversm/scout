<?php
class Stats {
	private static function getStatsTable() {
		return 'stats.'.date('Y').'.'.date('m').'.'.date('d');
	}
	
	private static function getStatTime() {
		return round(time() / 60) * 60 ;
	}
	
	private static function logExists($stat_name, $version, $user_id) {
		return Mongo_Query::create(self::getStatsTable())
			->where('stat_name', $stat_name)
			->where('version', $version)
			->where('user_id', $user_id)
			->limit(1)
			->findOne();
	}
	
	private static function isUnique($stat_name) {
		return isset(StatsConfig::$stats[$stat_name]['unique']);
	}
	
	private static function isStat($stat_name) {
		return isset(StatsConfig::$stats[$stat_name]);
	}
	
	public static function log($stat_name, $version = 'default', $user_id = 0) {
		if($user_id === 0) {
			$user_id = Authentication::create()->getFBUser();
		}
		
		if(self::isStat($stat_name)) {
			$stat_id = StatsConfig::$stats[$stat_name]['id'];
		} else {
			throw new Exception('Trying to log an non-existent stat: '.$stat_name);
		}
		
		if($user_id > 0 && self::isUnique($stat_name) && self::logExists($stat_name, $version, $user_id)) {
			return false;
		}
		
		Mongo_Query::create(self::getStatsTable())
			->values(array(
				'stat_id' => $stat_id,
				'version' => $version,
				'user_id' => $user_id,
				'time' => self::getStatTime()
			))->insert();
			
		return true;
	}
}