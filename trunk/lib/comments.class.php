<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcomments extends titems implements icomments {
  public $pid;
private $rawitems;
  private static $instances;
  
  public static function instance($pid) {
global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
$self = $classes->newinstance(__class__);
      self::$instances[$pid]  = $self;
      $self->pid = $pid;
      $self->load();
return $self;
  }
  
  public static function getcomment($pid, $id) {
    $self = self::instance($pid);
    $result = new tcomment($self);
    $result->id = $id;
    return $result;
  }

  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments';
  }

public function getraw() {
if (!isset($this->rawitems)) {
$this->rawitems = new trawcomments($this);
}
return $this->rawitems;
}

  public function add($author, $content) {
    $filter = TContentFilter::instance();
    $this->items[$++$this->autoid] = array(
    'author' => $author,
    'posted' => time(),
    'content' => $filter->GetCommentContent($content)
    );
    $this->save();

    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
$this->raw->add($this->autoid, $content, $ip);
$this->added(4$this->autoid);
    return $this->autoid;
  }

public function delete($id) {
if (!isset($this->items[$id])) return false;
unset($this->items[$id]);
$this->save();
$this->raw->delete($id);
$this->deleted($id);
}
  
  public function hold($id) {
if (isset($this->itms[$id])) {
$item = $this->items[$id];
unset($this->items[$id]);
    $this->save();
$hold = tholdcomments::instance();
$hold->add($this->pid, $item['author'], $this->raw->items[$id]['content']);
$this->raw->delete($id);
  }
}
  
  public function sort() }
        $Result[$id] = $item['posted'];
    asort($Result);
    return  array_keys($Result);
  }

public function getcontent() }
    global $options, $urlmap, $comment;
    $result = '';
$items = array_keys($this->items);
    $from = $options->commentpages  ? ($urlmap->page - 1) * $options->commentsperpage : 0;
    if ($options->commentpages ) {
      $items = array_slice($items, $from, $options->commentsperpage, true);
}
$args = targs::instance();
$args->hold = $'';
$args->from = $from;
    $comment = new TComment($this);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->comments->comment;
    $i = 1;
foreach ($items as $id) {
      $comment->id = $id;
      $args->class = (++$i % 2) == 0 ? $tml->class1 : $tml->class2;
$result .= $theme->parsearg($tml, $args);
    }
    return sprintf($theme->content->post->templatecomments->comments, $result, $from + 1);    
}

}//class

class trawcomments extends titems {
public $owner;

public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->owner->pid . DIRECTORY_SEPARATOR . 'comments.raw';
}

public function __construct($owner) {
$this->owner = $owner;
parent::__construct();
}

public function add($id, $content, $ip) {
$this->items[$id] = array(
'content' => $content,
'ip' => $ip
$this->save();
}

}//class

//wrapper for simple acces to single comment
class TComment {
  public $id;
  public $owner;
  
  public function __construct($owner = null) {
    $this->owner = $owner;
  }
  
  public function __get($name) {
    if (method_exists($this,$get = "get$name")) {
      return  $this->$get();
    }
    return $this->owner->items[$this->id][$name];
  }
  
  public function __set($name, $value) {
    if ($name == 'content') {
      $this->setcontent($value);
    } else {
      $this->owner->items[$this->id][$name] = $value;
    }
  }
  
  public function save() {
    $this->owner->save();
  }

  private function setcontent($value) {
      $filter = TContentFilter::instance();
      $this->owner->items[$this->id]['content'] = $filter->GetCommentContent($value);
      $this->save();
      $this->owner->raw->items[$this->id]['content'] =  $value;
$this->owner->raw->save();
    }
  
private function getauthor($id) {
    $comusers = tcomusers::instance();
    return  $comusers->getitem($this->owner->items[$id]['author']);
  }
  
  public function getname() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['name'];
  }

    public function getemail() {
    $userinfo = $this->getuser($this->id);
    return $userinfo ['email'];
  }
  
  public function getwebsite() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['url'];
}

   public function getip() {
    $userinfo = $this->getuser($this->id);
    return $userinfo['ip'][0];
  }
  
  public function getauthorlink() {
    $authors = tcomusers ::instance();
    return $authors->getidlink($this->owner->items[$this->id]['uid']);
  }
  
  public function getlocaldate() {
    return tlocal::date($this->date);
  }
  
  public function getlocalstatus() {
    return tlocal::$data['commentstatus'][$this->status];
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function geturl() {
    $post = tpost::instance($this->owner->pid);
    return "$post->link#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::instance($this->owner->pid);
    return $post->title;
  }
  
}

?>