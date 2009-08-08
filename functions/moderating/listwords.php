<?php

function listwords($socket, $channel, $sender, $msg, $infos)
{
// 	global $bad_words;
	global $db;

	$idchan = $db->verifica_chan($channel);
	$bad_words = $db->select(
		array("bad_words", "proibita"),
		array("word"),
		array(""),
		array("IDBadWord", "IDChannel"),
		array("=", "="),
		array("IDWord", "'$idchan'")
	);

	$words = array();

	foreach($bad_words as $badword) {
		$words[] = $badword['word'];
	}

	if(count($words) > 0) {
		sendmsg($socket, "Allora... Ti do l'elenco delle parole vietate!! ;)", $channel);
		sendmsg($socket, implode(", ", $words), $channel);
	} else {
		sendmsg($socket, "Non ci sono parole vietate in questo canale", $channel);
	}
}

?>