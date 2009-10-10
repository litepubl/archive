<?php

class TDBManager  {
private $max_allowed_packet;
  
  public function __get($name) {
    global $db;
    if ($name == 'db') return $db;
    return $db->$name;
  }
  
  public function __call($name, $arg) {
    global $db;
    return call_user_func_array(array(&$db, $name), $arg);
  }
  
  public function CreateTable($name, $struct) {
    return $this->exec("
    create table $this->prefix$name
    ($struct)
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }
  
  public function DeleteTable($name) {
    $this->exec("DROP TABLE IF EXISTS $this->prefix$name");
  }
  
  public function  DeleteAllTables( ) {
    global $dbconfig;
  $res = $this->query("show tables from {$dbconfig['dbname']}");
    $sql = '';
    while ($row = $res->fetch()) {
      //if (array_key_exists($row[0],$th)) do_export_table($row[0],1,$MAXI);
      $sql .= "drop table $name;\n";
    }
    return $this->exec($sql);
  }
  
  public function clear($name) {
    return $this->exec("truncate $this->prefix$name");
  }
  
  public function alter($arg) {
    return $this->exec("alter table $this->prefix$this->table $arg");
  }
  
  public function GetDatabases() {
    if ($res = $this->query("show databases")) {
      return $this->res2array($res);
    }
    return false;
  }
  
  public function DatabaseExists($name) {
    if ($list = $this->GetDatabaseList()) {
      return in_array($name, $list);
    }
    return FALSE;
  }
  
  public function GetTables() {
    global $dbconfig;
  if ($res = $this->query("show tables from {$dbconfig['dbname']} like '$this->prefix%'")) {
      return $this->res2array($res);
    }
    return false;
  }
  
  public function  TableExists( $name) {
    if ( $list = $this->GetTableList()) {
      return in_array($this->prefix . $name, $list);
    }
    return false;
  }
  
  public function CreateDatabase($name) {
    if ( $this->DatabaseExists($name) )  return false;
    return $this->exec("CREATE DATABASE $name");
  }
  
public function export() {
global $options, $dbconfig;
$res = $this->query("show variables like 'max_allowed_packet'");
$v = $res->fetch();
 $this->max_allowed_packet =floor($v['Value']*0.8);

@$result = "-- Lite Publisher dump $options->version\n";
$result .= "-- Datetime: ".date('Y-m-d H:i:s');
$result .= "\n-- Host: {$dbconfig['host']}\n-- Database: {$dbconfig['dbname']}\n\n";
$result .= "/*!40030 SET max_allowed_packet=$this->max_allowed_packet */;\n\n";

$tables = $this->GetTables();
foreach ($tables as $table) {
$result .= $this->ExportTable($table);
}
$result .= "\n-- Lite Publisher dump end\n";
return $result;
}

public function ExportTable($name) {
global $db;

if ($res = $this->query("show create table `$name`")) {
  $row=$res->fetch();
$result = "DROP TABLE IF EXISTS `$name`;\n$row[1];\n\n";
if ($res =$this->query("select * from `$name`")) {
$result .= "LOCK TABLES `$name` WRITE;\n/*!40000 ALTER TABLE `$name` DISABLE KEYS */;\n";
$sql = '';
while ($row = $res->fetch(PDO::FETCH_NUM)) {
    $values= array();
    foreach($row as $v){
$values[] = is_null($value) ? 'NULL' : $db->quote($value);
}
    $sql .= $sql ? ',(' : '(';
$sql .= implode(', ', $values);
$sql .= ')';

    if (strlen($sql)>$this->max_allowed_packet) {
$result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
$sql = '';
    }
  }

  if ($sql) $result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
$result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n";
}
return $result;
}
}

}//class
?>