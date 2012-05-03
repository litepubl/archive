(function( $ ){
  $.moderate = function(options) {
try {
		this.options = $.extend({
comments: "#commentlist",
hold: "#holdcommentlist",
comment: "#comment-",
content: "commentcontent-",
createhold: '<ol class="commentlist" id="holdcommentlist" start="1"></ol>',
buttons:".moderationbuttons",
button: '<button type="button">%%title%%</button>',
editor: "#comment"
}, ltoptions.theme.comments, options);

this.click = function() {
      var self = $(this);
      var action = self.data("moder");
      var id = self.data("idcomment");
      $.moderate.setstatus(id, action);
      return false;
  };

this.move= function(id, status) {
var options = $.moderate.options;
    var item =$(options.comment  + id);
//create hold list if it isn't exists
if (status == "hold") {
     var parent = $(options.hold);
if (parent.length == 0) {
parent = $(options.createhold);
$(options.comments).after(parent);
}
} else {
    var parent =  $(options.comments);
}

    parent.append(item);
  };

this.error= function(mesg) {
//alert(mesg);
$.messagebox(mesg);
};

this.confirm_delete = function(callback) {
$.confirmbox(lang["default"].confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
if (index ==0) callback();
});
};
  
  this.setstatus= function (id, status) {
var options = $.moderate.options;
    var idcomment = options.comment + id;
    switch (status) {
      case "delete":
$.moderate.confirm_delete(function() {
var mesg = lang.comments.notdeleted;
    $.litejson({method: "comment_delete", id: id}, mesg,
      function(r){
if (r == false) return $.moderate.error(mesg);
        $(idcomment).remove();
      });
});
      break;
      
      case "hold":
      case "approved":
var mesg = lang.comments.notmoderated;
    $.litejson({method: "comment_setstatus", id: id, status: status}, mesg,
      function(r) {
try {
if (r == false) return $.moderate.error(mesg);
        $.moderate.move(id, status);
} catch(e) { alert('error ' + e.message); }
      });
      break;
      
      case "edit":
    $.litejson({method: "comment_get", id: id}, lang.comments.errorrecieved,
      function(resp){
        try {
          var area = $($.moderate.options.editor);
          area.data("idcomment", id);
          area.data("savedtext", area.val());
          area.val(resp.rawcontent);
          $("#commentform").one("submit", function() {
          var area = $($.moderate.options.editor);
var content = $.trim(area.val());
if (content == "") return $.moderate.error(lang.comment.emptycontent);
          $.litejson({method: "comment_edit", id:area.data("idcomment"), content: content},
            lang.comments.notedited, function(r){
              area.val(area.data("savedtext"));
var cc = $.moderate.options.content + result.id;
              $(cc).html(result.content);
location.hash = cc.substring(1);
            });
            return false;
          });
        } catch (e) {
          alert(e.message);
        }
      });
      break;
      
      default:
      alert("Unknown status " + status);
    }
  };

this.create_buttons = function() {
var options = this.options;
alert(options.button);
var approve = options.button.replace('%%title%%', lang.comments.approve);
var hold = options.button.replace('%%title%%', lang.comments.hold);
var del = options.button.replace('%%title%%', lang.comments['delete']);
var edit = options.button.replace('%%title%%', lang.comments.edit);

var moderclick = this.click;
var iduser = get_cookie("litepubl_user_id");

    $(options.buttons, options.comments +", " + options.hold).each(function() {
var self = $(this);
var id = self.data("idcomment");
if (options.ismoder) {
$(approve).appendTo(self).data("idcomment", id).data("moder", "approve").click(moderclick);
$(hold).appendTo(self).data("idcomment", id).data("moder", "hold").click(moderclick);
$(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
$(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
} else {
var idauthor = self.data("idauthor");
if (idauthor == iduser) {
if (options.canedit) $(edit).appendTo(self).data("idcomment", id).data("moder", "edit").click(moderclick);
if (options.candelete) $(del).appendTo(self).data("idcomment", id).data("moder", "delete").click(moderclick);
}
}
});
};

this.create_buttons();
} catch(e) { alert('error ' + e.message); }
};
  
  $(document).ready(function() {
$.load_css(ltoptions.files + "/js/litepublisher/prettyphoto.dialog.css");
$.load_script(ltoptions.files + "/js/plugins/tojson.min.js", function() {
//alert($.toJSON (lang));
    $.moderate();
});
  });

})( jQuery );