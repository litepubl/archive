<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function textrasidebarsInstall($self) {
  $parser = tthemeparser::i();
$parser->lock();
$parser->beforeparse = $self->beforeparse;
$parser->parsed = $self->themeparsed;
$parser->unlock();

  ttheme::clearcache();
}

function textrasidebarsUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}