<?php
/* ----------------------------------
 BaseQ^ | XIII Master server list
 ---------------------------------- */
 
	// Includes
	require_once("config.php");
    require_once("cfg/gsQuery.php"); 
	require_once("cfg/lib_xiii.php");
	
	$info = new XIIIServerInfo();
	$log = new LogFile();
	
	echo "<head>";
	echo '<META HTTP-EQUIV="Refresh" CONTENT="30">';
	echo '<title> XIII Multiplayer - Serverlist </title>';
	
	
	echo '<link rel="icon" type="image/png" href="favicon.png" />';
	echo '<!--[if IE]><link rel="shortcut icon" type="image/png" href="favicon.png" /><![endif]-->';
	
	echo "</head>";
	
	// Connexion SQL
	mysql_connect (DB_HOST, DB_USER, DB_PASS) or die (ERR01);
	mysql_select_db (DB_DATABASE) or die (ERR02);
	
	$count = 0;
	// Counts how many XIII Servers are online yet.	
	$NBServersQuery[0]=0;
	$NBServersQuery = mysql_fetch_row(mysql_query("SELECT DISTINCT COUNT(*) FROM XIIIServer WHERE NOW() < SERVERVALIDTIME")); 
	
	// Retrieve all existing XIII servers we have in our database:
	$query = "SELECT DISTINCT * FROM XIIIServer WHERE NOW() < SERVERVALIDTIME"; 
	$result = mysql_query($query) or die(ERR03);

	// Make a test for each server.
	while($row = mysql_fetch_array($result))
	{
	    $gameserver=gsQuery::createInstance("gameSpy", $row['SERVERIP'], $row['SERVERQUERY']);
		
		// Server is found!!
        if($gameserver && $gameserver->query_server(FALSE, FALSE)) 
		{
			//-- Get the important infos :
			$hostname = $gameserver->htmlize($gameserver->hostname);
			$map = htmlentities($gameserver->mapname);
			$gamemode = htmlentities($gameserver->gametype);
			$gameclass = htmlentities($gameserver->gameclass);
			$mutator = htmlentities($gameserver->mutators);
		
			//-- Htmlize it
			echo ($row['SERVERIP']. ":". $row['SERVERPORT'] .' - '.$hostname.' / '.$info->Get_GameMode( $gamemode, $gameclass, $mutator ).' // '.$info->Get_Map( $map ).'('.htmlentities($gameserver->numplayers).'/'.htmlentities($gameserver->maxplayers).') ( No Beacon Received. Is the server down? )<br />');
			$count++;
		}
		else // Display the server, but with no informations.
		{
			echo ($row['SERVERIP']. ":". $row['SERVERPORT'] .'<br />');
			$count++;
		}
	}
	
	if ( $NBServersQuery[0] == 0)
		echo "<strong>No server currently running at the moment.</strong>";
	else
		echo ("<br/><strong> Total :  ".$NBServersQuery[0]." server(s) running.</strong>");

	mysql_close();
?>