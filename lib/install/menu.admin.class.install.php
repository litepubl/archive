<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tadminmenusInstall($self) {
  $self->lock();
  $self->heads = '  <link type="text/css" href="$site.files/js/jquery/ui-$site.jqueryui_version/redmond/jquery-ui-$site.jqueryui_version.custom.css" rel="stylesheet" />
  <script type="text/javascript" src="$site.files$template.jsmerger_admin"></script>';
  
  //posts
  $posts = $self->createitem(0, 'posts', 'author', 'tadminposts');
  {
    $id = $self->createitem($posts, 'editor', 'author', 'tposteditor');
    $self->items[$id]['title'] = tlocal::get('names', 'newpost');
    $self->createitem($posts, 'categories', 'editor', 'tadmintags');
    $self->createitem($posts, 'tags', 'editor', 'tadmintags');
    $self->createitem($posts, 'staticpages', 'editor', 'tadminstaticpages');
  }
  
  $moder = $self->createitem(0, 'comments', 'moderator', 'tadminmoderator');
  {
    $self->createitem($moder, 'hold', 'moderator', 'tadminmoderator');
    if (dbversion) $self->createitem($moder, 'holdrss', 'moderator', 'tadminmoderator');
    $self->createitem($moder, 'pingback', 'moderator', 'tadminmoderator');
    $self->createitem($moder, 'authors', 'moderator', 'tadminmoderator');
  }
  
  $plugins = $self->createitem(0, 'plugins', 'admin', 'tadminplugins');
  $files = $self->createitem(0, 'files', 'author', 'tadminfiles');
  {
    $self->createitem($files, 'image', 'editor', 'tadminfiles');
    $self->createitem($files, 'video', 'editor', 'tadminfiles');
    $self->createitem($files, 'audio', 'editor', 'tadminfiles');
    $self->createitem($files, 'icon', 'editor', 'tadminfiles');
    $self->createitem($files, 'deficons', 'editor', 'tadminicons');
    $self->createitem($files, 'bin', 'editor', 'tadminfiles');
  }
  
  $views = $self->createitem(0, 'views', 'admin', 'tadminviews');
  {
    $self->createitem($views, 'addview', 'admin', 'tadminviews');
    $self->createitem($views, 'themes', 'admin', 'tadminthemes');
    $self->createitem($views, 'edittheme', 'admin', 'tadminthemetree');
    $self->createitem($views, 'themefiles', 'admin', 'tadminthemefiles');
    $self->createitem($views, 'widgets', 'admin', 'tadminwidgets');
    $self->createitem($views, 'addcustom', 'admin', 'tadminwidgets');
    $self->createitem($views, 'group', 'admin', 'tadminviews');
    $self->createitem($views, 'defaults', 'admin', 'tadminviews');
    $self->createitem($views, 'spec', 'admin', 'tadminviews');
    $self->createitem($views, 'headers', 'admin', 'tadminviews');
    $self->createitem($views, 'jsmerger', 'admin', 'tadminjsmerger');
  }
  
  $menu = $self->createitem(0, 'menu', 'editor', 'tadminmenumanager');
  {
    $id = $self->createitem($menu, 'edit', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::get('menu', 'addmenu');
    $id = $self->createitem($menu, 'editfake', 'editor', 'tadminmenumanager');
    $self->items[$id]['title'] = tlocal::get('menu', 'addfake');
  }
  
  $opt = $self->createitem(0, 'options', 'admin', 'tadminoptions');
  {
    $self->createitem($opt, 'home', 'admin', 'tadminoptions');
    $self->createitem($opt, 'mail', 'admin', 'tadminoptions');
    $self->createitem($opt, 'rss', 'admin', 'tadminoptions');
    $self->createitem($opt, 'view', 'admin', 'tadminoptions');
    $self->createitem($opt, 'comments', 'admin', 'tadminoptions');
    $self->createitem($opt, 'ping', 'admin', 'tadminoptions');
    $self->createitem($opt, 'links', 'admin', 'tadminoptions');
    $self->createitem($opt, 'cache', 'admin', 'tadminoptions');
    $self->createitem($opt, 'catstags', 'admin', 'tadminoptions');
    $self->createitem($opt, 'secure', 'admin', 'tadminoptions');
    $self->createitem($opt, 'robots', 'admin', 'tadminoptions');
    $self->createitem($opt, 'local', 'admin', 'tadminlocalmerger');
    $self->createitem($opt, 'notfound404', 'admin', 'tadminoptions');
    $self->createitem($opt, 'redir', 'admin', 'tadminredirector');
  }
  
  $service = $self->createitem(0, 'service', 'admin', 'tadminservice');
  {
    $self->createitem($service, 'backup', 'admin', 'tadminservice');
    $self->createitem($service, 'upload', 'admin', 'tadminservice');
    $self->createitem($service, 'engine', 'admin', 'tadminservice');
    $self->createitem($service, 'run', 'admin', 'tadminservice');
  }
  
  /*
  $board = $self->additem(array(
  'parent' => 0,
  'url' => '/admin/',
  'title' => tlocal::get('names', 'board'),
  'name' => 'board',
  'class' => 'tadminboard',
  'group' => 'author'
  ));
  */
  $self->unlock();
  
  $redir = tredirector::i();
  $redir->add('/admin/', '/admin/posts/editor/');
}

function  tadminmenusUninstall($self) {
  //rmdir(. 'menus');
}

?>