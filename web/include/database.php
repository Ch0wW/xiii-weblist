<?
    class Database
    {
        public $conn;

        // constructor
        function __construct() {
            // connecting to database
            $this->conn = $this->connect();
        }

        function __destruct() { }

        // Connecting to database
        private function connect() 
		{
            require_once ('config.php');
			try
			{				
				$DB = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PASS);
				$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
            catch(PDOException $e) {
				return -1;
            }

            return $DB;
        }
    }

?>