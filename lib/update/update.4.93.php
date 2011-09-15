<?php

function update493() {
  litepublisher::$site->jquery_version = '1.6.4';
$updater = tupdater::instance();
  $updater->onupdated = tjsmerger::instance()->onupdated;

litepublisher::$classes->items['targs'][2] = 'theme.class.php';
litepublisher::$classes->add('tfilemerger', 'jsmerger.class.php');
litepublisher::$classes->add('tlocalmerger', 'localmerger.class.php');
litepublisher::$classes->save();

$merger = tlocalmerger::instance();
$merger->lock();
$plugins = tplugins::instance();
$language = litepublisher::$options->language;
foreach (array('codedoc', 'downloaditem', 'foaf', 'openid-provider', 'tickets') as $name) {
if (!isset($plugins->items[$name])) continue;
$merger->addplugin($name);
}

$merger->unlock();
}
