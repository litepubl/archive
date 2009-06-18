<?php

class TProfile extends TEventClass {
 
 public static function &Instance($id = 0) {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  global $Options;
  parent::CreateData();
  $this->basename = 'profile';
  $this->Data = $this->Data + array(
  'nick' => 'admin',
  'dateOfBirth' => date('Y-m-d'),
  'gender' => 'male',
  'img' => '',
  
  'icqChatID' => '',
  'aimChatID' => '',
  'jabberID' => '',
  'msnChatID' => '',
  'yahooChatID' => '',
  'mbox' => '',
  
  'country' => $Options->language,
  'region' => '',
  'city' => '',
  'geourl' => 'http://beta-maps.yandex.ru/?text=',
  'bio' => '',
  'interests' => '',
  'interesturl' => '  http://www.livejournal.com/interests.bml?int='
  );
 }
 
 public function GetFoaf() {
  global $Options;
  $posts = &TPosts::Instance();
  $postscount = count($posts->archives);
  $CommentManager = &TCommentManager::Instance();
  
  $result = "<foaf:nick>$this->nick</foaf:nick>
  <foaf:name>$this->nick</foaf:name>
  <foaf:dateOfBirth>$this->dateOfBirth</foaf:dateOfBirth>
  <foaf:gender>$this->gender</foaf:gender>
  <foaf:img rdf:resource=\"$this->img\" />
  <foaf:icqChatID>$this->icqChatID</foaf:icqChatID>
  <foaf:aimChatID>$this->aimChatID</foaf:aimChatID>
  <foaf:jabberID>$this->jabberID</foaf:jabberID>
  <foaf:msnChatID>$this->msnChatID</foaf:msnChatID>
  <foaf:yahooChatID>$this->yahooChatID</foaf:yahooChatID>
  <foaf:homepage>$Options->url$Options->home</foaf:homepage>
  <foaf:mbox>$this->mbox</foaf:mbox>
  <foaf:weblog
  dc:title=\"$Options->name\"
  rdf:resource=\"$Options->url$Options->home\"/>
  
  <foaf:page>
  <foaf:Document rdf:about=\"$Options->url/profile/\">
  <dc:title>$Options->name Profile</dc:title>
  <dc:description>Full profile, including information such as interests and bio.</dc:description>
  </foaf:Document>
  </foaf:page>
  
  <lj:journaltitle>$Options->name</lj:journaltitle>
  <lj:journalsubtitle>$Options->description</lj:journalsubtitle>
  
  <ya:blogActivity>
  <ya:Posts>
  <ya:feed
  dc:type=\"application/rss+xml\"
  rdf:resource=\"$Options->rss\"/>
  <ya:posted>$postscount</ya:posted>
  </ya:Posts>
  </ya:blogActivity>
  
  <ya:blogActivity>
  <ya:Comments>
  <ya:feed
  dc:type=\"application/rss+xml\"
  rdf:resource=\"$Options->rsscomments\"/>
  <ya:posted>$postscount</ya:posted>
  <ya:received>$CommentManager->count</ya:received>
  </ya:Comments>
  </ya:blogActivity>\n";
  
  if ($this->bio != '') $result .= "<ya:bio>$this->bio</ya:bio>\n";
  
  $result .= $this->GetFoafOpenid();
  $result .= $this->GetFoafCountry();
  $result .= $this->GetFoafInterests();
  return $result;
 }
 
 public function GetFoafInterests() {
  $result = '';
  $list = explode(',', $this->interests);
  foreach ($list as $name) {
   $name = trim($name);
   if (empty($name)) continue;
   $result .= "    <foaf:interest dc:title=\"$name\" rdf:resource=\"$this->interesturl". urlencode($name) . "\" />\n";
  }
  return $result;
  
 }
 
 public function GetFoafOpenid() {
  return '';
  //    <foaf:openid rdf:resource="http://dr-piliulkin.livejournal.com/" />
 }
 
