<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 18.08.15
 * Time: 11:09
 */

class WAL {
    private $path;
    private $defaultPath;
    private $toWrite =[];
    private $transId;


    public function __construct($id = null){
        $this->transId = $id;
        $this->path = dirname(__FILE__) . '/../log/'. $id . '.json';
        $this->defaultPath = dirname(__FILE__) . '/../log/log.json';
    }

    /**
     * Write successfully transactioned log to history log
     * @param $data
     */

    public function logSuccessfullTransaction(){

        file_put_contents($this->defaultPath, 'TIME: ' . date('Y-m-d H-m-s') .', DATA:' . PHP_EOL. file_get_contents($this->path) . PHP_EOL . PHP_EOL, FILE_APPEND);
        unlink($this->path);
    }

    /**
     * Temp log write transaction log to tmp file
     */
    public function logCommitTransaction(){
        file_put_contents($this->path, json_encode($this->toWrite), FILE_APPEND);
    }

    /**
     * Add data to log which will be committed later
     * @param $data
     */
    public function addCommitLog($data){
        $this->toWrite[] = $data;
    }

    /**
     * Get data from tmp log
     */
    public function getLog(){
       return file_get_contents($this->path);
    }

    public static function showLog(){
        $log = new self;
        $data = file_get_contents($log->defaultPath);
        $result = explode(PHP_EOL.PHP_EOL, $data);
        foreach ($result as $row){
            echo $row . '<br/><br/>';
        }

    }




}