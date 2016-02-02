//=============================================================================
//                           XIIIUdpQuery class.
//					MADE FOR SHOWING PARAMETERS OF THE SERVER 
//							DEFAULT PORT: 7099 .
//=============================================================================
class XIIIUdpQuery extends UdpLink;

var name     QueryName;               // Name to set this object's Tag to.
var int      CurrentQueryNum;     // Query ID Number.
var int      InfoPort, InfoCurrentPort;
var string   GameName;

var string ReplyData;
var string ServerName, Mutators;

/*
===============
PreBeginPlay
===============
*/
function PreBeginPlay()
{
     local int boundport;
     local string URLOptions;

     // Set the Tag
     Tag = QueryName;

     // Bind a port to be joignable through the XIII MasterServer.
     // DO NOT WORK WITH DEDICATED SERVERS YET...
     boundport = BindPort(7099, true);

     if( boundport == 0 )
     {
          Log("XIIIUdpQuery: Port failed to bind.");
          return;
     }

     Log("XIIIUdpQuery: Port "$boundport$" successfully bound.");
     InfoCurrentPort = boundport;

     //Blabla taken from ubi in order to have only the servername.
     URLOptions = AReplaceText(Level.getLocalURL()," ", "_");
     URLOptions = Mid(URLOptions, InStr( URLOptions, "?" ));

     // Compatibility for VanillaXIII Servers
     ServerName = Level.Game.ParseOption ( URLOptions, "ServerName" );
     if (ServerName == "")
     ServerName = Level.Game.ParseOption ( URLOptions, "SN" );
     if (ServerName == "")
     ServerName = "XIII Server"; // In case of...

     ReplaceText(ServerName,"_", " ");

     //[NEW] Mutators.
     Mutators = Level.Game.ParseOption (URLOptions, "Mutator" );

}

function PostBeginPlay()
{
     local UdpBeacon     Beacon;

     foreach AllActors(class'UdpBeacon', Beacon)
     {
          Beacon.UdpServerQueryPort = Port;
     }
     super.PostBeginPlay();
}

// Received a query request.
event ReceivedText( IpAddr Addr, string Text )
{
     local string Query;
     local bool QueryRemaining;
     local int  QueryNum, PacketNum;

     // Assign this packet a unique value from 1 to 100
     CurrentQueryNum++;
     if (CurrentQueryNum > 100)
          CurrentQueryNum = 1;
     QueryNum = CurrentQueryNum;

     Query = Text;
     if (Query == "")          // If the string is empty, don't parse it
          QueryRemaining = false;
     else
          QueryRemaining = true;
     //crt
     PacketNum =  0;
     ReplyData = "";
     while (QueryRemaining)
     {
          Query = ParseQuery(Addr, Query, QueryNum, PacketNum);
          if (Query == "")
               QueryRemaining = false;
          else
               QueryRemaining = true;
     }
}

function bool ParseNextQuery( string Query, out string QueryType, out string QueryValue, out string QueryRest, out int bFinalPacket )
{
     local string TempQuery;
     local int ClosingSlash;

     if (Query == "")
          return false;

     // Query should be:
     if (Left(Query, 1) == "\\")
     {
          // Check to see if closed.
          ClosingSlash = InStr(Right(Query, Len(Query)-1), "\\");
          if (ClosingSlash == 0)
               return false;

          TempQuery = Query;

          // Query looks like:
          QueryType = Right(Query, Len(Query)-1);
          QueryType = Left(QueryType, ClosingSlash);

          QueryRest = Right(Query, Len(Query) - (Len(QueryType) + 2));

          if ((QueryRest == "") || (Len(QueryRest) == 1))
          {
               bFinalPacket = 1;
               return true;
          } else if (Left(QueryRest, 1) == "\\")
               return true;     // \type\\

          // Query looks like:
          ClosingSlash = InStr(QueryRest, "\\");
          if (ClosingSlash >= 0)
               QueryValue = Left(QueryRest, ClosingSlash);
          else
               QueryValue = QueryRest;

          QueryRest = Right(Query, Len(Query) - (Len(QueryType) + Len(QueryValue) + 3));
          if (QueryRest == "")
          {
               bFinalPacket = 1;
               return true;
          } else
               return true;
     } else {
          return false;
     }
}

function string ParseQuery( IpAddr Addr, coerce string Query, int QueryNum, out int PacketNum )
{
     local string QueryType, QueryValue, QueryRest, ValidationString;
     local bool Result;
     local int bFinalPacket;

     bFinalPacket = 0;
     Result = ParseNextQuery(Query, QueryType, QueryValue, QueryRest, bFinalPacket);
     if( !Result )
          return "";

     //Log("Got  Query: "  $ QueryNum $ "." $ PacketNum $ ":" $ QueryType);


     if( QueryType=="basic" )
     {
          Result = SendQueryPacket(Addr, GetBasic(), QueryNum, PacketNum, bFinalPacket);
     }
     else if( QueryType=="info" )
     {
          Result = SendQueryPacket(Addr, GetInfo(), QueryNum, PacketNum, bFinalPacket);
     }

     if( !Result ) Log("XIIIUdpQuery: Error : Unable to respond to query.");
     return QueryRest;
}

function bool SendAPacket(IpAddr Addr, int QueryNum, out int PacketNum, int bFinalPacket)
{
     local bool Result;

     ReplyData = ReplyData$"\\queryid\\"$QueryNum$"."$++PacketNum;
     if (bFinalPacket == 1) {
          ReplyData = ReplyData $ "\\final\\";
     }
     Result = SendText(Addr, ReplyData);
     ReplyData = "";

     return Result;

}

