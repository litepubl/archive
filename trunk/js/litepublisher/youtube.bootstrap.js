/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function ($, document, window) {
  $.YoutubeBootstrap = Class.extend({
    id: 0,
    vid: '',
    width: 500,
    height: 344,
    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-hidden="true">' +
    '<div class="modal-dialog"><div class="modal-content">' +
    '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>' +
    '<div class="modal-body">' +
    '<iframe src=http://www.youtube.com/embed/%%vid%%?autoplay=1&html5=1" width="%%width%%" height="%%height%%" border="0"></iframe>' +
    '</div>' +
    '</div></div></div>',
    
    init: function() {
      var self = this;
      $("a[href^='http://youtu.be/'], a[href^='http://www.youtube.com/watch?v=']").on("click.youtube", function() {
        var url = $(this).attr("href");;
        var vid = get_get('v', url);
        if (!vid) {
          vid = url.split('youtu.be/').pop();
          var i = vid.indexOf('?');
          if(i> 0) vid = vid.substr(0, i);
          i = vid.indexOf('&');
          if(i> 0) vid = vid.substr(0, i);
        }
        
        self.vid = vid;
        var html = $.simpletml(self.tml, {
          vid: vid,
          id: self.id ? self.id++ : (self.id = 	$.now()),
          width: self.width,
          height: self.height
        });
        
        $(html).appendTo("body").modal().on("hide.bs.modal", function() {
          var dialog = $(this);
          var iframe = dialog.find('iframe:first');
          iframe.attr("src", "");
          window.setTimeout(function() {
            dialog.modal("destroy");
            dialog.remove();
          }, 100);
        });
        
        return false
      });
    }
    
  });
  
  ready2(function() {
    if ("modal" in $.fn) $.youtubeBootstrap = new $.YoutubeBootstrap();
  });
}(jQuery, document, window));