<?php
    // * Importing the Connection Variables
    require_once('config.php');

    // *--------- START: Singleton Connection Class Starts Here ---------------
    class Singleton_Connection {
        // * Member variables for Singleton_Connection class
        private $Host;
        private $DatabaseName;
        private $Username;
        private $Password;
        private $initConnection = null;

        // * Creating an instance of the Singleton_Connection class
        public static function instance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new Singleton_Connection();
            }
            return $instance;
        }

        // * Method for Closing the database connection
        private function close($connection) {
            $connection->close();
        }

        // * The Constructor for the Singleton_Connection class
        private function __construct() {
            // Initializing the Variables from config.php
            $variables = new Configuration();
            $this->Host = $variables->getHost();
            $this->DatabaseName = $variables->getDatabaseName();
            $this->Username = $variables->getUsername();
            $this->Password = $variables->getPassword();

            // Connecting to the Database at the $Host
            if ($this->initConnection !== null) {
                if (mysqli_ping($this->initConnection)) {
                    $this->initConnection->close();
                }
            }
            // Initializing the Connection object
            $this->initConnection = new mysqli($this->Host, $this->Username, $this->Password);
            // Checking if Connection was successful
            if ($this->initConnection->connect_error) {
                die("Connection to the Database failed: " . $this->initConnection->connect_error);
            } else {
                $this->initConnection->select_db($this->DatabaseName);
            }
        }

        // * The Destructor for the Singleton_Connection class
        public function __destruct() { // destructor closes connection
            // Check if the connection is still open and close it
            if (mysqli_ping($this->initConnection)) {
                $this->initConnection->close();
            }
        }

        // * Returning the Connection Variable
        public function getInitConnection() {
            return $this->initConnection;
        }
    } // * DONE
?>