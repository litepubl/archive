<?php

function update524() {
if (dbversion && isset(  litepublisher::$classes->items['tregservices'])) {
$man = tdbmanager::i();
$table = 'regservices';
$man->alter($table, "drop index id"); 
$man->alter($table, "drop index service"); 
$man->alter($table, "drop index uid"); 
$man->alter($table, "add   index (`service`, `uid`)");
}
}