//-----------------------------------------------------------
//		XIII WebMasterServer Sender
//-----------------------------------------------------------
class TCPToMasterServer extends TCPLink config;

var bool gDebug;                              // For some debugging sessions
var XIIIUdpQuery      Query;                  // The query object.
var int               VVVVVV;                 // Veni Vedi Vici Video Viper Verification
var name              TargetQueryName;        // name of the query server object to use.

var() config int QueryPort;                   // Query port.
var() config string MasterIp;                 // Masterserver address
var() config string MasterPage;               // Masterserver regisration page
var() config int MasterPort;                  // Masterserver port (if not 80)
var() config bool bWarningMsg;                // Warning Messages (Careful!!)

const REQ_NONE = 0;
const REQ_CREATED = 1;
const REQ_ALIVE = 2;
var int REQ_TYPE;

/*
========
PreBeginPlay()
Checks if we are having any error in order to register successfully a XIII server.
========
*/
function PreBeginPlay()
{
	REQ_TYPE = REQ_NONE;
	if ((Level.NetMode == NM_DedicatedServer) && bWarningMsg)
	{
		Log("");
		Log("=============== [ MasterServer Replacement for XIII ] =====================");
		Log("WARNING :");
		Log("if you see this message in your dedicated server, the query port is probably forced to 7099.");
		Log("");
		Log("If you host 1+ dedicated servers, please make a copy of your XIII.INI file, rename it, add a new port on your serveractor, and use -ini=[NEW INI] on your serverparameters.");
		Log(".");
		Log("=============== [ MasterServer Replacement for XIII ] =====================");
	}
    // Find a the server query handler.
    foreach AllActors(class'XIIIUdpQuery', Query, TargetQueryName)
        break;

    if( Query==None )
    {
		Log("");
		Log("=============== [ MasterServer Replacement for XIII ] =====================");
		Log("ERROR !");
		Log("There is no XIIIUdpQuery serveractor!");
		Log("Either you put it after TCPToMasterServer, either you forget to add it to [Engine.GameEngine] (XIII.ini) ...");
		Log("");
		Log("for further information of how to make it work, go to https://github.com/Ch0wW/xiii-weblist/blob/master/README.md ");
		Log("");
		Log("The server isn't registered on the MasterServer.");
		Log("");
		Log("ERROR !");
		Log("=============== [ MasterServer Replacement for XIII ] =====================");
        return;
    }

   if ((Level.NetMode != NM_DedicatedServer)) {QueryPort = Query.Port;}

   // Let's talk to you, Master.
   SetTimer ( 5 * 60, true);
   Resolve (MasterIp);
}

/*
I have talked to the server!
*/
event Resolved( IpAddr Addr )
{
   IpAddrToString(Addr);
   Addr.Port = MasterPort;
   BindPort();

   ReceiveMode = EReceiveMode.RMODE_Event;
   LinkMode = ELinkMode.MODE_Text;

    // Check if we aren't already connected to the server.
	if(!IsConnected())
        Open(Addr);

	if (REQ_TYPE != REQ_ALIVE)
	{
		REQ_TYPE = REQ_CREATED;
		log ("[TCP Masterserver] Sending Request to "$IpAddrToString(Addr));
	}
	XIII_SendToServer ();


}

/*
I have failed to contact you...
*/
event ResolveFailed ()
{
	 log ("[TCP Masterserver] Server failed to resolve "$MasterIp$".");
	 REQ_TYPE = REQ_NONE;
}

/*====================
XIII_SendToServer
-> Send a HTTP Request.
====================*/
function XIII_SendToServer ()
{
	local String XIIIHTTP, Query;

	Query = Xmp_PortQuery();

	XIIIHTTP = XIIIHeaders();
	XIIIHTTP = XIIIHTTP$ReqLength (Query);
	XIIIHTTP = XIIIHTTP$Chr(13)$Chr(10);
	XIIIHTTP = XIIIHTTP$Query;

	// Send my PHP request.
	SendText (XIIIHTTP);
}

function String XIIIHeaders ()
{
  local string sHeader;

  sHeader =  "POST "$MasterPage$" HTTP/1.0"$Chr(13)$Chr(10);
  sHeader = sHeader$"User-Agent: HTTPTool/1.0"$Chr(13)$Chr(10);
  sHeader = sHeader$"Content-Type: application/x-www-form-urlencoded"$Chr(13)$Chr(10);

  return sHeader;
}

function String ReqLength(String request)
{
  return "Content-Length: "$len(request)$Chr(13)$Chr(10);
}

function String Xmp_PortQuery()
{
	log ("[DEBUG INFO] xiiiport="$Level.Game.GetServerPort()$"&xiiiquery="$QueryPort);
	return "xiiiport="$Level.Game.GetServerPort()$"&xiiiquery="$QueryPort$"&azazazjaaza="$VVVVVV;
}

event ReceivedText( string Text )
{
	 if (gDebug)Log("Received : "$Text);

	 if (REQ_TYPE == REQ_CREATED)
		Log("[TCP MasterServer] Request to "$MasterIp$" sent.");
	 else if (REQ_TYPE == REQ_ALIVE)
		Log("[TCP MasterServer] Heartbeat to "$MasterIp$" sent.");
}

event Timer ()
{
	REQ_TYPE = REQ_ALIVE;
	Log("[TCP MasterServer] Sending heartbeat to "$MasterIp$" .");
	Resolve (MasterIp);
}

defaultproperties
{
  MasterPage =  "/xiii/web/xiiiaddserver.php"
  MasterIp =  "192.168.1.100"
  MasterPort = 80
  QueryPort= 7099
  VVVVVV=11286212359
  gDebug = False
  bWarningMsg = True
  TargetQueryName="MasterUplink"
}