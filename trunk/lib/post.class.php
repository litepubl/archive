<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tpost extends titem implements  itemplate {
  private $dateformater;
  
  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }

  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'index';
  }
  
  
  protected function create() {
    global $options;
$this->table = 'posts';
    $this->data= array(
    'id' => 0,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0, 
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => '',
    'description' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
'files' => array(),
    'status' => 'published',
    'commentsenabled' => $options->commentsenabled,
    'pingenabled' => $options->pingenabled,
    'rssenabled' => true,
    'password' => '',
    'template' => '',
    'subtheme' => '',
'icon' => 0,
    'pages' => array()
    );
  }
  
  public function getcomments() {
return TComments::instance($this->id);
  }
  
  public function getprev() {
if (dbversion) {
if ($id = $this->db->findid(sprintf('status = `published` and posted < `%s` order by posted desc', sqldate($this->posted)))) {
return self::instance($id);
}
return null;
} else {
    $posts = tposts::instance();
    $keys = array_keys($posts->archives);
    $i = array_search($this->id, $keys);
    if ($i < count($keys) -1) return self::instance($keys[$i + 1]);
}
    return null;
  }
  
  public function getnext() {
if (dbversion) {
if ($id = $this->db->findid(sprintf('status = `published` and posted > `%s` order by posted desc', sqldate($this->posted)))) {
return self::instance($id);
}
return null;
} else {
    $posts = tposts::instance();
    $keys = array_keys($posts->archives);
    $i = array_search($this->id, $keys);
    if ($i > 0 ) return self::instance($keys[$i - 1]);
}
    return null;
  }
  
  public function Getlink() {
    global $options;
    return $options->url . $this->url;
  }
  
  public function Setlink($link) {
    global $options;
    if ($UrlArray = parse_url($link)) {
      $url = $UrlArray['path'];
      if (!empty($UrlArray['query'])) $url .= '?' . $UrlArray['query'];
      $this->url = $url;
    }
  }
  
  public function getrsslink() {
    global $options;
    return "$options->url/comments/$this->id/";
  }
  
  public function Getpubdate() {
    return date('r', $this->posted);
  }
  
  public function Setpubdate($date) {
    $this->data['posted'] = strtotime($date);
  }
  
  //template
  public function getexcerptcategories() {
return $this->getcommontagslinks('categories', 'category', true);
}

  public function getexcerpttags() {
return $this->getcommontagslinks('tags', 'tag', true);
}

public function getcategorieslinks() {
return $this->getcommontagslinks('categories', 'category', false);
}

  public function Gettagslinks() {
return $this->getcommontagslinks('tags', 'tag', false);
}

private function getcommontagslinks($names, $name, $excerpt) {
global $classes;
$theme = ttheme::instance();
$tml = $excerpt ? $theme->excerpts : $theme->post;
    $tags= $classes->$names;
$tags->loaditems($this->$names);
$args = targs::instance();
$list = array();
    foreach ($this->$names as $id) {
$args->add($tags->items[$id]);
if ($item['icon'] != 0) {
$icons = ticons::instance();
$args->icon = $icons->getlink($item['icon']);
}
$list[] = $theme->parsearg($tml[$name], $args);
    }
$result = implode($tml[$name . 'dvider'], $list);
    return sprintf($theme->parse($tml[$names]), $result);
  }
  
  public function getlocaldate() {
    return tlocal::date($this->posted);
  }
  
  public function getdateformat() {
    if (isset($this->dateformater)){
    $this->dateformater->date = $this->posted;
} else {
 $this->dateformater = new tdateformater($this->posted);
}
    return $this->dateformater;
  }
  
  public function getmorelink() {
global $post;
    if ($this->moretitle == '') return '';
$post = $this;
$theme = ttheme::instance();
return $theme->parse($theme->excerpts['more']);
  }
  
   public function gettagnames() {
    if (count($this->tags) == 0) return '';
    $tags = ttags::instance();
    return implode(', ', $tags->getnames($this->tags));
  }
  
  public function settagnames($names) {
    $tags = ttags::instance();
    $this->tags=  $tags->createnames($names);
  }
  
  public function getcatnames() {
    if (count($this->categories) == 0)  return array();
    $categories = tcategories::instance();
    return $categories->getnames($this->categories);
  }
  
  public function setcatnames($names) {
    $categories = tcategories::instance();
    $this->categories = $categories->createnames($names);
    if (count($this->categories ) == 0) $this->dat['categories '][] = $categories->defaultid;
  }
  
  //ITemplate
  public function request($id) {
    parent::request($id);
    if ($this->status != 'published') return 404;
  }
  
  public function gettitle() {
if ($this->data['title2'] != '') return $this->data['title2'];
    return $this->data['title'];
  }
  
  public function gethead() {
    $result = '';
    if ($prev = $this->prev) $result .= "<link rel=\"prev\" title=\"$prev->title\" href=\"$prev->link\" />\n";
    if ($next = $this->next) $result .= "<link rel=\"next\" title=\"$next->title\" href=\"$next->link\" />\n";
    if ($this->commentsenabled && ($this->commentscount > 0))  {
      $lang = tlocal::instance('comment');
      $result .= "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"$lang->onpost $this->title\" href=\"$this->rsslink\" />\n";
    }
    return $result;
  }
  
  public function getkeywords() {
    return $this->Gettagnames();
  }
  
  public function getdescription() {
    return $this->data['description'];
  }

