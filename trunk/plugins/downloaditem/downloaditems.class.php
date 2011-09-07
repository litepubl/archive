<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditems extends tposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->childtable = 'downloaditems';
  }
  
  public function createpoll() {
    $lang = tlocal::admin('downloaditems');
    $items = explode(',', $lang->pollitems);
    $polls = tpolls::instance();
    return $polls->add('', 'opened', 'button', $items);
  }
  
  public function add(tpost $post) {
    //$post->poll = $this->createpoll();
    $post->updatefiltered();
    return parent::add($post);
  }
  
  public function edit(tpost $post) {
    $post->updatefiltered();
    return parent::edit($post);
  }
  
  public function postsdeleted(array $items) {
    $deleted = implode(',', $items);
    $db = $this->getdb($this->childtable);
    $idpolls = $db->res2id($db->query("select poll from $db->prefix$this->childtable where (id in ($deleted)) and (poll  > 0)"));
    if (count ($idpolls) > 0) {
      $polls = tpolls::instance();
      foreach ($idpolls as $idpoll)       $pols->delete($idpoll);
    }
  }
  
  public function themeparsed($theme) {
    include_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'downloaditems.class.install.php');
    add_downloaditems_to_theme($theme);
  }
  
}//class