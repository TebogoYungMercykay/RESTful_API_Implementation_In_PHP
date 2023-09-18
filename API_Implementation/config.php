<?php
    // *--------- START: Configuration Class Starts Here ---------------
    class Configuration {
        // * Member variables for Configuration class
        private $config_host = "localhost";
        private $config_databaseName = "hack_api_test";
        private $config_username = "root";
        private $config_password = "";

        // * The Constructor for the Configuration class
        public function __construct() {
            // Default Constructor
        }

        // * Getters for the Configuration class
        public function getHost() {
            return $this->config_host;
        }

        public function getDatabaseName() {
            return $this->config_databaseName;
        }

        public function getUsername() {
            return $this->config_username;
        }

        public function getPassword() {
            return $this->config_password;
        }
    } // * DONE
?>