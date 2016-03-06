<?php

/*
 *  gsQuery - Querys game servers
 *  Copyright (c) 2002-2004 Jeremias Reith <jr@terragate.net>
 *  http://gsquery.terragate.net
 *
 *  This file is part of the gsQuery library.
 *
 *  The gsQuery library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  The gsQuery library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with the gsQuery library; if not, write to the
 *  Free Software Foundation, Inc.,
 *  59 Temple Place, Suite 330, Boston,
 *  MA  02111-1307  USA
 *
 */

include_once("gsQuery.php");

/**
 * @brief Uses the gameSpy protcol to communicate with the server
 * @author Jeremias Reith (jr@terragate.net)
 * @version $Id: gameSpy.php,v 1.16 2004/03/21 10:02:27 jr Exp $
 * @bug some games does not escape the backslash, so we have a problem when somebody has a backlsash in its name
 *
 * The following games have been tested with this class:
 *
 *   - Unreal Tournamnet (and most mods)
 *   - Unreal Tournamnet 2003 (and most mods)
 *   - Battlefield 1942 (and most mods)
 */
class gameSpy extends gsQuery
{

  function getGameJoinerURI()
  {
    switch($this->gamename) {
    case "bfield1942":
      return "gamejoin://bf1942@". $this->address .":". $this->hostport ."/";
      break;
    case "ut2":
      return "gamejoin://ut2003@". $this->address .":". $this->hostport ."/";
      break;
    default:
      return "gamejoin://". $this->gamename ."@". $this->address .":". $this->hostport ."/";
    }
  }

  function query_server($getPlayers=TRUE,$getRules=TRUE)
  {       
    $this->playerkeys=array();
    $this->debug=array();
    $this->errstr="";
    $this->password=-1;
    
    $cmd="\\basic\\\\info\\";
    if(!($response=$this->_sendCommand($this->address, $this->queryport, $cmd))) {
      $this->errstr="No reply received";
      return FALSE;
    }    
    $this->_processServerInfo($response);
    

    // get players
    if($this->numplayers && $getPlayers) {
      $cmd="\\players\\";
      if(!($response=$this->_sendCommand($this->address, $this->queryport, $cmd))) {
	return FALSE;
      }    
  
      $this->_processPlayers($response);
    }    


    // get rules
    if($getRules) {
      $cmd="\\rules\\";
      if(!($response=$this->_sendCommand($this->address, $this->queryport, $cmd))) {
	return FALSE;
      } 
      $this->_processRules($response);
    }

    return TRUE;
  }

  /**
   * @internal @brief Process the given raw data and stores everything
   *
   * @param rawdata data that has the basic server infos
   * @return TRUE on success 
   */
  function _processServerInfo($rawdata)
  {

    $temp=explode("\\",$rawdata);
    $count=count($temp);
    for($i=1;$i<$count;$i++) {
      $data[$temp[$i]]=$temp[++$i];
    }

    $this->gamename = $data["gamename"];
    $this->hostport = $data["hostport"];
    //$this->gameversion = $data["gamever"];
    $this->servertitle = $data["hostname"];
    $this->maptitle = isset($data["maptitle"]) ? $data["maptitle"] : "";
    $this->mapname = $data["mapname"];
    $this->gametype = $data["gametype"];
    $this->numplayers = $data["numplayers"];
    $this->maxplayers = $data["maxplayers"];
	//Ch0wW : Added
	$this->gameclass = $data["gameclass"];
    $this->hostname = $data["hostname"];
	$this->mutators = $data["mutators"];

   
    if(isset($data["password"]) && ($data["password"]==0 || $data["password"]==1)) {  
      $this->password=$data["password"];
    }
    
    if(!$this->gamename) {
      $this->gamename="unknown";
    }

    return TRUE;
  }

  /**
   * @internal @brief Extracts the players out of the given data 
   *
   * @param rawPlayerData data with players
   * @return TRUE on success 
   */
  function _processPlayers($rawPlayerData) 
  {
    $temp=explode("\\", $rawPlayerData);
    $this->playerkeys["name"]=TRUE;
    $count=count($temp);
    for($i=1;$i<$count;$i++) {
      list($var, $playerid)=explode("_", $temp[$i]);
      switch($var) {
      case "player":
      case "playername":
	$players[$playerid]["name"]=$temp[++$i];	    
	break;
      case "teamname":
	$this->playerteams[$playerid]=$temp[++$i];	    
	break;
      default:
	$players[$playerid][$var]=$temp[++$i];
	$this->playerkeys[$var]=TRUE;
      }
    }
    $this->players=$players;
    return TRUE;
  }

  /**
   * @internal @brief Extracts the rules out of the given data 
   *
   * @param rawData data with rules
   * @return TRUE on success 
   */  
  function _processRules($rawData)
  {
    $temp=explode("\\",$rawData);
    $count=count($temp);
    for($i=1;$i<$count;$i++) { 
      if($temp[$i]!="queryid" && $temp[$i]!="final" && $temp[$i]!="password") {
	$this->rules[$temp[$i]]=$temp[++$i]; 
      } else {
	if($temp[$i++]=="password") {
	  $this->password=$temp[$i];	  
	}
      }
    } 
    return TRUE;
  }
  
  /**
   * @internal @brief sorts the given gamespy data
   *
   * @param data raw data to sort
   * @return raw data sorted
   */
  function _sortByQueryId($data)
  {
    $result="";
    $data=preg_replace("/\\\final\\\/", "", $data);
    $exploded_data=explode("\\queryid\\", $data);
    $count=count($exploded_data);
    for($i=0;$i<$count-1;$i++) { 
      preg_match("/^\d+\.(\d+)/", $exploded_data[$i+1], $id);
      $sorted_data[$id[1]]=$exploded_data[$i];
      $exploded_data[$i+1]=substr($exploded_data[$i+1],strlen($id[0]-1),strlen($exploded_data[$i+1]));
    }

    if(!$sorted_data) {
      // the request is probably incomplete  
      return $data;
    }

    // sort the hash
    ksort($sorted_data);
    foreach($sorted_data as $key => $value) {
      $result.=isset($value) ? $value : "";
    }
    return($result);
  }  

  function _sendCommand($address, $port, $command, $timeout=500000)
  {
    $data=parent::_sendCommand($address, $port, $command, $timeout);
    if(!$data) {
      return FALSE;
    }
    return $this->_sortByQueryId($data);
  }
}

?>
