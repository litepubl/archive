<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsimpleimporter extends timporter {
public $tagsmap;

  public static function instance() {
  return getinstance(__class__);
 }

 protected function create() {
  parent::create();
$this->data['script'] = '';
$this->addmap('tagsmap', array(
'title' => 'title',
'link' => 'link',
'pubDate' => 'pubdate',
'content:encoded' => 'content'
));
}

public function getcontent() {
$result = parent::getcontent();
$tagsmap = '';
foreach ($this->tagsmap as $key => $val) {
$tagsmap .= "$key = $val\n";
}
$args = targs::instance();
$args->tagsmap = $tagsmap;
$args->script = $this->script;
$admin = tadminplugins::instance();
$about = $admin->abouts[$_GET['plugin']];
$args->maplabel = $about['maplabel'];
$args->scriptlabel = $about['scriptlabel'];
$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'form.tml');
$html = THtmlResource::instance();
$result .= $html->parsearg($tml, $args);
return $result;
}

public function processform() {
if ($_POST['form'] != 'options')  return parent::ProcessForm();
$this->parsemap($_POST['tagsmap']);
$this->script = $_POST['script'];
$this->save();
}

public function parsemap($s) {
$this->tagsmap = array();
$lines = explode("\n", $s);
foreach ($lines as $line) {
if ($i = strpos($line, '=')) {
$key = trim(substr($line, 0, $i));
$val = trim(substr($line, $i + 1));
$this->tagsmap[$key] = $val;
}
}
}

public function import($s) {
require_once(litepublisher::$paths->lib . 'domrss.class.php');
$a = xml2array($s);

$urlmap = turlmap::instance();
$urlmap->lock();
$cats = tcategories::instance();
$cats->lock();
$tags = ttags::instance();
$tags->lock();
$posts = tposts::instance();
$posts->lock();
foreach ($a['rss']['channel'][0]['item'] as $item) {
$post = $this->add($item);
$posts->add($post);
//echo $post->id, "<br>\n";
if (!tdata::$GlobalLock) $post->free();
//echo "<pre>\n";
//var_dump($post->data);
}
$posts->unlock();
$tags->unlock();
$cats->unlock();
$urlmap->unlock();
}

public function add(array $item) {
$post = tpost::instance();
foreach ($this->tagsmap as $key => $val) {
if (isset($item[$key])) {
$post->{$val} = $item[$key];
}
}
if ($this->script != '') eval($this->script);
return $post;
}

}//class

?>