public function geticonurl() {
if ($this->icon == 0) return '';
$icons = ticons::instance();
return $icons->geturl($this->icon);
}

public function geticonlink() {
if ($this->icon == 0) return '';
return "<img src=\"$this->iconurl\" alt=\"$this->title\" />";
}


public function getscreenshots() {
if (count($this->files) === 0) return '';
$files = tfiles::instance();
return $files->getscreenshots($this->files);
}

public function getfilelist() {
if (count($this->files) === 0) return '';
$files = tfiles::instance();
return $files->getlist($this->files, false);
}
  
  public function GetTemplateContent() {
global $post;
$post = $this;
$theme = ttheme::instance();
    return $theme->parse($theme->post['tml']);
  }

public function getrsscomments() {
global $post;
    if ($this->commentsenabled && ($this->commentscount > 0)) {
$post = $this;
$theme = ttheme::instance();
return $theme->parse($theme->post['rss']);
}
return '';
}

public function getprevnext() {
global $prevpost, $nextpost;
    $result = '';
$theme = ttheme::instance();
    if ($prevpost = $this->prev) {
      $result .= $theme->parse($theme->post['prev']);
    }
    
    if ($nextpost = $post->next) {
      $result .= $theme->parse($theme->post['next']);
    }
    
    if ($result != '') $result = sprintf($theme->parse($theme->post['prevnext']), $result);
return $result;
}

public function getcommentslink() {
$tc = ttemplatecomments::instance();
return $tc->getcommentslink($this);
}

public function  gettemplatecomments() {
    if (($this->commentscount == 0) && !$this->commentsenabled) return '';
    if ($this->haspages && ($this->commentpages < $urlmap->page)) return $this->getcommentslink();
$tc = ttemplatecomments::instance();
return $tc->getcomments($this->id);
}

private function replacemore($content) {
global $post;
$post = $this;
$theme = ttheme::instance();
$more = $theme->parse($theme->post['more']);
$tag = '<!--more-->';
if ($i =strpos($content, $tag)) {
return str_replace($tag, $more, $content);
} else {
return $more . $content;
}
}
  
  public function getcontent() {
    $result = '';
$posts = tposts::instance();
$posts->beforecontent($this->id, &$result);
    $urlmap = turlmap::instance();
    if ($urlmap->page == 1) {
      $result .= $this->filtered;
$result = $this->replacemore($result);
    } elseif ($s = $this->getpage($urlmap->page - 1)) {
      $result .= $s;
    } elseif ($urlmap->page <= $this->commentpages) {
      //$result .= '';
    } else {
      $lang = tlocal::instance();
      $result .= $lang->notfound;
    }

    if ($this->haspages) {
$theme = theme::instance();
$result .= $theme->getpages($this->url, $urlmap->page, $this->countpages);
}

$post->aftercontent($this->id, &$result);
    return $result;
  }
  
  public function setcontent($s) {
    if ($s <> $this->rawcontent) {
      $this->rawcontent = $s;
      $filter = TContentFilter::instance();
      $filter->SetPostContent($this,$s);
    }
  }
  
  public function getrawcontent() {
    if (dbversion && ($this->id > 0) && empty($this->data['rawcontent'])) {
      $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
    }
    return $this->data['rawcontent'];
  }
  
protected function getrawdb() {
global $db;
$db->table = 'rawposts';
return $db;
}
  
  public function getpage($i) {
if ($i == 0) return $this->filtered;
    if (dbversion && ($this->id > 0)) {
     if ($r = $this->getdb('pages')->getassoc("(id = $this->id) and (page = $i) limit 1")) {
        return $r['content'];
      }
    } elseif ( isset($This->data['pages'][$i]))  {
      return $this->data['pages'][$i];
    }
    return false;
  }
  
  public function addpage($s) {
    $this->data['pages'][] = $s;
  }
  
  public function deletepages() {
    $this->data['pages'] = array();
  }
  
  public function gethaspages() {
    return ($this->pagescount > 1) || ($this->commentpages > 1);
  }

public function getpagescount() {
if (dbversion && ($this->id > 0)) return $this->data['pagescount'];
return isset($this->data['pages']) ? count($this->data['pages']) : 1;
}

    public function getcountpages() {
    return max($this->pagescount, $this->commentpages);
  }
  
  public function getcommentpages() {
    global $options;
    if (!$options->commentpages) return 1;
    return ceil($this->commentscount / $options->commentsperpage);
  }
  
  public function getcommentscount() {
    if (dbversion) {
      return $this->data['commentscount'];
    } else {
      return $this->comments->count;
    }
  }
  
  //db
  public function LoadFromDB() {
global $db;
    if ($res = $db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
where $db->posts.id = $this->id and  $db->urlmap.id  = $db->posts.idurl limit 1")) {
      $res->fetch(PDO::FETCH_INTO , TPostTransform::instance($this));
return true;
    }
return false;
  }
  
 protected function SaveToDB() {
TPostTransform ::instance($this)->save();
}

}//class

?>