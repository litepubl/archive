<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tstatfooter extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getfooter() {
    return ' | <?php echo round(memory_get_usage()/1024/1024, 2), \'MB | \';' .
    //' echo round(memory_get_peak_usage(true)/1024/1024, 2), \'MB | \';' .
    ' echo round(microtime(true) - litepublisher::$microtime, 2), \'Sec \'; ?>';
  }
  
  public function install() {
    $footer = $this->getfooter();
    $template = ttemplate::instance();
    if (!strpos($template->footer, $footer)) {
      $template->footer .= $footer;
      $template->save();
    }
  }
  
  public function uninstall() {
    $footer = $this->getfooter();
    $template = ttemplate::instance();
    $template->footer = str_replace($footer, '', $template->footer);
    $template->save();
  }
  
}//class
?>