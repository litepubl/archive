/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

$(document).ready(function() {
if ($("#filebrowser").length == 0) return;
var _dialog = false;

function fileprops_dialog(props) {
if (!_dialog) {
_dialog= $('<div title="%%lang_titledialog%%">\
<p><input type="text" name="fileprop_title" id="fileprop_title" value="" size="22" />\
<label for="fileprop_title"><strong>%%lang_title%%</strong></label></p>\
<p><input type="text" name="fileprop_description" id="fileprop_description" value="" size="22" />\
<label for="fileprop_description"><strong>%%lang_description%%</strong></label></p>\
<p><input type="text" name="fileprop_keywords" id="fileprop_keywords" value="" size="22" />\
<label for="fileprop_keywords"><strong>%%lang_keywords%%</strong></label></p>\
</div>').appendTo("#filetabs");

_dialog.dialog( {
autoOpen: false,
modal: true,
buttons: [
{
        text: "%%lang_update%%",
        click: function() {
 $(this).dialog("close"); 
props.title = $.trim($("#fileprop_title", _dialog).val());
props.description = $.trim($("#fileprop_description", _dialog).val());
props.keywords = $.trim($("#fileprop_keywords", _dialog).val());
send_fileprops(props);
}
    },
{
        text: "%%lang_cancel%%",
        click: function() { $(this).dialog("close"); }
    }
]
} );
}

$("#fileprop_title", _dialog).val(props.title);
$("#fileprop_description", _dialog).val(props.description);
$("#fileprop_keywords", _dialog).val(props.keywords);
_dialog.dialog( "open" );
$("#fileprop_title", _dialog).focus();
}

function send_fileprops(props) {
  $.get(ltoptions.url + '/admin/fileprops.htm', 
{
action: "set", 
id: props.id,
title: props.title,
description: props.description,
keywords: props.keywords
}, function (result) {
//alert(result + " file props updated");
});
}

$("#filebrowser a[rel^='prettyPhoto']").live('click', function() {
var props = $(this).data("fileprops");
if (props) {
fileprops_dialog(props);
} else {
var link = $(this);
  $.get(ltoptions.url + '/admin/fileprops.htm', 
{action: "get", filename: $(this).attr("href")}, function (resp) {
var fileprops = $.parseJSON(resp);
link.data("fileprops", fileprops);
fileprops_dialog(fileprops);
});
}
return false;
});
});
