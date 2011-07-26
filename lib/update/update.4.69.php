<?php

function update469() {
litepublisher::$urlmap->delete('/users.htm');
litepublisher::$classes->items['tauthor_rights'] = array('menu.admin.class.php', '');
litepublisher::$classes->add('tuserpages', 'users.pages.class.php');

$users = tusers::instance();
$users->lock();
$users->loadall();
$pages = tuserpages::instance();
$pages->lock();
foreach ($users->items as $id => $item) {
$pages->add($id, $item['name'], $item['email'], $item['url']);
if (dbversion) {
$pages->db->updateassoc(array(
'id' => $id,
'idurl' => 0,
'idview' => 1,
'registered' => $item['registered'],
'ip' => $item['ip'],
'avatar' => $item['avatar']
));
} else {
unset($item['name'], $item['email'], $item['url'], $item['registered'], $item['ip'], $item['avatar']);
$users->items[$id] = $item;
}
}
$pages->unlock();

if (dbversion) {
$man = tdbmanager::instance();
$man->alter($users->table, 'drop index status');
$man->alter($users->table, "drop name");
$man->alter($users->table, "drop email");
$man->alter($users->table, "drop url");
$man->alter($users->table, "drop registered");
$man->alter($users->table, "drop ip");
$man->alter($users->table, "drop avatar");
}
$users->unlock();
}