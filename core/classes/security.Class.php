<?php
/*
=====================================================
 Victor Rusu
-----------------------------------------------------
 http://ruvictor.com
-----------------------------------------------------
 Copyright (c) 2017
=====================================================
*/
class Security{
	function GetIp(){
		$ip=getenv("HTTP_X_FORWARDED_FOR");
		if (empty($ip) || $ip=='unknown'){
			$ip=getenv("REMOTE_ADDR");
		}
		$ip = stripslashes(htmlspecialchars($ip));
		return $ip;
	}
	function checking($type){
		$db = new Connect;
		$ip = $this->GetIp();
		$delete = $db->prepare("DELETE FROM security WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date) > 900");
		$delete -> execute();
		$attempts = $db->prepare("SELECT attempts FROM security WHERE ip=:ip AND type=:type");
		$attempts->execute(array(
				'ip' => $ip,
				'type'  => $type
				));
		$check = $attempts->fetch(PDO::FETCH_ASSOC);
		if($check['attempts'] > 2)
		{
			return 1;
		}
	}
	function reset_date($type){
		$db = new Connect;
		$ip = $this->GetIp();
		$select = $db->prepare("SELECT ip FROM security WHERE ip=:ip AND type=:type");
		$select->execute(array(
				'ip' => $ip,
				'type'  => $type
				));
		$check = $select->fetch(PDO::FETCH_ASSOC);
		if ($ip == $check['ip'])
		{
			$result52 = $db->prepare("SELECT attempts FROM security WHERE ip=:ip AND type=:type");
			$result52->execute(array(
					'ip' => $ip,
					'type'  => $type
					));
			$check1 = $result52->fetch(PDO::FETCH_ASSOC);         
			$col = $check1['attempts'] + 1;
			$update = $db->prepare("UPDATE security SET attempts=:col, date=NOW() WHERE ip=:ip AND type=:type");
			$update->execute(array(
					'col' => $col,
					'ip' => $ip,
					'type'  => $type
					));
		}else{
			$insert = $db->prepare("INSERT INTO security (attempts,ip,type,date) VALUES ('1',:ip,:type,NOW())");
			$insert->execute(array(
					'ip' => $ip,
					'type'  => $type
					));
		}
	}
}
?>