<?php

class tposts extends TItems {
  public $archives;

  public static function instance() {
    return getnamedinstance('posts', __class__);
  }
  
  public static function unsub($obj) {
    $self = self::instance();
    $self->UnsubscribeClassName(get_class($obj));
  }
  
  protected function create() {
    parent::create();
$this->table = 'posts';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', changed', 'singlecron');
    if (!dbversion) $this->AddDataMap('archives' , array());
  }
  
  public function getitem($id) {
if (dbversion) {
    if ($res = $this->db->select("id = $id")) {
if ($result = tpost::instance($id)) return $result;
} else {
    if (isset($this->items[$id])) return tpost::instance($id);
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
 public function GetWidgetContent($id) {
    global $options;
    $template = template::instance();
    $item = !empty($template->theme['widget']['recentpost']) ? $template->theme['widget']['recentpost'] :
    '<li><strong><a href=\'$options->url$post->url\' rel=\'bookmark\' title=\'Permalink to $post->title\'>$post->title</a></strong><br />
    <small>$post->localdate</small></li>';
    
    $result = '';
    $list = $this->getrecent($this->recentcount);
    foreach ($list as $id) {
      $post = tpost::instance($id);
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function add(tpost $post) {
    if ($post->date == 0) $post->date = time();
    $post->modified = time();
      $post->pagescount = count($post->pages);
    
    $Linkgen = TLinkGenerator::instance();
    if ($post->url == '' ) {
      $post->url = $Linkgen->Create($post, 'post');
    } else {
      $title = $post->title;
      $post->title = trim($post->url, '/');
      $post->url = $Linkgen ->Create($post, 'post');
      $Post->title = $title;
    }

        $urlmap = turlmap::instance();
    if (dbversion) {
      $post->id = TPostTransform ::add($post);
      $post->idurl = $urlmap->add($post->url, get_class($post), $post->id, $post->pagescount);      
$post->db->setvalue($post->id, 'idurl', $post->idurl);
$post->raw->InsertAssoc(array('id' => $post->id, 'rawcontent' => $post->data['rawcontent']));
      
      $db->table = 'pages';
      foreach ($post->pages as $i => $content) {
        $db->InsertAssoc(array('post' => $post->id, 'page' => $i         'content' => $content));
      }
      
      $this->Updated($post);
    } else {
      global $paths;
      $post->id = ++$this->lastid;
      $dir =$paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
      
      $this->lock();
    $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
      $this->Updated($post);
      $post->save();
      $this->unlock();
    }
    $this->Added($post->id);
    $this->Changed();
    $urlmap->ClearCache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $urlmap = turlmap::instance();
    $this->lock();
    
    $oldurl = $urlmap->Find(get_class($post), $post->id);
    if ($oldurl != $post->url) {
      $urlmap->lock();
      $urlmap->Delete($oldurl);
      $Linkgen = TLinkGenerator::instance();
      if ($post->url == '') {
        $post->url = $Linkgen->Create($post, 'post');
      } else {
        $title = $post->title;
        $post->title = trim($post->url, '/');
        $post->url = $Linkgen->Create($post, 'post');
        $post->title = $title;
      }
      $urlmap->Add($post->url, get_class($post), $post->id);
      $urlmap->unlock();
    }
    
    if ($oldurl != $post->url) {
      $urlmap->AddRedir($oldurl, $post->url);
    }
    
    $post->modified = time();
    $this->Updated($post);
    $post->save();
    $this->unlock();
    
    $urlmap->ClearCache();
    
    $this->Edited($post->id);
    $this->Changed();
  }
  
  public function delete($id) {
    if (!$this->ItemExists($id)) return false;
    $urlmap = turlmap::instance();
    if (dbversion) {
      global $db;
      $idurl = $this->db->idvalue($id, 'idurl');
      $urlmap->delete($idurl);
      $this->db->delete("id = $id limit 1");
      $db->table = 'pages';
      $db->delete("post = $id");
    } else {
      global $paths;
      
      $this->lock();
      $post = &TPost::instance($id);
      
      $urlmap->lock();
      $urlmap->Delete($post->url);
      $urlmap->unlock();
      
      unset($this->items[$id]);
      TItem::DeleteItemDir($paths['data']. 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      $this->UpdateArchives();
      $this->unlock();
    }
    $this->deleted($post->id);
    $this->changed();
    $urlmap->ClearCache();
    return true;
  }
  
  public function updated(tpost $post) {
    if (($post->status == 'published') && ($post->date > time())) {
      $post->status = 'future';
if (dbversion) $post->db->setvalue($post->id, 'status', 'future');
    }
    $this->PublishFuture();
if (!dbversion) {
    $this->items[$post->id] = array(
    'date' => $post->date
    );
    if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
    $this->UpdateArchives();
}

    $Cron = tcron::instance();
    $Cron->add('single', get_class($this), 'DoSingleCron', $post->id);
  }
  
  public function UpdateArchives() {
    $this->archives = array();
    foreach ($this->items as $id => $item) {
      if ((!isset($item['status']) || ($item['status'] == 'published')) &&(time() >= $item['date'])) {
        $this->archives[$id] = $item['date'];
      }
    }
    arsort($this->archives,  SORT_NUMERIC);
  }
  
  public function DoSingleCron($id) {
    $this->PublishFuture();
    $GLOBALS['post'] = tpost::instance($id);
    $this->singlecron($id);
    //ping
  }
  
  public function hourcron() {
    $this->PublishFuture();
  }
  
private function publish($id) {
$post = tpost::instance($id);
        $post->status = 'published';
        $this->edit($post);
}

  public function PublishFuture() {
    if (dbversion) {
if ($list = $this->db->idselect("status = 'future' and created <= now() order by created asc")) {
foreach( $list as $id) $this->publish($id);
}
} else {
    foreach ($this->items as $id => $item) {
      if (isset($item['status']) && ($item['status'] == 'future') && ($item['date'] <= time())) $this->publish($id);
    }
  }

public function getarchivescount() {
if (dbversion) return $this->db->getcount("status = 'published');
return count($this->archives);
}
  
  public function getrecent($count) {
if (dbversion) {
return $this->db->idselect("status = 'published'order by created desc limit $count");
} 
    return array_slice(array_keys($this->archives), 0, $count);
}
  }
  
  public function &GetPublishedRange($PageNum, $CountPerPage) {
    $Result= array();
    $Count = count($this->archives);
    $From = ($PageNum - 1) * $CountPerPage;
    if ($From > $Count)  return $Result;
    $To = min($From + $CountPerPage, $Count);
    $Result= array_slice(array_keys($this->archives), $From, $To - $From);
    return $Result;
  }
  
  public function StripDrafts(array &$items) {
    return array_intersect($items, array_keys($this->archives));
  }
  
  public function SortAsArchive(array $items) {
    $result = array();
    foreach ($items as  $id) {
      if (isset($this->archives[$id])) {
        $result[$id] = $this->archives[$id];
      }
    }
    
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
}

?>