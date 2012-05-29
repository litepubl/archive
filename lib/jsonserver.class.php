<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsonserver extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'jsonserver';
    $this->cache = false;
    $this->addevents('beforerequest', 'beforecall', 'aftercall');
    $this->data['eventnames'] = &$this->eventnames;
    $this->map['eventnames'] = 'eventnames';
    $this->data['url'] = '/admin/jsonserver.php';
  }
  
  public function getpostbody() {
    global$HTTP_RAW_POST_DATA;
    if ( !isset( $HTTP_RAW_POST_DATA ) ) {
      $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
    }
    if ( isset($HTTP_RAW_POST_DATA) ) {
      $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
    }
    
    if (litepublisher::$debug) {
      tfiler::log("request:\n" . $HTTP_RAW_POST_DATA, 'json.txt');
      $reqname = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR  . 'request.json';
      file_put_contents($reqname, $HTTP_RAW_POST_DATA);
      @chmod($reqname, 0666);
      //$HTTP_RAW_POST_DATA = file_get_contents($GLOBALS['paths']['home'] . 'raw.txt');
    }
    
    return $HTTP_RAW_POST_DATA;
  }
  
  public function get_json_args() {
    if ($s = trim($this->getpostbody())) {
      return json_decode($s, true);
    }
    return false;
  }
  
  public function request($param) {
    //sleep(20);
    //$this->addevent('comments_get_hold', 'tjsoncomments', 'comments_get_hold');
    $this->beforerequest();
    if (isset($_GET['method'])) {
      $method = $_GET['method'];
      $args = $_GET;
    } elseif (isset($_POST['method'])) {
      tguard::post();
      $method = $_POST['method'];
      $args = $_POST;
    } elseif ($args = $this->get_json_args()) {
      if (isset($args['method'])) {
        $method = $args['method'];
      } else {
        return 403;
      }
    } else {
      return 403;
    }
    if (!isset($this->events[$method])) return 403;
    if (isset($args['litepubl_user'])) $_COOKIE['litepubl_user'] = $args['litepubl_user'];
    if (isset($args['litepubl_user_id'])) $_COOKIE['litepubl_user_id'] = $args['litepubl_user_id'];
    $a = array($args);
    $this->callevent('beforecall', $a);
    
    try {
      //tfiler::log(var_export($_GET, true));
      //tfiler::log(var_export($args, true));
      $result = $this->callevent($method, $a);
      //dumpvar($result);
      //tfiler::log(var_export($result, true));
    } catch (Exception $e) {
      if (403 == $e->getCode()) {
        $result = '<?php Header(\'HTTP/1.0 403 Forbidden\', true, 403); ?>';
      } else {
        //500 error
        $result = '<?php header(\'HTTP/1.1 500 Internal Server Error\', true, 500); ?>';
      }
      $result .= $e->getMessage();
      return $result;
    }
    
    $this->callevent('aftercall', array(&$result, $args));
    $js = json_encode($result);
    //if (litepublisher::$debug) tfiler::log("response:\n".$js, 'json.txt');
    
    return "<?php
    header('Connection: close');
    header('Content-Length: ". strlen($js) . "');
    header('Content-Type: text/javascript');
    //header('Content-Type: application/json');
    header('Date: ".date('r') . "');
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    header('X-Pingback: ". litepublisher::$site->url . "/rpc.xml');
    ?>" . $js;
  }
  
  public function addevent($name, $class, $func) {
    if (!in_array($name, $this->eventnames)) $this->eventnames[] = $name;
    return parent::addevent($name, $class, $func);
  }
  
}//class