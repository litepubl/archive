<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tvkontakteregservice extends tregservice {

    public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'vkontakte';
$this->data['title'] = 'VKontakte';
$this->data['icon'] = 'vkontakte.png';
$this->data['url'] = '/vkontakte-oauth2callback.php';
}

public function getauthurl() {
$url = 'http://oauth.vk.com/authorize';
$url .= '?scope=
$url .= parent::getauthurl();
return $url;
}

//handle callback
  public function request($arg) {
if ($err = parent::request($arg)) return $err;
$code = $_REQUEST['code'];
$resp = self::http_post::get('https://oauth.vk.com/access_token', array(
'code' => $code,
'client_id' => $this->client_id,
'client_secret' => $this->client_secret,
'redirect_uri' => litepublisher::$site->url . $this->url,
//'grant_type' => 'authorization_code'
));

if ($resp) {
$tokens  = json_decode($resp);
if ($r = http::get('https://api.vk.com/method/getProfiles?uids=' . $tokens->user_id . '&access_token=' . $tokens->access_token)) {
$js = json_decode($r);
$info = $js->response[0];
return $this->adduser(array(
'service' => $this->name,
'uidservice' => $info->uid,
'name' => $info->first_name.' '.$info->last_name,
'website' => 'http://vkontakte.ru/id'.$info->uid
));
}
}

return $this->errorauth();
}

public function gettab($html, $args, $lang) {
$result = $html->p($lang->vkontakte_head . litepublisher::$site->url . $this->url);
$result .= $html->getinput('text', "client_id_$this->name", tadminhtml::specchars($this->client_id), $lang->client_id) ;
$result .= $html->getinput('text', "client_secret_$this->name", tadminhtml::specchars($this->client_secret), $lang->client_secret) ;
return $result;
}

}//class