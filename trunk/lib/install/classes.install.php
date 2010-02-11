<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function installclasses($language) {
  ParseClassesIni();
  $options = toptions::instance();
  $options->lock();
  require_once(dirname(__file__) . DIRECTORY_SEPARATOR. 'options.class.install.php');
  $password = installoptions($language);
  doinstallclasses();
  $options->unlock();
  return $password;
}

function ParseClassesIni() {
$classes = litepublisher::$classes;
  $replace = dbversion ? '.class.db.' : '.class.files.';
  $exclude = !dbversion ? '.class.db.' : '.class.files.';
  
  $ini = parse_ini_file(litepublisher::$paths->lib.'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
  foreach ($ini['items'] as $class => $filename) {
    //��������� �� ������ ������ ����� ��� �� ��� ������
    if (strpos($filename, $exclude)) continue;
    if (!file_exists(litepublisher::$paths->lib . $filename)){
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists(litepublisher::$paths->lib . $filename))continue;
    }
    $classes->items[$class] = array($filename, '');
  }
  
  $classes->classes = $ini['classes'];
  $classes->interfaces = $ini['interfaces'];
  $classes->Save();
  
  //��� ��� ttheme ��� ������ �� ��������� ������ ����
  @mkdir(litepublisher::$paths->data . 'themes', 0777);
  @chmod(litepublisher::$paths->data . 'themes', 0777);
}

function doinstallclasses() {
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $posts = tposts::instance();
  $posts->lock();
  
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->lock();
  foreach(litepublisher::$classes->items as $class => $item) {
    //echo "$class\n";
    if (preg_match('/^titemspostsowner|tcomment$/', $class)) continue;
    $obj = getinstance($class);
    if (method_exists($obj, 'install')) $obj->install();
  }
  
  $xmlrpc->unlock();
  $posts->unlock();
  $urlmap->unlock();
}

?>