<?php


	require("config.php");
	require("cfg/lib_xiii.php");
	
	$info = new XIIICreatorInfo();
	$log = new LogFile();

	// Quick check.
	function IsEmpty($var)
	{
		if ($var == -1)
			return true;
		return false;
	}

	//echo ('<head> <meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> </head>');

	// Check for illegal inputs.
	if ( IsEmpty($info->Get_HashCode()) && ($info->Get_HashCode() != KEYPASS) ) 
	{
		$log->DateLog("[WARNING] - Ip ".$info->Get_Ip().".");
		return;
	}
	// Invalid Port range.
	if ( ($info->Get_Port()      > MAXPORT && $info->Get_Port()      < MINPORT) 
	  || ($info->Get_QueryPort() > MAXPORT && $info->Get_QueryPort() < MINPORT) )
	  {
		$log->DateLog("[WARNING] - Ip ".$info->Get_Ip()." wants to bypass the regisration by entering an illegal query/server port.");
		return;	
	  }

	// Our checks seem to be "OK", so log it.
	$log->DateLog("[ASK] - Server ".$info->Get_Ip().":".$info->Get_Port()." asks for MasterServer regisration.");	

	// Connexion SQL
	mysql_connect (DB_HOST, DB_USER, DB_PASS) or die (ERR01);
	mysql_select_db (DB_DATABASE) or die (ERR02);
		
	// Check if we got already the ip/port into our database.
	$checkid=mysql_query('SELECT `ID` FROM XIIIServer WHERE SERVERIP="'.$info->Get_Ip().'" AND SERVERPORT='.$info->Get_Port());
	$array= mysql_fetch_assoc($checkid);

	if ($array['ID'] != "")
	{
		// Increase the server valid time to our database.
		$sql_upd_test = mysql_query('UPDATE XIIIServer SET SERVERVALIDTIME=DATE_ADD( NOW(), INTERVAL 6 MINUTE) WHERE ID='.$array['ID']) or die (ERR04);
		mysql_close();
			
		// Have we entered it without any trouble?
		if($sql_upd_test)
			$log->DateLog("[SUCCESS] - Server at ip ".$info->Get_Ip().":".$info->Get_Port()." successfully updated for 6 more minutes!");
			
		return;
	}	
	else {
		// Insert it to our database while he's alive.
		$rq = "INSERT INTO XIIIServer(ID, SERVERIP, SERVERPORT, SERVERQUERY,SERVERCREATION,SERVERVALIDTIME) ";
		$rq .= "values (";
		$rq .= "LAST_INSERT_ID(),";
		$rq .= '"'.$info->Get_Ip().'",';		// IP
		$rq .= $info->Get_Port().",";			// PORT
		$rq .= $info->Get_QueryPort().",";			// QUERY
		$rq .= "NOW(), DATE_ADD( NOW(), INTERVAL 6 MINUTE))";	// SERVERCREATION + VALIDTIME
		$sql_req_test = mysql_query($rq) or die (mysql_error());	
		mysql_close();

		// Check if it was successful!
			if ($sql_req_test)
				$log->DateLog("[SUCCESS] - Server at ip ".$info->Get_Ip().":".$info->Get_Port()." successfully created to our database!");
	}

?> 