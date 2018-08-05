<?php
namespace helper;


function getConst($keyString){
    $data = require 'const.php';
    $needle = explode('.',$keyString);

    $result = $data;
    foreach($needle as $key){
        $result = $result[$key];
    }
    return $result;
}

function getTableList(){
    $result = array();
    if(file_exists('tables/tables.json')){
        $list = file('tables/tables.json');

        foreach($list as $row){
            $result[] = json_decode($row);
        }
    }
    return $result;
}
function getMessage($keyString,$replace=null){
    $needle = explode('message.',$keyString);
    $result = getConst($needle);
    //TODO replace
    return $result;

}

function generateTableID(){
    $list = getTableList();
    $tmp = array(0);
    foreach($list as $row){
        $tmp[] = $row->id;
    }
    return max($tmp) + 1;
}
