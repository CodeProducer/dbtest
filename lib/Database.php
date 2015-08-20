<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 18.08.15
 * Time: 11:09
 */

class Database {
    private static $instance;
    private $errors;
    private $path;
    private $transId;
    private $wal;
    private function __construct($path = null){
        $this->path = empty($path) ? __DIR__ . '/../db/' : $path;
    }
    private function __clone(){}

     public static function getInstance($path = null){
        if(empty(self::$instance)){
            self::$instance = new static($path);
        }
        return self::$instance;
    }

    public function startTransaction(){
        $this->transId = md5(rand() . time());
        $this->wal = new WAL($this->transId);
    }

    public function commit(){
        $this->wal->logCommitTransaction();
        if($this->commitTransaction($this->wal->getLog())){
             $this->wal->logSuccessfullTransaction();
             $this->transId = null;
            return true;
        }
        else{
            $this->addError(105, 'Cannot commit transaction');
            $this->rollback();
            $this->transId = null;
            return false;
        }
    }

    private function commitTransaction($log){
        $operations = json_decode($log, true);
        try{
            foreach($operations as $operation){
                switch($operation['type']){
                    case 'delete':
                        $this->deleteEntry($operation['table'], $operation['id'], false);
                        break;
                    case 'insert':
                        $this->addEntry($operation['table'], $operation['entry'], false);
                        break;
                    case 'update':
                        $this->updateEntry($operation['table'], $operation['entry'], false);
                        break;
                }
            }
        }
        catch (Exception $e) {
            $this->addError(106, $e->getMessage);
            return false;
        }
        return true;
    }

    /**
     * To rollback transaction just delete log
     */
    public function rollback(){
        $this->wal = false;
    }

    public function getTableEntries($table){
        return  $this->getAllEntries($table);
    }
    public function getEntry($table, $id){
        $allEntries = $this->getAllEntries($table);
        if(!empty($allEntries[$id])){
            return $allEntries[$id];
        }
        $this->addError(103, 'Entry ' . $id . 'does not exist');
    }

    public function addEntry($table, $data, $useTransaction = true){
        if(!$this->tableExists($table)){
            $this->addError(102, 'Table does not exist');
            return false;
        }
        $allEntries = $this->getAllEntries($table);
        if(!isset($allEntries[$data['id']])){
            // if transaction write to transaction log
            if(!empty($this->transId)  && $useTransaction){
                $this->wal->addCommitLog(['type' => 'insert', 'table' => $table, 'entry' => $data]);
            }
            else{
                $allEntries[$data['id']] = $data;
                $this->saveAllEntries($table, $allEntries);
            }
        }
        else{
            $this->addError(104, 'Entry ' . $data['id'] . ' has already exists');
        }
    }

    public function deleteEntry($table, $id, $useTransaction = true){
        if(!$this->tableExists($table)){
            $this->addError(102, 'Table does not exist');
            return false;
        }
        $allEntries = $this->getAllEntries($table);
        if(!empty($allEntries[$id])){
            if(!empty($this->transId) && $useTransaction){
                $this->wal->addCommitLog(['type' => 'delete', 'table' => $table, 'id' => $id]);
            }
            else{
                unset($allEntries[$id]);
                $this->saveAllEntries($table, $allEntries);
            }
        }
        $this->addError(103, 'Entry ' . $id . 'does not exist');
    }

    public function updateEntry($table, $data, $useTransaction = true){
        if(!$this->tableExists($table)){
            $this->addError(102, 'Table ' . $table . ' does not exist');
            return false;
        }
        $allEntries = $this->getAllEntries($table);
        if(isset($allEntries[$data['id']])){
            if(!empty($this->transId) && $useTransaction){
                $this->wal->addCommitLog(['type' => 'update',  'table' => $table, 'entry' => array_merge($allEntries[$data['id']],$data)]);
            }
            else{
                $allEntries[$data['id']] = array_merge($allEntries[$data['id']],$data);
                $this->saveAllEntries($table, $allEntries);
            }
        }
        else{
            $this->addError(104, 'Entry ' . $data['id'] . ' doesnot exists');
        }
    }

    public function createTable($table, $fields){
        if($this->tableExists($table)){
            $this->addError(101, 'Table ' . $table . ' already exists');
            return false;
        }
        if(!empty($fields) && is_array($fields)){
            file_put_contents($this->getConfigPath($table), json_encode($fields));
            file_put_contents($this->getTablePath($table), '');
            return true;
        }
        else{
            $this->addError(100, 'Incorrect fields');
            return false;
        }
    }

    public function truncateTable($table){
        if($this->tableExists($table)){
            $this->addError(102, 'Table ' . $table . ' does not exist');
            return false;
        }

    }

    public function deleteTable($table){
        if(!$this->tableExists($table)){
            $this->addError(102, 'Table doesnot exist');
            return false;
        }
        if(unlink($this->getTablePath($table)) && unlink($this->getConfigPath($table))){
            return true;
        }
        else{
            $this->addError(103, 'Table hasnot been deleted');
        }
    }

    private function tableExists($table){
        if(file_exists($this->getTablePath($table)) && file_exists($this->getConfigPath($table))){
            return true;
        }
        return false;
    }



    public function getErrors(){
        return !empty($this->errors)? $this->errors : null;
    }

    private function addError($code, $message){
        $this->errors[] = ['code' => $code, 'message' => $message];
    }

    /**
     * Return table db table path
     *
     * @param $table
     * @return string
     */
    private function getTablePath($table){
        return $this->path . $table . '.json';
    }

    /**
     * Return table db table config path
     *
     * @param $table
     * @return string
     */
    private function getConfigPath($table){
        return $this->path. $table . '_conf.json';
    }

    /**
     * Get all entries from particular table
     *
     * @param $table
     * @return array|mixed
     */
    private function getAllEntries($table){
        $allEntriesJson =  file_get_contents($this->getTablePath($table));
        return strlen($allEntriesJson) > 0 ? json_decode($allEntriesJson, true) : [];
    }

    /**
     * Save table to file
     *
     * @param $table
     * @param $allEntries
     */
    private function saveAllEntries($table, $allEntries){
        $allEntries = json_encode($allEntries);
        file_put_contents($this->getTablePath($table), $allEntries);
    }




}