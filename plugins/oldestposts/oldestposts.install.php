<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function toldestpostsInstall($self) {
  $widgets = twidgets::i();
  $widgets->addclass($self, 'tpost');
}