/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function set_progress(value) {
switch (value) {
case -1:
$('#progressbar').hide();
break;

case 0:
$('#progressbar').show();
$('#uploadedpercent').text(value + ' %');
break;

default:
$('#uploadedpercent').text(value + ' %');
}
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
set_progress(0);
  //var url = ltoptions.uploadurl == undefined ? ltoptions.url: ltoptions.uploadurl;
  //ltoptions.swfu .setUploadURL(url + "/admin/ajaxposteditor.htm?get=upload&id=" + ltoptions.idpost + '&random=' + Math.random());
  this.startUpload();
}

function uploadStart(file) {
  return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
set_progress(Math.ceil((bytesLoaded / bytesTotal) * 100));
}

function uploadError(file, errorCode, message) {
  alert('uploadError');
}

function uploadComplete(file) {
set_progress(-1);
}

//central event
function uploadSuccess(file, serverData) {
//alert(serverData);
set_color('headerurl', serverData);
}

function createswfu () {
  var settings = {
    flash_url : ltoptions.files + "/js/swfupload/swfupload.swf",
    upload_url: ltoptions.url + "/theme-generator.htm",
    // prevent_swf_caching: false,
  post_params: {"formtype": "image"},
    file_size_limit : "4 MB",
    file_types : "*.jpg;*.png;*.gif",
    file_types_description : "Images",
    file_upload_limit : 1,
    file_queue_limit : 1,
    //debug: true,
    
    // Button settings
    button_image_url: ltoptions.files + "/js/swfupload/images/XPButtonNoText_160x22.png",
    button_text: '<span class="upload_button">' + 'lang.themegen.upload_button_text' + '</span>',
    button_placeholder_id : "uploadbutton",
    button_width: 160,
    button_height: 22,
      button_text_style : '.upload_button { font-family: Helvetica, Arial, sans-serif; font-size: 14pt; text-align: center; }',
    button_text_top_padding: 1,
button_text_left_padding: 5,

    file_dialog_complete_handler : fileDialogComplete,
    upload_start_handler : uploadStart,
    upload_progress_handler : uploadProgress,
    upload_error_handler : uploadError,
    upload_success_handler : uploadSuccess,
    upload_complete_handler : uploadComplete
  };
  
   try {
    return new SWFUpload(settings);
} catch(e) { alert('Error create swfupload ' + e.message); }
}

function set_color(name, value) {
		$("#text-color-" + name).val(value);
for (var i = 0, l =ltoptions.colors.length ; i < l; i++) {
var item = ltoptions.colors[i];
if (name == item['name']) {
var propvalue = item['value'].replace('%%' + name + '%%', value);
var a = propvalue.split('%%');
if (a.length >= 2) {
var name2= a[1];
propvalue = propvalue.replace('%%' + name2 + '%%', $('#text-color-' + name2).val());
}
//alert(propvalue);
$(item['sel']).css(item['propname'], propvalue);
}
}
}

$(document).ready(function() {
$("#showmenucolors").click(function() {
$("#menucolors").slideToggle();
return false;
});

ltoptions.swfu = createswfu();
$("input[id^='colorbutton']").ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$(el).ColorPickerHide();
try {
set_color($(el).attr("rel"), hex);
} catch(e) { alert(e.message); }
	},

//onShow: function() {$(".colorpicker_submit").append('<a href="">submit</a>');},

	onBeforeShow: function () {
var edit = "#text-color-" + $(this).attr("rel");
$(this).ColorPickerSetColor($(edit).val());
	}
});

});