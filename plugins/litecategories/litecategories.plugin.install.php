<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlitecategoriesInstall($self) {
  tcategories::i()->onlite = $self->onlite;
}

function tlitecategoriesUninstall($self) {
  tcategories::i()->unbind($self);
}