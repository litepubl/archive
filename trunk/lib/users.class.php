<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusers extends titems {
  public $grouptable;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'users';
    $this->table = 'users';
    $this->grouptable = 'usergroup';
    $this->autoid = 1;
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    $db = litepublisher::$db;
    while ($item = $db->fetchassoc($res)) {
      $id = (int) $item['id'];
      $item['idgroups'] = tdatabase::str2array($item['idgroups']);
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function getitem($id) {
    if ($id == 1) return array(
    'email' =>litepublisher::$options->email,
'name' => litepublisher::$options->name,
'website' => litepublisher::$site->url . '/',
    'password' => litepublisher::$options->password,
    'cookie' => litepublisher::$options->cookie,
    'expired' => sqldate(litepublisher::$options->cookieexpired ),
    'status' => 'approved',
    'idgroups' => array(1)
    );
    
    return parent::getitem($id);
  }
  
  public function add(array $values) {
    $email = trim($values['email']);
    if ($this->emailexists($email)) return false;
    $groups = tusergroups::i();
    $idgroups = $groups->cleangroups($values['idgroups']);
    if (count($idgroups) == 0) $idgroups = array($groups->getidgroup($groups->defaultgroup));
    
    $password = empty($values['password']) ? md5uniq() : $values['password'];
    $password = basemd5(sprintf('%s:%s:%s', $email,  litepublisher::$options->realm, $password));
    
    $item = array(
    'email' => $email,
'name' =>isset($values['name']) ? trim($values['name']) : '', 
'website' => isset($values['website']) ? trim($values['website']) : '',
    'password' => $password,
    'cookie' =>  md5uniq(),
    'expired' => sqldate(),
    'idgroups' => implode(',', $idgroups),
    'trust' => 0,
    'status' => $groups->ingroup(litepublisher::$options->user, 'admin') ? 'approved' : 'wait'
    );
    
    $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
    $item['idgroups'] = $idgroups;
    $this->items[$id] = $item;
    if ($this->dbversion) {
      $this->setgroups($id, $item['idgroups']);
    } else {
      $this->save();
    }
    
tuserpages::i()->add($id);
    $this->added($id);
    return $id;
  }
  
  public function edit($id, array $values) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    foreach ($item as $k => $v) {
      if (!isset($values[$k])) continue;
      switch ($k) {
        case 'password':
        if ($values['password'] != '') {
          $item['password'] = basemd5(sprintf('%s:%s:%s', $values['email'],  litepublisher::$options->realm, $values['password']));
        }
        break;
        
        case 'idgroups':
        $groups = tusergroups::i();
        $item['idgroups'] = $groups->cleangroups($values['idgroups']);
        break;
        
        default:
        $item[$k] = trim($values[$k]);
      }
    }
    
    $this->items[$id] = $item;
    $item['id'] = $id;
    if ($this->dbversion) {
      $this->setgroups($id, $item['idgroups']);
      $item['idgroups'] = implode(',', $item['idgroups']);
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
    
    $pages = tuserpages::i();
    $pages->edit($id, $values);
    return true;
  }
  
  protected function setgroups($id, array $idgroups) {
    $this->items[$id]['idgroups'] = $idgroups;
    if ($this->dbversion) {
      $db = $this->getdb($this->grouptable);
      $db->delete("iduser = $id");
      foreach ($idgroups as $idgroup) {
        $db->add(array(
        'iduser' => $id,
        'idgroup' => $idgroup
        ));
      }
    }
  }
  
  public function delete($id) {
    if ($this->dbversion) $this->getdb($this->grouptable)->delete('iduser = ' .(int)$id);
    tuserpages::i()->delete($id);
    return parent::delete($id);
  }
  
  public function emailexists($email) {
    if ($email == litepublisher::$options->email) return 1;
    if ($this->dbversion) {
      return $this->db->findid('email = '. dbquote($email));
    } else {
      foreach ($this->items as $id => $item) {
        if ($email == $item['email']) return true;
      }
      return false;
    }
  }
  
  public function getpassword($id) {
    return $id == 1 ? litepublisher::$options->password : $this->getvalue($id, 'password');
  }
  
  public function changepassword($id, $password) {
    $item = $this->getitem($id);
    $this->setvalue($id, 'password', basemd5(sprintf('%s:%s:%s', $item['email'],  litepublisher::$options->realm, $password)));
  }
  
  public function approve($id) {
    if (dbversion) {
      $this->db->setvalue($id, 'status', 'approved');
      if (isset(            $this->items[$id])) $this->items[$id]['status'] = 'approved';
    } else {
      $this->items[$id]['status'] = 'approved';
      $this->save();
    }
    $pages = tuserpages::i();
    if ($pages->createpage) $pages->addpage($id);
  }
  
  public function auth($email,$password) {
    $password = basemd5(sprintf('%s:%s:%s', $email,  litepublisher::$options->realm, $password));
    if ($this->dbversion) {
      $email = dbquote($email);
      if (($a = $this->select("email = $email and password = '$password'", 'limit 1')) && (count($a) > 0)) {
        $item = $this->getitem($a[0]);
        if ($item['status'] == 'wait') $this->approve($item['id']);
        return (int) $item['id'];
      }
    } else {
      foreach ($this->items as $id => $item) {
        if (($email == $item['email']) && ($password = $item['password'])) {
          if ($item['status'] == 'wait') $this->approve($id);
          return $id;
        }
      }
    }
    return  false;
  }
  
  public function authcookie($cookie) {
    $cookie = (string) $cookie;
    if (empty($cookie)) return false;
    $cookie = basemd5( $cookie . litepublisher::$secret);
    if ($cookie == basemd5(litepublisher::$secret)) return false;
    if ($id = $this->findcookie($cookie)) {
      $item = $this->getitem($id);
      if (strtotime($item['expired']) > time()) return  $id;
    }
    return false;
  }
  
  public function findcookie($cookie) {
    if ($this->dbversion) {
      $cookie = dbquote($cookie);
      if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
        return (int) $a[0];
      }
    } else {
      foreach ($this->items as $id => $item) {
        if ($cookie == $item['cookie']) return $id;
      }
    }
    return false;
  }
  
  public function getgroupname($id) {
    $item = $this->getitem($id);
    $groups = tusergroups::i();
    return $groups->items[$item['idgroups'][0]]['name'];
  }
  
  public function clearcookie($id) {
    $this->setcookie($id, '', 0);
  }
  
  public function setcookie($id, $cookie, $expired) {
    if ($cookie != '') $cookie = basemd5($cookie . litepublisher::$secret);
    $expired = sqldate($expired);
    if (isset($this->items[$id])) {
      $this->items[$id]['cookie'] = $cookie;
      $this->items[$id]['expired'] = $expired;
    }
    
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
      'cookie' => $cookie,
      'expired' => $expired
      ));
    } else {
      $this->save();
    }
  }
  
  public function optimize() {
    if ($this->dbversion) {
      $time = sqldate(strtotime('-1 day'));
      $pagetable = litepublisher::$db->prefix . 'userpage';
      $delete = $this->db->idselect("status = 'wait' and id in (select id from $pagetable where registered < '$time')");
      if (count($delete) > 0) {
        $this->db->delete(sprintf('id in (%s)', implode(',', $delete)));
        $this->getdb($this->grouptable)->delete(sprintf('iduser in (%s)', implode(',', $delete)));
        $pages = tuserpages::i();
        foreach ($delete as $id) {
          $pages->delete($id);
        }
      }
    } else {
      $pages = tuserpages::i();
      $pages->lock();
      $time = strtotime('-1 day');
      $deleted = false;
      foreach ($this->items as $id => $item) {
        if (($item['status'] == 'wait') && ($pages->items[$id]['registered'] < $time)) {
          unset($this->items[$id]);
          $pages->delete($id);
          $deleted = true;
        }
      }
      if ($deleted) $this->save();
      $pages->unlock();
    }
  }
  
}//class