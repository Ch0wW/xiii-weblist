# xiii-weblist
This project is a Master-Server replacement done through PHP and an UScript beacon.
The Official MasterServer (and Ubi.com service) has been discontinued in May 2014, and I wanted to make a simple alternative.

## Usage (For Hosts)
0) Make sure the following ports are open:

- 7777 TCP
- 7099 UDP

1) Place your XIIIWebMasterServer.u package in your [XIII INSTALL FOLDER]/System/PC folder.

2) Then, in your XIII.ini file, look at this part:
> [Engine.GameEngine]

Search for this:

> ServerActors=IpDrv.UdpBeacon<br />
ServerActors=IpDrv.UdpServerQuery<br />
ServerActors=IpDrv.RegisterServerToUbiCom<br />

Replace it with this:

> ServerActors=XIIIWebMasterServer.XIIIUdpQuery <br />
ServerActors=XIIIWebMasterServer.TCPToMasterServer MasterIp=[ip/url] MasterPage=[webpage]<br />

***Example:*** <br />
>ServerActors=XIIIWebMasterServer.TCPToMasterServer MasterIp=192.168.1.100 MasterPage=xiii/xserveradd.php

3) Now, host a LAN Server. It may take a bit more time than usual, but it's normal: it tells to the Web MasterServer than we exist. Every 6 minutes, it sends a heartbeat.

4) To make sure, go to the Masterserver webpage, and see if your server appears.

## How can clients connect?

You don't need a custom client, or console to do this. All you actually need is to open the console (F2 key), and type this:

> Start Ip:Port (if other than 7777)

***Example:*** <br />
> start 11.22.33.44:7777