// SendQueryPacket is a wrapper for SendText that allows for packet numbering.
function bool SendQueryPacket(IpAddr Addr, coerce string SendString, int QueryNum, out int PacketNum, int bFinalPacket)
{
     local bool Result;

     //Log("Send Query: "  $ QueryNum $ "." $ PacketNum $ ":" $ bFinalPacket);
     result = true;
     if (len(ReplyData) + len(SendString) > 1000)
          result = SendAPacket(Addr, QueryNum, PacketNum, 0);

     ReplyData = ReplyData $ SendString;

     if (bFinalPacket == 1)
          result = SendAPacket(Addr, QueryNum, PacketNum, bFinalPacket);

     return Result;
}

/*
INFO QUERIES
Just tried to use all we can easily.
*/
function string GetBasic() {
     local string ResultSet;

     // The name of this game.
     ResultSet = "\\gamename\\"$GameName;

     return ResultSet;
}

// Return a string of important system information.
function string GetInfo()
{
     local string ResultSet;

     // Servername used in ?ServerName= or ?SN=
     ResultSet = "\\hostname\\"$ServerName;

     // The server port.
     ResultSet = ResultSet$"\\hostport\\"$Level.Game.GetServerPort(); // I wanted the ip, but blocked to 0.0.0.0 ...

     // Map name
     ResultSet = ResultSet$"\\mapname\\"$Left(string(Level), InStr(string(Level), "."));

     // The GameName
     ResultSet = ResultSet$"\\gametype\\"$Level.Game.default.GameName;

     // The Game class (Not Really used, but still interesting...)
     ResultSet = ResultSet$"\\gameclass\\"$GetItemName(string(Level.Game.class));

     // [NEW] The Mutators (careful though)
     ResultSet = ResultSet$"\\mutators\\"$Mutators;

     // Actual number of players on the server.
     ResultSet = ResultSet$"\\numplayers\\"$Level.Game.NumPlayers;

     // The maximum number of players.
     ResultSet = ResultSet$"\\maxplayers\\"$Level.Game.MaxPlayers;

     // The game mode: openplaying
     ResultSet = ResultSet$"\\gamemode\\openplaying";

     ResultSet = ResultSet$Level.Game.GetInfo();

     return ResultSet;
}

function string GetRules()
{
     local string ResultSet;

     ResultSet = Level.Game.GetRules();

     // Thought if someone modified this (nobody actually until I release this), it would display those infos...
     // else I would have already removed it =)

     // Admin's Name
     if( Level.Game.GameReplicationInfo.AdminName != "" )
          ResultSet = ResultSet$"\\AdminName\\"$Level.Game.GameReplicationInfo.AdminName;

     // Admin's Email
     if( Level.Game.GameReplicationInfo.AdminEmail != "" )
          ResultSet = ResultSet$"\\AdminEMail\\"$Level.Game.GameReplicationInfo.AdminEmail;

     return ResultSet;
}

// Return a string of information on a player.
function string GetPlayer( Controller P, int PlayerNum )
{
     local string ResultSet;
     local string SkinName, FaceName;

     // Name
     ResultSet = "\\player_"$PlayerNum$"\\"$P.PlayerReplicationInfo.PlayerName;

     // Frags
     ResultSet = ResultSet$"\\frags_"$PlayerNum$"\\"$int(P.PlayerReplicationInfo.Score);

     // Ping
     ResultSet = ResultSet$"\\ping_"$PlayerNum$"\\"$P.ConsoleCommand("GETPING");

     // Team
     ResultSet = ResultSet$"\\team_"$PlayerNum$"\\"$P.PlayerReplicationInfo.Team;

     return ResultSet;
}

// Send data for each player
function bool SendPlayers(IpAddr Addr, int QueryNum, out int PacketNum, int bFinalPacket)
{
     local Controller P;
     local int i;
     local bool Result, SendResult;

     Result = false;

     P = Level.ControllerList;
     while( i < Level.Game.NumPlayers )
     {
          if (P.IsA('PlayerPawn'))
          {
               if( i==Level.Game.NumPlayers-1 && bFinalPacket==1)
                    SendResult = SendQueryPacket(Addr, GetPlayer(P, i), QueryNum, PacketNum, 1);
               else
                    SendResult = SendQueryPacket(Addr, GetPlayer(P, i), QueryNum, PacketNum, 0);
               Result = SendResult || Result;
               i++;
          }
          P = P.nextController;
     }

     return Result;
}

// Get an arbitrary property from the level object.
function string GetLevelProperty( string Prop )
{
     local string ResultSet;

     ResultSet = "\\"$Prop$"\\"$Level.GetPropertyText(Prop);

     return ResultSet;
}

// Get an arbitrary property from the game object.
function string GetGameProperty( string Prop )
{
     local string ResultSet;

     ResultSet = "\\"$Prop$"\\"$Level.Game.GetPropertyText(Prop);

     return ResultSet;
}

// Get an arbitrary property from the players.
function string GetPlayerProperty( string Prop )
{
     local string ResultSet;
     local int i;
     local Controller P;

     foreach AllActors(class'Controller', P) {
          i++;
          ResultSet = ResultSet$"\\"$Prop$"_"$i$"\\"$P.GetPropertyText(Prop);
     }

     return ResultSet;
}

/*
===============
AReplaceText
===============
*/
function string AReplaceText(string Text, string Replace, string With)
{
	local int i;
	local string Input;

	Input = Text;
	Text = "";
	i = InStr(Input, Replace);
	while(i != -1)
	{
		Text = Text $ Left(Input, i) $ With;
		Input = Mid(Input, i + Len(Replace));
		i = InStr(Input, Replace);
	}
	Text = Text $ Input;
	return  Text;
}



defaultproperties
{
     InfoPort=7099 // UDP PORT
     GameName="XIII" // DNT it!!
     QueryName="MasterUplink"
     RemoteRole=ROLE_None
}