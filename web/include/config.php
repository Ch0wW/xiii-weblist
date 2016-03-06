<?php
/*--------------------------------------------
XIII MASTERSERVER LIST - CONFIG FILE.
----------------------------------------------*/

//-- Database
define	("DB_HOST", 	'DATABASE_HOST');	// Link to the database
define	("DB_USER", 	'DATABASE_USER');		// db account for XIII servers.
define	("DB_PASS", 	'DATABASE_PASSWORD');	// Password of the account.
define 	("DB_DATABASE", 'DATABASE_NAME');	// DataBase for the servers.
	
//=====
// ERROR CODES
//=====
define	("ERR01", 		'Database connexion problem.');	// Link to the database
define	("ERR02", 		'Database not found.');			// db account for XIII servers.
define	("ERR03", 		'Query impossible...');			// Query problem
define	("ERR04",		'Impossible to update value(s).'); // UPDATE impossible
define	("ERR05",		'Impossible to alter the table.'); // Alter table impossible.

?>