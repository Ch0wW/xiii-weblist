<?php
/* ----------------------------------
 BaseQ^ | XIII Master server list
 ---------------------------------- */
 
	// Includes
    require_once("include/gsQuery.php"); 
	require_once("include/lib_xiii.php");
	
	$info = new XIIIServerInfo();
	$log = new LogFile();
?>
	<head>
		<title>XIII Web Masterlist</title>
		<META HTTP-EQUIV="Refresh" CONTENT="30">
		<link rel="icon" type="image/png" href="favicon.png" />
		<!--[if IE]><link rel="shortcut icon" type="image/png" href="favicon.png" /><![endif]-->
	</head>

<?	
	$count = 0;
	$arrayquery = $info->GetServerArray();
	
	// Make a test for each server.
	foreach ($arrayquery as $row)
	{
	    $gameserver=gsQuery::createInstance("gsqp", $row['SERVERIP'], $row['SERVERQUERY']);
		
		if($gameserver && $gameserver->query_server(FALSE, FALSE)) 
		{
			$hostname = $gameserver->htmlize($gameserver->hostname);
			$map = htmlentities($gameserver->mapname);
			$gamemode = htmlentities($gameserver->gametype);
			$gameclass = htmlentities($gameserver->gameclass);
			$mutator = htmlentities($gameserver->mutators);
		
			echo ($row['SERVERIP']. ":". $row['SERVERPORT'] .' - '.$hostname.' / '.$info->Get_GameMode( $gamemode, $gameclass, $mutator ).' // '.$info->Get_Map( $map ).'('.htmlentities($gameserver->numplayers).'/'.htmlentities($gameserver->maxplayers).')<br />');
		}
		else
			echo ($row['SERVERIP']. ":". $row['SERVERPORT'] .'( No Beacon Received. Is the server down? )<br />');
		
		$count++;
		$gameserver = NULL;	
	}
	
	if ( $count == 0)
		echo "<strong>No server is currently running at the moment.</strong>";
	else
		echo ("<br/><strong> Total: ".$count." server(s) running.</strong>");
?>