<?php
	include("functions/messages/funcs.php");

	/**
	* @param $user User (Receiver) to search
	* @param $to User (Sender of message) to search
	* @param $all Type of search. false means only unread messages, true all messages
	* @return Returns an array of messages retrieved from the database
	*/
	function __getmsgs($user, $to = "", $all = false, $silent = false)
	{
// 		global $dbname;
//
// 		$where = $all ? "" : " AND msg.letto = 'false'";
// 		$where1 = ($to == "") ? "" : " AND User_From = '$to'";//WHERE
// 		$where2 = !$silent ? "" : " AND notified = 'false'";
//
// 		$dbhandle = new SQLiteDatabase($dbname);
// 		//$query = $dbhandle->query("SELECT IDMsg, data, User_To, username AS User_From, letto FROM (SELECT IDMsg, data, username AS User_To, IDFrom, letto, notified FROM msg LEFT JOIN user ON msg.IDTo = user.IDUser WHERE User_To = '$user'{$where}{$where2}) LEFT JOIN user ON IDFrom = user.IDUser$where1");
// 		$query = $dbhandle->query("SELECT IDMsg, data, username AS User_To, username AS User_From, letto FROM msg, user WHERE msg.IDFrom=user.IDUser AND msg.IDTo=user.IDUser AND User_To='$user'{$where}{$where1}{$where2}");
// 		$result = $query->fetchAll(SQLITE_ASSOC);
		global $db;

		$cond_f = array("IDFrom", "IDTo", "User_To");
		$cond_o = array("=", "=", "=");
		$cond_v = array("IDUser", "IDUser", "'paolo86'");

		if(!$all) {
			$cond_f[] = "letto";
			$cond_o[] = "=";
			$cond_v[] = "'false'";
		}
		if($to != "") {
			$cond_f[] = "User_From";
			$cond_o[] = "=";
			$cond_v[] = "'$to'";
		}
		if($silent) {
			$cond_f[] = "notified";
			$cond_o[] = "=";
			$cond_v[] = "'false'";
		}

		$result = $db->select(array("message", "user"), array("IDMsg", "data", "username", "username", "letto"), array("", "", "User_To", "User_From", ""), $cond_f, $cond_o, $cond_v);

// 		print_r($result);
		foreach($result as $msg) {
			//$query = $dbhandle->query("UPDATE msg SET notified = 'true' WHERE IDMsg = " . $msg['IDMsg']);
			$db->update("message", array("notified"), array("true"), array("IDMsg"), array("="), array($msg['IDMsg']));
		}
		return $result;
	}

	/**
	* @param $user User (Receiver) to search
	* @param $index Index of message to search
	* @param $to User (Sender of message) to search
	* @param $all Type of search. false means only unread messages, true all messages
	* @return Returns an array of messages (one message) retrieved from the database
	*/
	function __getmsg($user, $index, $to = "", $all = false)
	{
// 		global $dbname;
//
// 		$where = $all ? "" : " AND msg.letto = 'false'";
// 		$where1 = ($to == "") ? "" : " WHERE User_From = '$to'";
//
// 		$dbhandle = new SQLiteDatabase($dbname);
// 		$query = $dbhandle->query("SELECT IDMsg, message, data, User_To, username AS User_From, letto FROM (SELECT IDMsg, message, data, username AS User_To, IDFrom, letto FROM msg LEFT JOIN user ON msg.IDTo = user.IDUser WHERE User_To = '$user'$where AND IDMsg = $index) LEFT JOIN user ON IDFrom = user.IDUser$where1");
// 		$result = $query->fetchAll(SQLITE_ASSOC);
		global $db;

		$cond_f = array("IDFrom", "IDTo", "User_To", "IDMsg");
		$cond_o = array("=", "=", "=", "=");
		$cond_v = array("IDUser", "IDUser", "'paolo86'", $index);

		if(!$all) {
			$cond_f[] = "letto";
			$cond_o[] = "=";
			$cond_v[] = "'false'";
		}
		if($to != "") {
			$cond_f[] = "User_From";
			$cond_o[] = "=";
			$cond_v[] = "'$to'";
		}

		$result = $db->select(array("message", "user"), array("IDMsg", "message", "data", "username", "username", "letto"), array("", "", "", "User_To", "User_From", ""), $cond_f, $cond_o, $cond_v);

		foreach($result as $msg) {
			//$query = $dbhandle->query("UPDATE msg SET letto = 'true' WHERE IDMsg = " . $msg['IDMsg']);
			$db->update("message", array("letto"), array("true"), array("IDMsg"), array("="), array($msg['IDMsg']));
		}
		return $result;
	}

	function getmsgs($socket, $channel, $sender, $msg, $infos)
	{
// 		create_db();

		$number = -1;
		$to = "";
		$all = $silent = false;

		if(count($infos) >= 1) {
			if(ereg("^(allmessages|readall)", $infos[0]))
				$all = true;
			if(ereg("on_join", $infos[0]))
				$silent = true;
			if(ereg("bot_join", $infos[0]))
				$silent = true;
		}
		if(count($infos) > 1) {
			if(is_numeric($infos[1]))
				$number = $infos[1];
			else
				$to = $infos[1];
		}
		if($number != -1)
			$msgs = __getmsg($sender, $number, $to, $all);
		else
			$msgs = __getmsgs($sender, $to, $all, $silent);
		$cnt = count($msgs);
		if($cnt > 0 || ($cnt == 0 && !$silent)) {
			if($all)
				$letti = "";
			else
				$letti = " non lett" . ($cnt == 1 ? "o" : "i");
			sendmsg($socket, "$sender, hai $cnt " . ($cnt == 1 ? "messaggio" : "messaggi") . $letti , $sender);
			foreach($msgs as $msg) {
				sendmsg($socket, "{$msg['IDMsg']}) Sender: {$msg['User_From']}, Date: " . date("d F Y", strtotime($msg['data'])), $sender);
				if($number != -1)
					sendmsg($socket, "\t\t{$msg['message']}", $sender);
			}
		}
	}
?>