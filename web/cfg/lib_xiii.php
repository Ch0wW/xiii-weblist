<?php
/*--------------------------------------------
			XIII Database Library
--------------------------------------------*/
define	("MINPORT", 1024);
define	("MAXPORT", 65538);

define	("KEYPASS", 11286212359);

class XIIIServerInfo
{
	/**
	 * Returns the Gamemode used by the server
	 * @param string $gamemode, $gameclass, $mutator
	 */
	public function Get_GameMode ( $gamemode, $gameclass , $mutator )
	{	
		$lstMmutator = explode(",", $mutator);
		
		// Special case, because "Deathmatch game" is kinda ridiculous when you see the other gamenames...
		if ($gamemode == "DeathMatch Game")	
			return "Deathmatch";		
		
		// Another VERY Special case with "Power Up!" gamemode.
		for ($i = 0; $i < sizeof($lstMmutator); $i++) {
			if ($gameclass == "XIIIMPGameInfo" && $lstMmutator[$i] == "XIIIMP.MarioMutator") {
				return "Power Up";														
			}
		}

		// XIIIMP Game-MODS Database
		if ($gameclass == "WarGameInfo") 					return  "OX Promod";
		else if ($gameclass == "DoomMulti")			 		return 	"DooM Mod";
		else if ($gameclass == "RealismTDM") 				return 	"Realism XIII";
		else if ($gameclass == "TeamFortressGameInfo") 		return 	"Team FortrXIII";
		else if ($gameclass == "XIIIMPTeamGameInfoPlus") 	return 	"4-TDM";
		else if ($gameclass == "XIIIMPRealDuckGameInfo")	return  "The Duck";
		
		return $gamemode;
	}
	
	/**
	 * Returns the Map used by the server
	 * @param string $map
	 */
	public function Get_Map ( $map )
	{
		// Official XIII Maps
		// Deathmatch
			 if ($map == "DM_Banque")			return	"Winslow Bank";
		else if ($map == "DM_Base")				return	"Platform-02";
		else if ($map == "DM_Base_XBox")		return	"Platform-03";
		else if ($map == "DM_Base2")			return	"AFM-10";
		else if ($map == "DM_Hual1")			return	"Emerald";
		else if ($map == "DM_Amos")				return	"FBI";
		else if ($map == "DM_Pal")				return	"Bristol Suites";
		else if ($map == "DM_Spads")			return	"SPADS (PC)";
		else if ($map == "DM_Spads_XBox")		return	"SPADS (XBOX)";
		else if ($map == "DM_PRock")			return	"Plain Rock";
		else if ($map == "DM_Warehouse")		return	"Warehouse 33";
		else if ($map == "DM_USA2_demo")		return	"Docks (DM)";
		else if ($map == "DM_Hual04a")			return	"Hualpar";
		else if ($map == "DM_PRock01a")			return	"Asylum";
		else if ($map == "DM_USA01")			return	"USA";
		else if ($map == "DM_SM01")				return	"SS-419";
		else if ($map == "DM_LostTemple")		return 	"Lost Temple";
		
		// Team Maps
		else if ($map == "CTF_Base")			return	"USS-Patriot";
		else if ($map == "CTF_Sanc")			return	"XX";
		else if ($map == "CTF_Snow")			return	"Kellownee";
		else if ($map == "CTF_Toits")			return	"New-York";
		else if ($map == "CTF_Temple")			return	"Temple";

		// Sabotage
		else if ($map == "SB_USA2")				return	"Docks";
		else if ($map == "SB_Hual1a")			return	"Choland";
		else if ($map == "SB_Camp")				return	"Camp";
		
		//-------------------------------
		// Unofficial XIII Maps (We don't know the map, sorry)
		return $map;
	}
	
}
	
class XIIICreatorInfo
{		
	public function Get_Port()
	{
		if (!isset ($_POST["xiiiport"]))
			return -1;	//Error 
		
		return htmlentities ($_POST["xiiiport"]);
	}
	
	public function Get_QueryPort()
	{
		if (!isset ($_POST["xiiiquery"])) 
			return -1; 	//Error
		
		return htmlentities ($_POST["xiiiquery"]);
	}
	
	public function Get_Ip()
	{ 
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
		else if (isset($_SERVER['HTTP_CLIENT_IP'])) 
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		else 
			$ip = $_SERVER['REMOTE_ADDR']; 
			
		return htmlentities ($ip);
	}
	
	public function Get_HashCode()
	{
		if (!isset ($_POST["azazazjaaza"])) 
			return -1; 	//Error
		
		return htmlentities ($_POST["azazazjaaza"]);
	}
}
	
class LogFile
{
	public function DateLog ($txt)
	{
		$datee = date("d_m_Y");
		$filename = "logs/".$datee.".txt";
		
		$timestamp = date("G:i");
		$timelog = "[".$timestamp."] ".$txt."\n";
		
		if (!$handle = fopen($filename, 'a')) {
		echo "Cannot open file ($filename)";
		return;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $timelog) === FALSE) {
		echo "Cannot write to file ($filename)";
		return;
		}
		
		fclose($handle);
	}

	public function LogToFile ($filename, $txt, $number)
	{
		$timelog = $txt."\n";
		
		if ($number == 0) {
			if (!$handle = fopen($filename, 'w')){
				echo "Cannot open file ($filename)";
				return;
			}

			if (fwrite($handle, $timelog) === FALSE){
				echo "Cannot write to file ($filename)";
				return;
			}
		}	
		else {
			if (!$handle = fopen($filename, 'a')){
				echo "Cannot open file ($filename)";
				return;
			}

			if (fwrite($handle, $timelog) === FALSE){
				echo "Cannot write to file ($filename)";
				return;
			}
		}
		fclose($handle);
	}
}
?>	


