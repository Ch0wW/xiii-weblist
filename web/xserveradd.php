<?php
	require("include/lib_xiii.php");
	
	$info = new XIIICreatorInfo();
	$log = new LogFile();

	// Quick check.
	function IsEmpty($var)
	{
		if ($var == -1)
			return true;
		return false;
	}
	
	// Check for illegal inputs.
	if ( IsEmpty($info->Get_HashCode()) && ($info->Get_HashCode() != KEYPASS) ) 
		return;

	// Invalid Port range.
	if ( ($info->Get_Port()      > MAXPORT && $info->Get_Port()      < MINPORT) 
	  || ($info->Get_QueryPort() > MAXPORT && $info->Get_QueryPort() < MINPORT) )
		return;	

	// Our checks seem to be "OK", so log it.
	$log->DateLog("[ASK] - Server ".$info->Get_Ip().":".$info->Get_Port()." asks for MasterServer regisration.");	

	// Check if we got already the ip/port into our database.
	$idcheck = $info->CheckServerAlive($info->Get_Ip(), $info->Get_Port());

	if ($idcheck != "")
	{
		if ($info->UpdateServer($idcheck))
			$log->DateLog("[SUCCESS] - Server at ip ".$info->Get_Ip().":".$info->Get_Port()." successfully updated for 6 more minutes!");
	}	
	else {
		if ($info->AddServer($info->Get_Ip(), $info->Get_Port(), $info->Get_QueryPort()))
			$log->DateLog("[SUCCESS] - Server at ip ".$info->Get_Ip().":".$info->Get_Port()." successfully created to our database!");
	}

?> 