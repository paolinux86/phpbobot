<?php
	function greetinchanonjoin($socket, $channel, $sender, $msg, $infos)
	{
		global $db;
		$errore = 0;

		$table = "chan";
		$cond_f = array("name");
		$cond_o = array("=");
		$cond_v = array($channel);

		if($infos[1] == "utenti") {
			$field = "greet";
			if(mb_strtoupper($infos[2]) == "ON") {
				$value = "TRUE";
			} else if(mb_strtoupper($infos[2]) == "OFF") {
				$value = "FALSE";
			} else {
				$errore = 1;
			}
		} else if($infos[1] == "nuovi") {
			$field = "greetnew";
			if(mb_strtoupper($infos[2]) == "ON") {
				$value = "TRUE";
			} else if(mb_strtoupper($infos[2]) == "OFF") {
				$value = "FALSE";
			} else {
				$errore = 1;
			}
		} else {
			$errore = 1;
		}

		if($errore != 1) {
			$db->update($table, array($field), array($value), $cond_f, $cond_o, $cond_v);
			sendmsg($socket, _("greetinchanonjoin-done"), $channel); //"Tutto ok!"
		} else {
			sendmsg($socket, _("greetinchanonjoin-error"), $channel); //"Errore"
		}
	}
?>
