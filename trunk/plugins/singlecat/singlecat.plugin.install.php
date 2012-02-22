<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsinglecatInstall($self) {
  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tsinglecatUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();
}