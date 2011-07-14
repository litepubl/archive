<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticketeditor extends tposteditor {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::instance();
  $result .= $template->getready('$("#tabs, #contenttabs").tabs({ cache: true });');
    return $result;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      tlocal::loadsection('admin', 'tickets', dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
      return tlocal::$data['tickets']['editor'];
    }
  }
  
  public function request($id) {
    if ($s = parent::request($id)) return $s;
    $this->basename = 'tickets';
    if ($this->idpost > 0) {
      $ticket = tticket::instance($this->idpost);
      if ((litepublisher::$options->group == 'ticket') && (litepublisher::$options->user != $ticket->author)) return 403;
    }
  }
  
  public function gethtml($name = '') {
    $html = tadminhtml::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $html->addini('tickets', $dir . 'html.ini');
    //$html->section = 'tickets';
    tlocal::loadsection('', 'ticket', $dir);
    tlocal::loadsection('admin', 'tickets', $dir);
    tlocal::$data['tickets'] = tlocal::$data['ticket'] + tlocal::$data['tickets'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $this->basename = 'tickets';
    $ticket = tticket::instance($this->idpost);
    ttheme::$vars['ticket'] = $ticket;
    $args = targs::instance();
    $args->id = $this->idpost;
    $args->title = htmlspecialchars_decode($ticket->title, ENT_QUOTES);
    $args->categories = $this->getpostcategories($ticket);
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$ticket->id&get");
    $ajaxeditor = tajaxposteditor ::instance();
    $args->raw = $ajaxeditor->geteditor('raw', $ticket->rawcontent, true);
    
    $html = $this->html;
    $lang = tlocal::instance('tickets');
    
    $args->code = $html->getinput('editor', 'code', tadminhtml::specchars($ticket->code), $lang->codetext);
    
    $args->fixed = $ticket->state == 'fixed';
    $types = array(
    'bug' => tlocal::$data['ticket']['bug'],
    'feature' => tlocal::$data['ticket']['feature'],
    'support' => tlocal::$data['ticket']['support'],
    'task' => tlocal::$data['ticket']['task'],
    );
    
    $args->typecombo= $html->array2combo($types, $ticket->type);
    $args->typedisabled = $ticket->id == 0 ? '' : "disabled = 'disabled'";
    
    $states =array();
    foreach (array('fixed', 'opened', 'wontfix', 'invalid', 'duplicate', 'reassign') as $state) {
      $states[$state] = tlocal::$data['ticket'][$state];
    }
    $args->statecombo= $html->array2combo($states, $ticket->state);
    
    $prio = array();
    foreach (array('trivial', 'minor', 'major', 'critical', 'blocker') as $p) {
      $prio[$p] = tlocal::$data['ticket'][$p];
    }
    $args->priocombo = $html->array2combo($prio, $ticket->prio);
    
    if ($ticket->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    /*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return;
    */
    extract($_POST, EXTR_SKIP);
    $tickets = ttickets::instance();
    $this->basename = 'tickets';
    $html = $this->html;
    
    // check spam
    if ($id == 0) {
      $newstatus = 'published';
      if (litepublisher::$options->group == 'ticket') {
        $hold = $tickets->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
        $approved = $tickets->db->getcount('status = \'published\' and author = '. litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 2) return $html->h4->noapproved;
          $newstatus = 'draft';
        }
      }
    }
    if (empty($title)) {
      $lang =tlocal::instance('editor');
      return $html->h4->emptytitle;
    }
    $ticket = tticket::instance((int)$id);
    $ticket->title = $title;
    $ticket->categories = self::processcategories();
    if (isset($tags)) $ticket->tagnames = $tags;
    if ($ticket->author == 0) $ticket->author = litepublisher::$options->user;
    if (isset($files))  {
      $files = trim($files);
      $ticket->files = $files == '' ? array() : explode(',', $files);
    }
    
    $ticket->content = $raw;
    $ticket->code = $code;
    $ticket->prio = $prio;
    $ticket->state = $state;
    $ticket->version = $version;
    $ticket->os = $os;
    if (litepublisher::$options->group != 'ticket') $ticket->state = $state;
    if ($id == 0) {
      $ticket->status = $newstatus;
      $ticket->type = $type;
      $ticket->closed = time();
      $id = $tickets->add($ticket);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
      if (litepublisher::$options->group == 'ticket') {
        $users =tusers::instance();
        $user = $users->getitem(litepublisher::$options->user);
        $comusers = tcomusers::instance();
        $uid = $comusers->add($user['name'], $user['email'], $user['url'], '');
        $comusers->setvalue($uid, 'cookie', $user['cookie']);
        $subscribers = tsubscribers::instance();
        //$subscribers->update($id, $uid, true);
        $subscribers->add($id, $uid);
      }
    } else {
      $tickets->edit($ticket);
    }
    
    return $html->h4->successedit;
  }
  
}//class
?>