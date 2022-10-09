<?php

/**
 * class for summing all previously received natural numbers
 * using PDO for work with database
 * test coverage 100%
 *   
 * @version 1.0
 * @author German Soyref
 */

class NaturalNumbersSum
{

    private $pdo; 

    /**
     * connect to database
     * @param string $db_name name of database
     * @param string $db_user username to connect
     * @param string $db_password user password for database access
     * @return bool result of connection 
     */
    public function connectDB (string $db_name, string $db_user, string $db_password) :bool
    {

        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=".$db_name, $db_user, $db_password);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * create table for natural numbers
     * @return void
     */
    public function createTable ()
    {

        $sql = "
            CREATE TABLE natural_numbers ( 
                id INT(11) NOT NULL AUTO_INCREMENT
                , idempotent_key VARCHAR(256) NOT NULL 
                , number INT(11) NOT NULL 
                , system_time BIGINT(20) NOT NULL 
                , PRIMARY KEY (id)
                , INDEX (idempotent_key)
                , INDEX (system_time)
            ) ENGINE = InnoDB;";

        $this->pdo->query($sql);
    }


    /**
     * Get sum of all previously received natural numbers plus current param value
     * 
     * Situations not descripted in task
     * 1. number in param is integer, but not natural - admit as error
     * 2. idempotent key is set, but natural number in DB for this key not equal number in param - admit as error
     * 3. idempotent key and number not set both - counting sum of all numbers in DB to request time without insert in DB
     * 4. idempotent key is set, but number is not set - admit as error
     * 
     * if you mean another behavior in these situations let me know for correction
     * 
     * @param string $idempotent_key
     * @param mixed $natural_number
     * 
     * @return object stdClass
     *  { 
     *      'success' => false if error 
     *      'errorText' => text description of error, empty if success
     *      'sum' => result of request, defined if success only
     *  }
     * 
     */
    public function getNaturalNumbersSum (string $idempotent_key = '', $natural_number = NULL) :object 
    {

        $system_time = hrtime(true);

        $result = new stdClass();
        $result->success = true;
        $result->errorText = ''; 

        // check number is natural if not NULL
        if ($natural_number !== NULL && (!is_int($natural_number) || $natural_number < 1)) {
            $result->success = false;
            $result->errorText = 'Incorrect data: number is not natural';  
            return $result;              
        }

        // start a transaction to eliminate a race condition
        $this->pdo->query("SET autocommit=0");
        $this->pdo->query("START TRANSACTION");

        $need_insert = true;

        if ($idempotent_key) {
            if ($natural_number) {
                // search record with same idempotent_key
                $sql = $this->pdo->prepare("SELECT * FROM natural_numbers WHERE idempotent_key = :idempotent_key");
                $sql->execute(['idempotent_key' => $idempotent_key]);
                $rec = $sql->fetch(PDO::FETCH_LAZY);
    
                if ($rec) {
                    if ($rec->number == $natural_number) { // there is idempotent_key in DB with correct data
                        $system_time = $rec->system_time; // for idempotent result
                        $need_insert = false; 
                    } else { // there is idempotent_key in DB but number not equal param value
                        $this->pdo->query("COMMIT");
                        $result->success = false;
                        $result->errorText = 'Incorrect data: natural number not relate idempotent key';  
                        return $result;  
                    }
                } 
            } else {
                // idempotent key is set but natural number is not set
                $this->pdo->query("COMMIT");
                $result->success = false;
                $result->errorText = 'Incorrect data: natural number is not set';  
                return $result;  
            }
        } 

        if ($need_insert) { // there is no idempotent_key in DB or empty idempotent_key
            $sql = $this->pdo->prepare(
                "INSERT INTO natural_numbers SET 
                    idempotent_key = :idempotent_key
                    , number = :number
                    , system_time = :system_time");

            $sql->execute([
                'idempotent_key' => $idempotent_key
                , 'number' => $natural_number
                , 'system_time' => $system_time
            ]);
        }

        // counting sum using system_time for idempotent result
        $sql = $this->pdo->prepare("SELECT SUM(number) as sum FROM natural_numbers WHERE system_time <= :system_time");    
        $sql->execute(['system_time' => $system_time]);
        $result->sum = $sql->fetchColumn();

        $this->pdo->query("COMMIT");
        return $result;
    }
}