 public function GetFoafCountry() {
  $result = '';
  if ($this->country != '') $result .= "<ya:country dc:title=\"$this->country\"
  rdf:resource=\"$this->geourl" . urlencode($this->country) . "\"/>\n";
  
  if ($this->region != '') $result .="<ya:region dc:title=\"$this->region\"
  rdf:resource=\"$this->geourl". urlencode($this->region) . "\"/>\n";
  
  if ($this->city != '') $result .= "<ya:city dc:title=\"$this->city\"
  rdf:resource=\"$this->geourl". urlencode("$this->country, $this->city") . "\"/>\n";
  
  return $result;
 }
 
 public function Gettitle() {
  return TLocal::$data['profile']['myprofile'];
 }
 
 public function GetTemplateContent() {
  global $Options;
  $lang = &TLocal::$data['profile'];
 $result = "<h2>{$lang['myprofile']}</h2>\n";
  $result .= $this->GetStat();
  
 $result .= "<h2>{$lang['myself']}</h2>\n";
  $result .= $this->GetMyself();
  
 $result .= "<h2>{$lang['contact']}</h2>\n";
  $result .= $this->GetContacts();
  
 if ($this->bio != '') $result .= "<h2>{$lang['bio']}</h2>\n
  <p>$this->bio</p>\n";
  
 $result .= "<h2>{$lang['interests']}</h2>\n";
  $result .= $this->GetMyInterests();
  
  $result .= "<h2>" . TLocal::$data['default']['myfriends'] . "</h2>\n";
  $result .= $this->GetFriendsList();
  return $result;
 }
 
 private function GetStat() {
  $posts = &TPosts::Instance();
  $CommentManager = &TCommentManager::Instance();
  return sprintf(TLocal::$data['profile']['statistic'], count($posts->archives), $CommentManager->count) . "\n";
 }
 
 private function GetMyself() {
  $lang = TLocal::$data['profile'];
  $result = array();
  if ($this->img != '') $result[] = "<img src=\"$this->img\" />";
 if ($this->nick != '') $result[] = "{$lang['nick']} $this->nick";
  if (($this->dateOfBirth != '')  && @sscanf($this->dateOfBirth , '%d-%d-%d', $y, $m, $d)) {
   $date = mktime(0,0,0, $m, $d, $y);
   $ldate = TLocal::date($date);
   $result[] = sprintf($lang['birthday'], $ldate);
  }
  
  $result[] = $this->gender == 'female' ? $lang['female'] : $lang['male'];
  
  if (!$this->country != '') $result[] = $this->country;
  if (!$this->region != '') $result[] = $this->region;
  if (!$this->city != '') $result[] = $this->city;
  return "<p>\n" . implode(", ", $result) . "</p>\n";
 }
 
 private function GetContacts() {
  $contacts = array(
  'icqChatID' => 'ICQ',
  'aimChatID' => 'AIM',
  'jabberID' => 'Jabber',
  'msnChatID' => 'MSN',
  'yahooChatID' => 'Yahoo',
  'mbox' => 'E-Mail'
  );
  $lang = TLocal::$data['profile'];
  $result = "<table>
  <thead>
  <tr>
 <th align=\"left\">{$lang['contactname']}</th>
 <th align=\"left\">{$lang['value']}</th>
  </tr>
  </thead>
  <tbody>\n";
  
  foreach ($contacts as $contact => $name) {
   if ($this->Data[$contact] == '') continue;
   $result .= "<tr>
   <td align=\"left\">$name</td>
  <td align=\"left\">{$this->Data[$contact]}</td>
   </tr>\n";
  }
  
  $result .= "</tbody >
  </table>";
  return $result;
 }
 
 private function GetMyInterests() {
  $result = "<p>\n";
  $list = explode(',', $this->interests);
  foreach ($list as $name) {
   $name = trim($name);
   if (empty($name)) continue;
   $result .= "<a href=\"$this->interesturl". urlencode($name). "\">$name</a>,\n";
  }
  $result .= "</p>\n";
  return $result;
 }
 
 private function GetFriendsList() {
  global $Options;
  $result = "<p>\n";
  $foaf = &TFoaf::Instance();
  foreach ($foaf->items As $id => $friend) {
  $url = $foaf->redir ?"$Options->url$foaf->redirlink{$Options->q}friend=$id" : $friend['blog'];
  $result .= "<a href=\"$url\" rel=\"friend\">{$friend['nick']}</a>,\n";
  }
  $result .= "</p>\n";
  return $result;
 }
 
}//class

?>