<?php

function TLinksWidgetInstall(&$self) {
  tlocal::loadlang('admin');
  $lang = tlocal::$data['installation'];
  $self->Add($lang['homeurl'], $lang['homedescription'], $lang['homename']);
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->AddGet($self->redirlink, get_class($self), null);
  
  $robots = &TRobotstxt ::Instance();
  $robots->AddDisallow($self->redirlink);
  $robots->Save();
}

function TLinksWidgetUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
  TUrlmap::unsub($self);
}

?>