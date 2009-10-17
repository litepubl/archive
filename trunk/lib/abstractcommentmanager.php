<?php

abstract class TAbstractCommentManager extends TItems {

  abstract   public function getcomment($id);
abstract   public function GetWidgetContent($id);
abstract   public function PostDeleted($postid);
abstract   public function add($postid, $name, $email, $url, $content);
abstract   public function AddToPost(&$post, $userid, $content);
abstract   public function AddPingback(&$post, $url, $title) {
abstract   public function delete($id);
abstract   public function hasauthor($author);
abstract   public function UserHasApproved($userid);
abstract   public function HasApprovedCount($userid, $count);
abstract   public function setstatus($id, $value);
abstract   public function UserCanAdd($userid);
abstract   public function Getholditems();

  protected function create() {
    parent::create();
$this->table = 'comments';
$this->rawtable = 'rawcomments';
    $this->basename = 'commentmanager';
    $this->AddEvents('edited', 'changed', 'approved');
  }
    
  protected function CreateStatus($userid, $content) {
    global $options;
    if ($options->DefaultCommentStatus == 'approved') return 'approved';
    if ($this->UserHasApproved($userid)) return  'approved';
    return 'hold';
  }
  
  protected function DoAdded($id) {
    $this->DoChanged($this->items[$id]['pid']);
    $this->CommentAdded($id);
    $this->Added($id);
  }
  
  public function DoChanged($postid) {
    template::WidgetExpired($this);
    
    $post = TPost::instance($postid);
    $Urlmap = TUrlmap::instance();
    $Urlmap->SetExpired($post->url);
    
    $this->changed($postid);
  }
  
  public function CommentAdded($id) {
    global $options;
    if (!$this->options->SendNotification) return;
    $comment = $this->getcomment($id);
    $html = THtmlResource::instance();
    $html->section = 'moderator';
    $lang = TLocal::instance();
    eval('$subject = "' . $html->subject . '";');
    eval('$body = "'. $html->body . '";');
    TMailer::SendMail($options->name, $options->fromemail,
    'admin', $options->email,  $subject, $body);
  }
  
}//class

?>