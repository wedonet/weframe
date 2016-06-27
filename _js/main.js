var ttt;
var webdir = '/';

$(document).ready(function () {

    $("a.j_open").j_open();	//模拟弹出
    //$("a.j_do").j_do();	//模拟弹出

    //$("span.j_hover").j_hover();		//js下拉菜单
    //j_load();		//Ajax载入网页
    //JT_init();		//

    //$("#pagecontent").autoimgsize(680);
    //$("form.jform").formatinput();	

    $("table.j_list").list();





    //focus后移除fault状态
    //$("input").live("focus", function(){
    //	$(this).removeClass("fault");
    //})

    //为class=j_confirm的链接加删除确认
//	$("a.j_confirm").bind("click",function(){
//		if (confirm($(this).attr("title")))
//		{
//			return true;
//		}
//		else{
//			return false;
//		}
//	});

    //$('a.j_confirmdel').j_confirmdel();

    $('a.j_del').j_del();

	 /*等网页加载完再使submit生效*/
	 $('input.slowsubmit').removeAttr('disabled');

//	$(".j_temp").bind("click", function(){
//		alert("制做中");
//		return false;
//	})


});



$.fn.j_open = function () {
    //模拟弹出窗口
    $(this).on("click", function () {

        //有rel的弹出框需要确认
        var title = $(this).attr("title");

        if ((title != "") && (title != undefined))
        {
            if (!confirm(title))
            {
                return false;
            }
        }

        var href = $(this).attr("href");
        if ("" == href)
        {
            alert("地址错误");
            return(false);
        }

        $("body").append(jumpdiv());

        //$("#bg").height($(document).height()).bgiframe();
        $("#bg").height($(document).height()).width($(document).width());


        $("#edit").dialog("");

        $("#editbody").load(href, {timestamp: new Date().getTime()}, function (data) {

            $("#edit").dialog("").show();


            //执行载入网页的js
            //var s = "<script type='text/javascript'>"+$("#edit .jsupdata").val()+"</script>";
            //$("#edit").append(s);			

            //$("#j_reopen a.j_reopen").reopen();
            //if ($(data).find("#autoclose").length>0)
            //{
            //	setTimeout("autoclose()", $("#autoclose").text());
            //}
            //autoclose();
            //JT_init(); //tip

        });
        closeedit();
        return false;
    });
}

/*ajax操作*/
$.fn.j_do = function (callback) {
    //模拟弹出窗口
    $(this).on("click", function () {

        //有rel的弹出框需要确认
        var title = $(this).attr("title");

        if ((title != "") && (title != undefined))
        {
            if (!confirm(title))
            {
                return false;
            }
        }

        var href = $(this).attr("href");
        if ("" == href)
        {
            alert("地址错误");
            return(false);
        }



        //$("body").append(jumpdiv());

        loading();

        //$("#bg").height($(document).height()).width($(document).width());


        //$("#edit").dialog("");


        $.ajax({
            cache: false,
            type: 'POST',
            url: href,
            dataType: 'json', //返回json格式数据
            success: function (json) {
                //removeloading();
                //$('#bg').removeClass('centerloading');
                //functionname(json);
                //return json;
					 $('#centertable').remove();
					 if(callback){
						callback(json);
					 }
            },
            error: function (xhr, type, error) {
                alert('Ajax error:' + xhr.responseText);

                //alert('Ajax error!');
            }
        })




        //$("#editbody").load(href, {timestamp: new Date().getTime()}, function (data) {

        //    $("#edit").dialog("").show();

        //});
        closeedit();
        return false;
    });
}

/*删除莲接，确认删除时，ajax访问删除地址，跟据返回值刷新页面
 * 返回json格式
 * 删除成功将ajax删除上级的 .j_parent容器
 */
$.fn.j_confirmdel = function (options) {
    var defaults = {
        reload: false
    }

    var config = $.extend(defaults, options);

    $(this).bind('click', function () {
        var obj = $(this);
        if (!confirm(obj.attr('title'))) {
            return false;
        } else {
            loading();

            var url = obj.attr('href');

            $.ajax({
                cache: false,
                type: 'POST',
                url: url,
                dataType: 'json', //返回json格式数据
                success: function (json) {

                    /*保存成功*/
                    if ('y' == json.success)
                    {
                        /*ajax删除*/
                        obj.closest('.j_parent').remove();
                        removeloading();
                        if (config.reload) {
                            window.location.reload();
                        }

                    } else { //保存失败，显示失败信息
                        errdialog(json);
                    }

                },
                error: function (xhr, type, error) {
                    alert('Ajax error:' + xhr.responseText);
                }
            })
        }

        return false;
    })
}



/*删除，确认删除时，ajax访问删除地址，转为转转转，跟据返回值弹出错误信息或ajax删除
 * 返回json格式
 * 删除成功将ajax删除上级的 .j_parent容器
 */
$.fn.j_del = function () {

    $(this).bind('click', function () {
        var obj = $(this);

        /*if设置了提示信息，then进行确认*/
        if (typeof (obj.attr("title")) != "undefined") {
            if (!confirm(obj.attr('title'))) {
                return false;
            }
        }

        /*克隆一个链接的复本*/
        var temp_a = obj.clone();

        /*变为loading*/
        obj.hide().after('<span id="myloading">loading</span>');

        var url = obj.attr('href');

        $.ajax({
            cache: false,
            type: 'POST',
            url: url,
            dataType: 'json', //返回json格式数据
            success: function (json) {

                /*保存成功*/
                if ('y' == json.success)
                {
                    /*ajax删除*/

                    obj.closest('.j_parent').remove();

                    obj.show();

                    $('#myloading').remove();
                } else { //保存失败，显示失败信息
                    errdialog(json);

                    obj.show();

                    $('#myloading').remove();
                }

            },
            error: function (xhr, type, error) {
                alert('Ajax error:' + xhr.responseText);
            }
        })

        return false;
    })


}

/*确认修改，ajax访问修改地址，跟据返回值刷新页面
 * 返回json格式
 * 删除成功将ajax删除上级的 .j_parent容器
 */
$.fn.j_confirmedit = function (functionname) {


    $(this).bind('click', function () {
        var obj = $(this);
        if (!confirm(obj.attr('title'))) {
            return false;
        } else {
            loading();

            var url = obj.attr('href');

            $.ajax({
                cache: false,
                type: 'POST',
                url: url,
                dataType: 'json', //返回json格式数据
                success: function (json) {

                    /*处理成功*/
                    if ('y' == json.success)
                    {
                        functionname(json);
                        removeloading();
                    } else { //保存失败，显示失败信息
                        errdialog(json);
                    }

                },
                error: function (xhr, type, error) {
                    alert('Ajax error:' + xhr.responseText);
                }
            })
        }

        return false;
    })
}

$.fn.reopen = function () {
    $(this).bind("click", function () {
        $("#edit").css("z-index", "1");
        $("body").append(rejumpdiv());
        $("#reeditbody").html(loading());
        $("#reedit").dialog("").show().css("z-index", "502");		//添加正在执行标志
        $("#reeditbody").load($(this).attr("href") + "&timestamp=" + new Date().getTime(), function (data) {
            $("#reedit").dialog("");
            if ($(data).find("#autoclose").length > 0)
            {
                setTimeout("autoclose()", $("#autoclose").text());
            }

        });

        $("#reclose").click(function () {
            $("#edit").css("z-index", "501");
            $("#reedit").remove();
        });
        return false;
    })
}



function j_load() {
    $("a.j_load").each(function () {
        var obj = $(this);
        var url = obj.attr("href");
        var getobj = $(this).attr("rel"); //提取子页id=obj的容器

        $.get(url, {timestamp: new Date().getTime()}, function (data) {
            obj.replaceWith(data);
        })
    });
}

function jumpdiv() {
    var s;
    s = "<div id='bg'></div>";
    s += "<div id='edit' style='display:none'>";
    s += "	<div class='p'>";
    s += "		<a href='javascript:void(0)' id='close'><img src='" + webdir + "_images/close2.gif' alt='close2' title='关闭窗口' /></a>";
    s += "		<div id='editbody'><div class='loading'><img src='" + webdir + "_images/loading.gif' alt='loading' /></div></div>";
    s += "	</div>";
    s += "</div>";
    return s;
}

function rejumpdiv() {
    var s;
    s = "<div id='reedit'>";
    s += "<div class='p'>";
    s += "<a href='javascript:void(0)' id='reclose'><img src='" + webdir + "_images/close2.gif' alt='close2' title='关闭窗口' /></a>";
    s += "<div id='reeditbody'></div>";
    s += "<div id='closeall'><a href='javascript:void(0)' onclick='closewin()'>关闭全部窗口</a></div>";
    s += "</div></div>";
    return s;
}

/*添加一个大背景，显示正在loading*/
function loading() {
    var width_ = $(document).width();
    var height_ = $(document).height();

    if ($('#bg').length < 1) {
        var str = '';
        str += '<div id="bg" style="width:' + width_ + 'px;height:' + height_ + 'px;">';
        str += '</div>';
        $('body').append(str);
    }

    var str = '';

    str += '<div align="center" style="width:31px;position:fixed;left:50%;top:50%;margin-left:-15px;" id="centertable"><img src="/_images/ajax-loader.gif" /></div>';

    $('#bg').append(str);
}

function loadingdiv() {

}

/*添加一个大背景，显示正在loading
 * 适用于弹出框上的提交操作
 */
function reloading() {
    var width_ = $(document).width();
    var height_ = $(document).height();

    var s = '';
    s += '<div id="bg2" style="width:' + width_ + 'px;height:' + height_ + 'px;">&nbsp;</div>';
    s += '<div id="reloading" style="display:none"><img src="/_images/ajax-loader.gif" /></div>';

    $('body').append(s);

    $('#reloading').dialog("").show();
}

/*移除正在loading*/
function removeloading() {
    $('#bg').remove();
}

/*添加一个大背景，显示遮罩*/
function mask() {
    var width_ = $(document).width();
    var height_ = $(document).height();
    $('body').append('<div id="bg" style="width:' + width_ + 'px;height:' + height_ + 'px;">&nbsp;</div>');
}

//显示隐藏指定容器
function togglediv(obj) {
    $("#" + obj).toggle();
}

$.fn.j_hover = function () {
    $(this).hover(
            function () {
                var obj = $(this);
                var href = obj.find("a").attr("href");
                var pos = obj.find("a").attr("rel");
                if (pos == "right")
                {
                    pos = "hoverbody2";
                } else
                {
                    pos = "hoverbody";
                }
                if (href.indexOf("?") > 0)
                {
                    href += "&timestamp=" + new Date().getTime();
                } else {
                    href += "?timestamp=" + new Date().getTime();
                }
                obj.css("position", "relative").addClass("hover");
                obj.append("<div class='" + pos + "' id='hoverbody'><img src='" + webdir + "_images/loading.gif' alt='正在执行' /></div>");
                obj.find("#hoverbody").load(href, function (data) {
                    j_open();
                    //obj.find("#hoverbody").dropShadow().bgiframe();
                });
            },
            function () {
                $(this).css("position", "static").removeClass("hover");

                //$(this).find("#hoverbody").removeShadow().remove();
            }
    );
}

//=========================================

function odepth() {
    $("option.odepth1").each(function () {
        $(this).html("|--" + $(this).html());
    });
    $("option.odepth2").each(function () {
        $(this).html("|----" + $(this).html());
    });
    $("option.odepth3").each(function () {
        $(this).html("|------" + $(this).html());
    });
}

function showimg(src, alt, width) {
    document.write("<img src=\"" + src + "\" alt=\"" + alt + "\"");
    if (width.length > 0) {
        document.write(" width=\"" + width + "\"");
    }
    document.write(" onload=\"rsimg(this," + width + ")\"");
    document.write(" />");
}
function rsimg(o, w) {
    if (o.width > w) {
        o.resized = true;
        o.width = w;
        o.height = (w / o.width) * o.height;
    }
}

function admin_Size(num, obj) {
    var obj = document.getElementById(obj);
    if (parseInt(obj.rows) + num >= 3) {
        obj.rows = parseInt(obj.rows) + num;
    }
    if (num > 0)
    {
        obj.width = "90%";
    }
}

function bbimg(o) {
    var zoom = parseInt(o.style.zoom, 10) || 100;
    zoom += event.wheelDelta / 12;
    if (zoom > 0)
        o.style.zoom = zoom + '%';
    return false;
}

function getsize(size) {
    if (size == null)
        return "";
    size = (size / 1024);
    size = Math.round(size);
    if (size == 0) {
        size = 1
    }
    return (size + "k");
}



//控制字体大小
//size=12 14 或16
//str=obj
function doZoom(size, obj) {
    $("." + obj).css("font-size", size + "px");
}

function checkradio(obj, value) {

    $("input[name='" + obj + "'][value=" + value + "]").attr("checked", "checked");
}
function checkcheckbox(obj, value) {
    value = value.replace(/\s/g, ""); //去除空格
    value = value.split(",");
    for (var i = 0; i < value.length; i++)
    {
        checkradio(obj, value[i]);
    }
}

$.fn.tab = function () {
    //选项卡
    var obj = $(this);
    obj.find("a").bind("click", function () {
        var showid = $(this).attr("href");//将显示的容器ID
        var hideid = obj.find(".selected").removeClass("selected").attr("href"); //要隐藏的ID		
        obj.find(".selected").removeClass("selected");//移除选中状态		
        $(this).addClass("selected");//增加当前链接选中状态		
        $(hideid).hide();//隐藏以前显示的内容		
        $(showid).fadeIn("fast");//显示匹配内容
        this.blur();
        return false;
    });
}


//myevent 事件 click 或 hover
//myway ""=div , ajax=ajax方式载入
$.fn.tab1 = function (myevent, myway) {
    //选项卡
    var obj = $(this);

    switch (myevent) {
        case "click":
            break;
        case "hover":
            break;
        case "mouseover":
            break;
        default:
            alert("tab1参数错误");
            break;
    }

    obj.find("a").bind(myevent, function () {
        var showid = $(this).attr("rel");//将显示的容器ID
        var hideid = obj.find("li.on a").attr("rel"); //要隐藏的 div ID		


        obj.find(".on").removeClass("on");//移除选中状态		
        $(this).parent("li").addClass("on");//增加当前链接选中状态		
        $("#" + hideid).hide();//隐藏以前显示的内容		
        $("#" + showid).fadeIn("fast");//显示匹配内容
        this.blur();
        return false;
    });
}

$.fn.tabrel = function () {
    //选项卡
    var obj = $(this);
    obj.find("a").bind("click", function () {
        var showid = $(this).attr("rel");//将显示的容器ID
        var hideid = obj.find(".on a").attr("rel"); //要隐藏的ID		

        obj.find(".on").removeClass("on");//移除选中状态		

        $(this).parent().addClass("on");//增加当前链接选中状态		
        $("#" + hideid).hide();//隐藏以前显示的内容		
        $("#" + showid).fadeIn("fast");//显示匹配内容
        this.blur();
        return false;
    });
}


/*Textarea限制输入最大字符数*/
function isNotMax(obj, num) {
    return getobj(obj).value.length != num;
}


//弹出新窗口
function showinnew(obj, width, height) {
    URL = obj.href;
    var left = (screen.width - width) / 2;
    var top = (screen.height - height) / 2;
    window.open(URL, '', 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left + ',scrollbars=1,resizable=0');
    return false;
}


$.fn.getcode = function () {
    $(this).one("focus", function () {
        var url = webdir + '_inc/getcode.php?t=' + Math.random();
        $(this).next("span").load(url);
    });
}

$.fn.getcodes = function () {
    $(this).one("focus", function () {
        var url = webdir + '_inc/getcode.php?t=' + Math.random();
        $("span.getcodes").load(url);
    });
}


//=============================在线编辑器
//模板在线编辑器
//num=1时是管理员编辑器,显示的功能多一些
//num=2时,是普通用户编辑器
//num=3:编辑模板
function wedoneteditor(obj, num, url) {

    if ($("#" + obj).length < 1)
    {
        return false;
    }
    switch (num)
    {
        case 1 :
            CKEDITOR.replace(obj, {
                filebrowserBrowseUrl: '/_user/user_myfiles.php?fromeditor=1',
                filebrowserWindowWidth: '880',
                filebrowserWindowHeight: '585'
            });
            break;
        case 2 :
            //if (ismaster==true)
            //{
            CKEDITOR.replace(obj, {
            });

            //}
            //else{
            //CKEDITOR.replace(obj,{});
            //}
            break;
        case 3 :
            CKEDITOR.replace(obj, {
                enterMode: CKEDITOR.ENTER_BR
            });
            break;
    }
}

function getUrlParam(paramName) {
    var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');

    var match = window.location.search.match(reParam);

    return (match && match.length > 1) ? match[ 1 ] : null;
}


$.fn.AddUrl = function () {
    $(this).bind("click", function () {
        //var funcNum = getUrlParam( 'CKEditorFuncNum' );
        var fileUrl = $(this).attr("href");
        //window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
        window.parent.opener.CKEDITOR.tools.callFunction(3, fileUrl, function () {
            window.parent.close();
        });

        return false;
    })
}

function GetInnerHTML(obj)
{
    var oEditor = FCKeditorAPI.GetInstance(obj);
    return(oEditor.GetXHTML(true));
}

//向在线编辑器插入值
function InsertHTML(str)
{
    var oEditor = FCKeditorAPI.GetInstance("content");
    if (oEditor.EditMode == FCK_EDITMODE_WYSIWYG)
    {
        oEditor.InsertHtml(str);
    }
}
//str 图片obj
//向在线编辑器插入图片
function docoutengimg(obj, str) {
    {
        if (str.lenth == 0) {
            return false;
        }
        if (obj.length == 0) {
            return false;
        }
        //window.parent.CKEDITOR.instances.editor1.insertHtml("a");
        window.parent.CKEDITOR.instances.content.insertHtml("<img src=\"" + $("#" + str).val() + "\" alt=''>");
    }
}


function format_time(str, num) {
    var str = str.split(" ");
    var myday = str[0];
    var mytime = str[1];
    switch (num)
    {
        case 1:
            return myday;
    }
}
//格式化货币
function format_currency(num) {
    if (isNaN(num) || num == "") {
        return ("￥0.00元");
    } else {
        num = parseFloat(num);
        if (num == 0)
        {
            return ("￥0.00元");
        } else {
            return ("￥" + num.toFixed(2) + "元");
        }
    }
}

/*拆分tags*/
/*num=b*/
function gettags(num, str) {
    str += "";
    if (str.length == 0)
    {
        document.write("");
    } else {
        str = str.split(",");
        for (i = 0; i < str.length; i++)
        {
            document.write("<a href=\"?b=" + num + "&amp;myfield=tags&amp;keyword=" + str[i] + "\">" + str[i] + "</a> ");
        }
    }
}
function cutstring(str, start, len)
        /**
         * 截取指定长度的字符串（中英文混合）
         * @author xwl
         * @param String str 要截取的字符串
         * @param Int start 起始位置
         * @param Int length 截取长度
         * @return String 截取以后字符串
         **/
        {

            var ilen = start + len;
            var reStr = "";

            if (str.length <= ilen) {
                return str;
            }

            for (i = 0; i < ilen; i++)
            {
                if (escape(str.substr(i, 1)) > 0xa0)
                {
                    reStr += str.substr(i, 2);
                    i++;
                } else {
                    reStr += str.substr(i, 1);
                }
            }
            return reStr + "...";
        }


function adjustFrameSize(obj)
{
    var frm = document.getElementById(obj);
    var subWeb = document.frames ? document.frames[obj].document : frm.contentDocument;
    if (frm != null && subWeb != null)
    {
        frm.style.height = "0px";//初始化一下,否则会保留大页面高度
        frm.style.height = subWeb.documentElement.scrollHeight + "px";
        frm.style.width = subWeb.documentElement.scrollWidth + "px";
        subWeb.body.style.overflowX = "auto";
        subWeb.body.style.overflowY = "auto";

    }
}



//全选
function checkall(obj) {
    $("input[name='" + obj + "']").each(function () {
        $(this).attr("checked", true);
    });
}

//全不选
function uncheckall(obj) {
    $("#select").attr("checked", false);
    $("input[name='" + obj + "']").each(function () {
        $(this).attr("checked", false);
    });
}

//反选
function contrasel(obj) {
    $("input[name='" + obj + "']").each(function () {
        if ($(this).attr("checked"))
        {
            $(this).attr("checked", false);
        } else
        {
            $(this).attr("checked", true);
        }
    });
}


function showmyurl(obj) {
    var str = getobj(obj).value;
    str = str.replace(/\n/g, "#");
    str = str.replace(/\r/g, "");
    str = str.split("#");
    document.write("<ul>");
    for (var i = 0; i < str.length; i++)
    {
        document.write("<li><a href='down.asp?" + window.location.href.split("?")[1] + "&amp;urlid=" + i + "' target='_blank'>" + str[i].split("|")[0] + "</a></li>");
    }
    document.write("</ul>");
}
/*收藏夹*/
function setBookmark(url, str) {
    if (str == '')
        str = url;
    if (document.all)
        window.external.AddFavorite(url, str);
    else
        alert('同时按下Ctrl和D添加到收藏夹:\n"' + url + '".');
}

/*触发*/
function closeedit() {
    $("#close").click(function () {
        doclose();
        return false;//解决Gif不动的Ie6 Bug
    });

    /*点击空白区域关闭弹出窗口*/
    $('#bg').click(function (e) {
        var _con = $('#edit');   // 设置目标区域
        //if(!_con.is(e.target) && _con.has(e.target).length === 0){ // Mark 1

        doclose();
        return false;//解决Gif不动的Ie6 Bug
        //}
    });
}
/*关闭弹出窗口*/
function doclose() {
    if (ttt != "")
    {
        clearTimeout(ttt);
    }
    ;
    //$("#edit iframe").remove();
    $("#edit").remove();

    $("#bg").remove();
}

function autoclose() {
    $("#reedit").fadeOut("slow");
    $("#edit").remove();
    $("#bg").remove();
}
function autolocate(x) {
    //alert("a");
    window.location.href = x;
}

function closewin() {
    $("#reedit").remove();
    $("#edit").remove();
    $("#bg").remove();
    return false;
}

/*
 2009-5-18 认为没用了,删除
 $.fn.loading = function(){
 $("body").append("")
 };
 */


//把图片插入到相对应input
function inserturl(obj, url) {
    $("#" + obj).val(url);
    $("#close").click();
}

//===========================================
//表单部分
//适用于Ajax页面
function UpdateEditorValue() {
    if (typeof (CKEDITOR) != "undefined")
    {

        var textareas = $('textarea');
        $.each(textareas, function () {
            var idname = $(this).attr('id');
            var editor = CKEDITOR.instances[idname];
            if (typeof (editor) != "undefined")
            {
                $(this).val(editor.getData());
            }
        });

    }
    ;
}

function FckReset() {
    if ((typeof (FCKeditorAPI)) != "undefined") {
        for (i in FCKeditorAPI.__Instances)
        {
            FCKeditorAPI.__Instances[i].SetHTML('');
        }
    }
}




/*批量操作*/
function j_batchpost2(formid, action, msg, batchid) {
    var selected = false;
    $(":input[name='" + batchid + "']").each(function () {
        if ('checked' == $(this).attr('checked'))
        {
            selected = true;
            return false;
        }
    });

    if (true == selected)
    {
        j_post2(formid, action, msg);
    } else {
        alert("请选择记录！");
    }
}

/*
 * ajax提交表单
 * obj : 表单对象
 * 
 */
function j_post(obj, functionname) { //定义j_p函数（对象和回调名称）

    loading();
    UpdateEditorValue();
    var data = obj.serialize(); //表单信息
    var url = obj.attr('action');

	 /*移除上次的当前表单， 给当前表单加上currentform*/
	 $('form.currentform').removeClass('currentform');

	 obj.addClass('currentform');

    //把当前提交的表单，保存在data
    $('body').data('currentform', obj.attr('id'));

    $.ajax({
        cache: false,
        type: 'POST',
        url: url,
        data: data,
        dataType: 'json', //返回json格式数据
        success: function (json) {
            //removeloading();
            $('#bg').removeClass('centerloading');
            functionname(json);
        },
        error: function (xhr, type, error) {
            alert('Ajax error:' + xhr.responseText);

            //alert('Ajax error!');
        }
    })

    return false;
}


/*
 * ajax提交表单
 * obj : 表单对象
 * 
 */
function j_repost(obj, functionname) { //定义j_p函数（对象和回调名称）

    reloading();


    var data = obj.serialize(); //表单信息
    var url = obj.attr('action');





    //把当前提交的表单，保存在data
    //$('body').data('currentform', obj.attr('id'));

    $.ajax({
        cache: false,
        type: 'POST',
        url: url,
        data: data,
        dataType: 'json', //返回json格式数据
        success: function (json) {
            //removeloading();
            $('#bg').removeClass('centerloading');
            $('#divloading').remove();
            $('#reloading').remove();
            functionname(json);
        },
        error: function (xhr, type, error) {
            alert('Ajax error:' + xhr.responseText);

            //alert('Ajax error!');
        }
    })

    return false;
}

/*添加一个loading层，带正在执行图片*/
function addloading() {
    var s = '<div id="divloading" style="z-index:501;position:absolute;width:50px;height:50px;display:none;"><img src="/_images/ajax-loader.gif" /></div>';

    $('body').append(s);

    $('#divloading').dialog("").show();
}

function j_reset() {
    FckReset();
    return true;
}

function j_resource() {
    $("#j_source").bind("click", function () {
        $(this).replaceWith("<iframe frameborder='0' width='100%' height='260' scrolling='no' src='" + $(this).attr("href") + "'></iframe>");
        return false;
    });
}


;
$.fn.dialog = function (pos) {
    var wnd = $(window), doc = $(document),
            pTop = doc.scrollTop(), pLeft = doc.scrollLeft(),
            minTop = pTop;

    if ($.inArray(pos, ['center', 'top', 'right', 'bottom', 'left']) >= 0) {
        pos = [
            pos == 'right' || pos == 'left' ? pos : 'center',
            pos == 'top' || pos == 'bottom' ? pos : 'middle'
        ];
    }
    if (pos.constructor != Array) {
        pos = ['center', 'middle'];
    }
    if (pos[0].constructor == Number) {
        pLeft += pos[0];
    } else {
        switch (pos[0]) {
            case 'left':
                pLeft += 0;
                break;
            case 'right':
                pLeft += wnd.width() - this.outerWidth();
                break;
            default:
            case 'center':
                pLeft += (wnd.width() - this.outerWidth()) / 2;
        }
    }
    if (pos[1].constructor == Number) {
        pTop += pos[1];
    } else {
        switch (pos[1]) {
            case 'top':
                pTop += 0;
                break;
            case 'bottom':
                pTop += wnd.height() - this.outerHeight();
                break;
            default:
            case 'middle':
                pTop += (wnd.height() - this.outerHeight()) / 2;
        }
    }

    // prevent the dialog from being too high (make sure the titlebar
    // is accessible)
    pTop = Math.max(pTop, minTop);
    this.css({top: pTop, left: pLeft});
    return this;
};





function prop(n) {
    return n && n.constructor === Number ? n + 'px' : n;
}









/*tip*/
function JT_init() {
    var title;
    $("a.j_tip")
            .hover(
                    function () {
                        title = this.title;
                        $(this).attr("id", "a_tip");
                        JT_show(this.href, "a_tip", title);
                        this.title = "";

                    },
                    function () {
                        $('#JT').remove();
                        this.title = title;
                        $(this).removeAttr("id");
                    })

            .click(function () {
                return false
            });
}

function JT_show(url, linkId, title) {
    if (title == false)
        title = "&nbsp;";
    var de = document.documentElement;
    var w = self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
    var hasArea = w - getAbsoluteLeft(linkId);
    var clickElementy = getAbsoluteTop(linkId) - 3; //set y position

    var queryString = url.replace(/^[^\?]+\??/, '');
    var params = parseQuery(queryString);
    if (params['width'] === undefined) {
        params['width'] = 250
    }
    ;
    if (params['link'] !== undefined) {
        $('#' + linkId).bind('click', function () {
            window.location = params['link']
        });
        $('#' + linkId).css('cursor', 'pointer');
    }

    if (hasArea > ((params['width'] * 1) + 75)) {
        $("body").append("<div id='JT' style='width:" + params['width'] * 1 + "px'><div id='JT_arrow_left'></div><div id='JT_close_left'>" + title + "</div><div id='JT_copy'><div class='JT_loader'><div></div></div>");//right side
        var arrowOffset = getElementWidth(linkId) + 11;
        var clickElementx = getAbsoluteLeft(linkId) + arrowOffset; //set x position
    } else {
        $("body").append("<div id='JT' style='width:" + params['width'] * 1 + "px'><div id='JT_arrow_right' style='left:" + ((params['width'] * 1) + 1) + "px'></div><div id='JT_close_right'>" + title + "</div><div id='JT_copy'><div class='JT_loader'><div></div></div>");//left side
        var clickElementx = getAbsoluteLeft(linkId) - ((params['width'] * 1) + 15); //set x position
    }

    $('#JT').css({left: clickElementx + "px", top: clickElementy + "px"});
    $('#JT').show();
    $('#JT_copy').load(url);

}

function getElementWidth(objectId) {
    x = document.getElementById(objectId);
    return x.offsetWidth;
}

function getAbsoluteLeft(objectId) {
    // Get an object left position from the upper left viewport corner
    o = document.getElementById(objectId)
    oLeft = o.offsetLeft            // Get left position from the parent object
    while (o.offsetParent != null) {   // Parse the parent hierarchy up to the document element
        oParent = o.offsetParent    // Get parent object reference
        oLeft += oParent.offsetLeft // Add parent left position
        o = oParent
    }
    return oLeft
}

function getAbsoluteTop(objectId) {
    // Get an object top position from the upper left viewport corner
    o = document.getElementById(objectId)
    oTop = o.offsetTop            // Get top position from the parent object
    while (o.offsetParent != null) { // Parse the parent hierarchy up to the document element
        oParent = o.offsetParent  // Get parent object reference
        oTop += oParent.offsetTop // Add parent top position
        o = oParent
    }
    return oTop
}

function parseQuery(query) {
    var Params = new Object();
    if (!query)
        return Params; // return empty object
    var Pairs = query.split(/[;&]/);
    for (var i = 0; i < Pairs.length; i++) {
        var KeyVal = Pairs[i].split('=');
        if (!KeyVal || KeyVal.length != 2)
            continue;
        var key = unescape(KeyVal[0]);
        var val = unescape(KeyVal[1]);
        val = val.replace(/\+/g, ' ');
        Params[key] = val;
    }
    return Params;
}

function blockEvents(evt) {
    if (evt.target) {
        evt.preventDefault();
    } else {
        evt.returnValue = false;
    }
}

/*typeid=0 then 隐藏确定按钮; typeid=1 显示确定按钮*/
;
$.fn.selectclass = function (href, typeid, myid) {
    var obj = $(this);
    var offset = obj.offset();

    var s = "";
    s = "<div id='bg'></div>";
    s += "<div id='edit'>";
    s += "<div class='p'>";
    s += "	<a href='javascript:void(0)' id='close'><img src='" + webdir + "_images/close2.gif' title='关闭窗口' /></a>";
    s += "	<div id='editbody' class='selectclass'><img src='" + webdir + "_images/loading.gif' alt='loading'></div>";
    s += "</div>";
    s += "</div>";

    $(this).addClass("imselect"); //下拉框增加右键头

    $(this).bind("click", function () {
        $("body").append(s);
        $("#bg").height($(document).height()).bgiframe();
        $("#edit").css({
            "top": offset.top + 3,
            "left": offset.left + 3
        }).show().drag(function (ev, dd) {
            $(this).css({
                top: dd.offsetY,
                left: dd.offsetX
            });
        }, {handle: ".th"});
        $.getJSON(href, {timestamp: new Date().getTime(), typeid: typeid}, function (s) {
            $("#editbody").html(s.html).loadclass(myid, obj);
        })
        closeedit();

        return false;
    });

    $.fn.loadclass = function (myid, obj) {
        $(this).find("a").bind("click", function () {
            $("#editbody").html("<img src='" + webdir + "_images/loading.gif' alt='loading'>"); //添加等待状态
            $.getJSON($(this).attr("href"), {timestamp: new Date().getTime(), typeid: typeid}, function (t) {
                $("#editbody").html(t.html).loadclass(myid, obj);
                if (1 == t.isleaf)
                {
                    $("#" + myid).val(t.classid);
                    obj.val(t.thename);
                    $("#edit").remove();
                    $("#bg").remove();
                    //alert("a");
                }
                $("#submitclass").bind("click", function () {
                    $("#" + myid).val($("#sclassid").val());
                    obj.val($("#sclassname").val());
                    $("#edit").remove();
                    $("#bg").remove();
                });

                //if ("" != obj.val())
                //{
                obj.next("img").show(); //显示取消链接
                //}
            });
            return false;
        });
    };

    //取消选定的图片加链接,显示取消链接
    $(this).next("img").bind("click", function () {
        obj.val("");
        $("#" + myid).val("");
        $(this).hide();
    });

}


function selimg(preid, inputid, url) {
    $("#" + preid).attr("src", url);
    $("#" + inputid).val(url);

    //关闭窗口

    //$("#edit iframe").remove();

    $("#bg").remove();
    $("#edit").remove();
}


function GetQueryString(locstring, str) {
    var rs = new RegExp("(^|)" + str + "=([^\&]*)(\&|$)", "gi").exec(locstring), tmp;
    if (tmp = rs)
        return tmp[2];
    return "没有这个参数";
}

$.fn.focusdiv = function (focus_width, focus_height, text_height) {
    //循环部分 <li>{$preimg}|{$readhref}|{$title}</li>
    //var pics="/images/show.jpg|/images/show2.jpg";
    //var links = "/index.asp|/index.asp";
    //var texts = "text1|text2";
    //var interval_time=5 //图片停顿时间，单位为秒，为0则停止自动切换
    //var focus_width=280 //宽度
    //var focus_height=211 //高度
    //var text_height=20 //标题高度
    var swf_height = focus_height + text_height; //相加之和最好是偶数,否则数字会出现模糊失真的问题

    var pics = "";
    var links = "";
    var texts = "";
    var s;

    $(this).find("li").each(function () {
        s = $(this).html().split("|");
        pics += (s[0] + "|");
        links += (s[1] + "|");
        texts += (s[2] + "|");
    })

    pics = pics.substr(0, pics.length - 1);
    links = links.substr(0, links.length - 1);
    texts = texts.substr(0, texts.length - 1);

    $(this).flash({
        'swf': '/flash/pixviewer.swf',
        'width': focus_width,
        'height': swf_height,
        'bgcolor': "#F0F0F0",
        'menu=': 'false',
        'quality': 'high',
        'wmode': 'transparent',
        'WMode': 'Opaque',
        'flashvars': {
            'pics': pics,
            'links': links,
            'texts': texts,
            'borderwidth': focus_width,
            'borderheight': focus_height,
            'textheight': text_height
        }
    });
}

function focusjs() {
    var sWidth = $("#focus").width(); //获取焦点图的宽度（显示面积）
    var len = $("#focus ul li").length; //获取焦点图个数
    var index = 0;
    var picTimer;

    //以下代码添加数字按钮和按钮后的半透明长条
    var btn = "<div class='btnBg'></div><div class='btn'>";
    for (var i = 0; i < len; i++) {
        btn += "<span>" + (i + 1) + "</span>";
    }
    btn += "</div>"
    $("#focus").append(btn);
    $("#focus .btnBg").css("opacity", 0.2);

    //为数字按钮添加鼠标滑入事件，以显示相应的内容
    $("#focus .btn span").mouseenter(function () {
        index = $("#focus .btn span").index(this);
        showPics(index);
    }).eq(0).trigger("mouseenter");

    //本例为左右滚动，即所有li元素都是在同一排向左浮动，所以这里需要计算出外围ul元素的宽度
    $("#focus ul").css("width", sWidth * (len + 1));

    //鼠标滑入某li中的某div里，调整其同辈div元素的透明度，由于li的背景为黑色，所以会有变暗的效果
    $("#focus ul li div").hover(function () {
        $(this).siblings().css("opacity", 0.7);
    }, function () {
        $("#focus ul li div").css("opacity", 1);
    });

    //鼠标滑上焦点图时停止自动播放，滑出时开始自动播放
    $("#focus").hover(function () {
        clearInterval(picTimer);
    }, function () {
        picTimer = setInterval(function () {
            if (index == len) { //如果索引值等于li元素个数，说明最后一张图播放完毕，接下来要显示第一张图，即调用showFirPic()，然后将索引值清零
                showFirPic();
                index = 0;
            } else { //如果索引值不等于li元素个数，按普通状态切换，调用showPics()
                showPics(index);
            }
            index++;
        }, 3000); //此3000代表自动播放的间隔，单位：毫秒
    }).trigger("mouseleave");

    //显示图片函数，根据接收的index值显示相应的内容
    function showPics(index) { //普通切换
        var nowLeft = -index * sWidth; //根据index值计算ul元素的left值
        $("#focus ul").stop(true, false).animate({"left": nowLeft}, 500); //通过animate()调整ul元素滚动到计算出的position
        $("#focus .btn span").removeClass("on").eq(index).addClass("on"); //为当前的按钮切换到选中的效果
    }

    function showFirPic() { //最后一张图自动切换到第一张图时专用
        $("#focus ul").append($("#focus ul li:first").clone());
        var nowLeft = -len * sWidth; //通过li元素个数计算ul元素的left值，也就是最后一个li元素的右边
        $("#focus ul").stop(true, false).animate({"left": nowLeft}, 500, function () {
            //通过callback，在动画结束后把ul元素重新定位到起点，然后删除最后一个复制过去的元素
            $("#focus ul").css("left", "0");
            $("#focus ul li:last").remove();
        });
        $("#focus .btn span").removeClass("on").eq(0).addClass("on"); //为第一个按钮添加选中的效果
    }
}

/*取消链接*/
$.fn.unlink = function () {
    var obj = $(this);
    obj.each(function () {
        var s = $(this).html();
        $(this).replaceWith("<span class='color2'>" + s + "</span>").unbind("click");
    });

}

/*格式化状态*/
$.fn.rore = function () {
    $(this).html(function (index, html) {
        switch (html)
        {
            case "0" :
                return("");
                break;
            case "1" :
                return("<img src=\"" + webdir + "images/check_right.gif\" />");
                break;
            case "2" :
                return("<img src=\"" + webdir + "images/check_error.gif\" />");
                break;
            case "3" :
                return("<img src=\"" + webdir + "images/stop.gif\" />");
                break;
        }

    });
}


$.fn.autoimgsize = function (width) {
    if ($(this).length > 0)
    {
        $(this).find("img").each(function () {

            if ($(this).width() >= width)
            {
                $(this).width(width);
            }
        });

    }
}

/*格式化输入框*/
;
$.fn.formatinput = function () {
    var obj = $(this);
    var onid = "";
    obj.find("input").each(function () {
        if ($(this).attr("type") == "text")
        {
            $(this).addClass("itext").inputon();
        }
    });
    obj.find("textarea").each(function () {
        $(this).addClass("itextarea").inputon();
    });
    obj.find("select").addClass("iselect");
}
;
$.fn.inputon = function () {
    $(this).focus(function () {
        $(this).addClass("on")
    });
    $(this).blur(function () {
        $(this).removeClass("on")
    });
}

/*操作cookie*/
function setCookie(c_name,value,expiredays){
	var exdate=new Date()
	exdate.setDate(exdate.getDate()+expiredays)
	document.cookie=c_name+ "=" +escape(value)+
	((expiredays==null) ? "" : ";path=/;expires="+exdate.toGMTString())
}

function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null){		
        return unescape(arr[2]);
	 }
    return '';
}

function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}

//选择图片
function filefrom(ftype) {
    //var iframe = "<iframe src='"+url+"' frameborder='0' scrolling='no' width='100%' height='450'></iframe>";
    //$("#upcontent").html(iframe);

    $("#frameclass").attr("src", webdir + "_user/user_myfiles.php?act=showclass&ftype=" + ftype);
    $("#main").attr("src", webdir + "_user/user_myfiles.php?act=list&ftype=" + ftype);

    $("#tabup .on").removeClass("on");
    $("#ftype" + ftype).addClass("on");
    return false;
}

/*选择操作*/
;
$.fn.seldo = function () {
    $(this).hover(
            function () {
                $(this).find("dd").show()
                        .end().find("dt").addClass("on");
            },
            function () {
                $(this).find("dd").hide()
                        .end().find("dt").removeClass("on");
            }
    );
}


//bgchecked, 如果和默认值不一样, 加一个背景
$.fn.bgchecked = function () {
    var defaultvalue = $(this).find("option:first").html();
    if (defaultvalue != $(this).val()) //值和默认的不一样, 表未已经做了选择
    {
        $(this).addClass("bgselected");
    } else {
        $(this).removeClass("bgselected");
    }
}

//
$.fn.j_getmore = function () {
    $(this).each(function () {
        var obj = $(this);
        obj.toggle(function () {
            var href = $(this).attr("href") + "&timestamp=" + new Date().getTime();
            $.get(href, function (data) {
                obj.closest("tr").after(data);
            })
            obj.css("background-position", "3px -47px");
        },
                function () {
                    obj.closest("tr").siblings("tr").remove();
                    obj.css("background-position", "3px 0.5px");
                });
    })


}

$.fn.extdiv = function (href) {
    $("body").append(jumpdiv());
    $("#bg").height($(document).height()).bgiframe();
    $("#edit").dialog("").show().drag(function (ev, dd) {
        $(this).css({
            top: dd.offsetY,
            left: dd.offsetX
        });
    }, {handle: ".th"});

    $("#editbody").load(href, {timestamp: new Date().getTime()}, function (data) {
        $("#edit").dialog("");
        $("#edit a.j_extdivlive").j_extdivlive();
    });
    closeedit();
    return false;
}

$.fn.j_extdivlive = function () {
    $(this).bind("click", function () {
        var href = $(this).attr("href");
        $("#editbody").load(href, {timestamp: new Date().getTime()}, function (data) {
            $("#edit").dialog("");
            $("#edit a.j_extdivlive").j_extdivlive();

        });
        return false;
    })
}


function getidlist(obj) {
    var idlist = "";

    $(obj).each(function () {
        idlist += $(this).val() + ",";
    });

    if (idlist.length > 0)
    {
        idlist = idlist.substr(0, idlist.length - 1);
    }
    return idlist;
}

function getapplyinfo()
{
    var idlist = "";

    $(":input[name=id]").each(function () {
        idlist += $(this).val() + ",";
    });

    if (idlist.length > 0)
    {
        idlist = idlist.substr(0, idlist.length - 1);
        var href = "/ajax/getapplyinfo.asp?idlist=" + idlist + "&timestamp=" + new Date().getTime();
        //alert(href);
        $.getScript(href);
    }
}



function test1() {

}

//focus 后清除内容
function cleartext(obj, s) {
    if (s == obj.value) {
        obj.value = "";
    }

}





/**
 * 图片预加载等比例缩放
 */
$.fn.LoadImage = function (width, height, nopicsrc) {

    //没有图片时显示这个
    if (nopicsrc == null)
    {
        nopicsrc = webdir + "_images/nopic.jpg";
    }

    $(this).each(function () {

        var obj = $(this);

        if ($(this).parent()[0].tagName.toLowerCase() == "a")
        {
            obj.parent().wrap("<table width='100%' height='100%' style='overflow:hidden'><tr valign='middle'><td style='text-align:center;padding:0;vertical-align:middle'></td></tr></table>");
        } else {
            obj.wrap("<table width='100%' height='100%' style='overflow:hidden'><tr valign='middle'><td style='text-align:center;padding:0;vertical-align:middle'></td></tr></table>");
        }
        var src = obj.attr("src");

        if (src == "")
        {
            src = nopicsrc;
        }



        obj.attr("src", webdir + "_images/loading.gif");

        var img = new Image();
        img.src = src;

        if (img.complete)
        {
            doimg(obj, img);
            obj.attr("src", src);
        } else {
            $(img).load(function () {
                doimg(obj, img);
                obj.attr("src", src);
            });
        }
    });

    function doimg(obj, img) {
        if (img.width > 0 && img.height > 0)
        {
            //宽,高比大于标准比例, 也就是说图片宽了
            if (img.width / img.height >= width / height)
            {
                if (img.width > width)
                {
                    obj.attr("width", width);
                }
            } else {
                if (img.height > height)
                {
                    obj.attr("height", height);
                }
            }
        }
    }

};

//


//Ajax Login Ajax登录
function AjaxPage(myhref) {
    $("body").append(jumpdiv());
    $("#bg").height($(document).height()).bgiframe();
    $("#edit").dialog("").show().drag(function (ev, dd) {
        $(this).css({
            top: dd.offsetY,
            left: dd.offsetX
        });
    }, {handle: ".th"});

    $("#editbody").load(myhref, {timestamp: new Date().getTime()}, function (data) {
        $("#j_codestr").getcode();
        $("#edit").dialog("");
    });



    closeedit();
    return false;
}



//关键词样式
$.fn.hotkey = function () {
    $(this).find("a").each(function () {
        //五选一
        var x = (parseInt(Math.random() * 6) + 1);
        $(this).addClass("hotkey" + x);
    })


}

$.fn.list = function () {
    var obj = $(this);
    obj.find("tr").hover(
            function () {
                $(this).addClass("hover");
            },
            function () { //如果鼠标移到class为tableborder1的表格的tr上时，执行函数
                $(this).removeClass("hover");	 //移除该行的class,给这行添加class值为over，并且当鼠标移出该行时执行函数
            });
    obj.find("tr:even").addClass("alt");
    //列表刷新后,清除所有复选框
    obj.find("input[name=id]").attr("checked", false);
}

/*鼠标focus后移除错误提示*/
$.fn.holdfault = function () {
    $(this).find("input").bind("focus", function () {
        $(this).removeClass("fault");
    });
}

/*tab hover*/
//$.fn.tabhover = function(){
//   var delayTime = [];
//	var obj = $(this);
//
//   obj.each(function(index) {
//        $(this).hover(function() {
//            var _self = this;
//            delayTime[index] = setTimeout(function() {
//					 obj.removeClass("on");	
//                $(_self).addClass("on");
//            },
//            400)
//        },
//        function() {
//            clearTimeout(delayTime[index]);
//            $(this).removeClass("on");
//        })
//    });
//				
//				
//
//
//}


function tabhover() {
    var delaytime;

    $("#itab1 a").hover(function () {
        var _self = this;

        delaytime = setTimeout(function () {
            //文字color恢复
            $("#itab1 a").removeClass("on");
            $(".tabdiv").hide();

            $(_self).addClass("on");

            if ($(_self).hasClass("a1"))
            {
                $("#itab1").removeClass("sta2");
                $(".iorder").show();
            } else {
                $("#itab1").addClass("sta2");
                $(".ilogin").show();
            }
        },
                400)
    },
            function () {
                clearTimeout(delaytime);
            });
}





//收藏
function tomyfav() {

    if (window.sidebar) {

        window.sidebar.addPanel("超级预定", "http://", "");

    } else if (document.all) {

        window.external.AddFavorite("http://超级预定", "超级预定");

    } else if (window.opera && window.print) {

        return true;

    }

}


/*使按钮不可点击*/
function disbutton(obj) {
    //$(obj).attr('disabled', 'disabled');
}



function DateAdd(interval, number, date) {
    switch (interval.toLowerCase()) {
        case "y":
            return new Date(date.setFullYear(date.getFullYear() + number));
        case "m":
            return new Date(date.setMonth(date.getMonth() + number));
        case "d":
            return new Date(date.setDate(date.getDate() + number));
        case "w":
            return new Date(date.setDate(date.getDate() + 7 * number));
        case "h":
            return new Date(date.setHours(date.getHours() + number));
        case "n":
            return new Date(date.setMinutes(date.getMinutes() + number));
        case "s":
            return new Date(date.setSeconds(date.getSeconds() + number));
        case "l":
            return new Date(date.setMilliseconds(date.getMilliseconds() + number));
    }
}


function getstayprice(leavedate, orderid, enddate, maxday) {
    var href = webdir + '_ajax/reliveprice.php?mydate2=' + leavedate;
    href += '&orderid=' + orderid;
    href += '&t=' + new Date().getTime();

    $.get(href, function (data) {
        if (!isNaN(data))
        {
            $("#myprice").text(data);

            if (data > 0) {
                $("#btn_stay").removeAttr('disabled');
            } else {
                $("#btn_stay").attr('disabled', 'disabled');
            }
        } else {
            href = webdir + '_ajax/reliveprice.php?mydate2=' + enddate;
            href += '&orderid=' + orderid;
            href += '&t=' + new Date().getTime();
            if (data.match(/不能超过/) != null) {
                alert(data);
                $.get(href, function (data) {
                    $("#myprice").text(data);
                    $('#formdate').val(enddate);
                    $('#mydaynum').text(maxday);
                    if (data > 0) {
                        $("#btn_stay").removeAttr('disabled');
                    } else {
                        $("#btn_stay").attr('disabled', 'disabled');
                    }
                })
                return false;
            }
            $("#myprice").text('');
            $("#btn_stay").attr('disabled', 'disabled');

            alert(data);
        }



    });
}

//ajax登录后刷新工具栏里的登录信息
function reloaduserinfo() {
    var obj = $('#topajaxuserinfo');

    var href = webdir + '_ajax/topbar.php';

    obj.load(href);
}

/*接收get*/
function GetRequest() {
    var url = location.search; //获取url中？符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
        }
    }
    return theRequest;
}





/*给当前菜单加on*/
function Menuon() {
    var url = this.location.pathname;	//取路径和文件名
    if (url.indexOf('.') < 1) {  //如果“.”的位置小于1（没有），就不执行添加on的效果 
        return(false);
    }

    $("#menuleft a").each(function () {
        var o = $(this);
        if (o.attr('href').indexOf(url) > -1)
        {

            o.addClass('on');
        }
    })
}
function Menuons() {
    var ele = $(".crumb li").eq(3).text();
    $("#menuleft a").each(function () {
        var o = $(this);
        if (o.text() == ele)
        {
            o.addClass('on');
        }
    })
}

/*财务相关信息中，收入的前面加上‘+’号；支出的加‘-’号*/
$.fn.addcode = function (code) {
    $(this).html(function (index, html) {
        if ('' !== html) {
            return code + html;
        }
    })
}

/*首行冻结插件开始*/
// JavaScript Document
//这个是想解解决首页冻结的另外的一个js

$.fn.firstLineFreeze = function () {
    var $this = this;
    var topmain = $this.offset().top;
    var leftmain = $this.offset().left;
    var lineEle = $('.linefrezee');

    lineEle.css({top: topmain, left: leftmain, display: 'none'});
    var tlen = $('.linefrezee').find('th').length;
    getNewLine();
    $(window).scroll(function () {
        var topNow = $(this).scrollTop();
        if (topNow > topmain) {

            lineEle.css({top: 0}).show()
        } else {
            lineEle.css({top: topmain}).hide()
        }
    })
    $(window).resize(function () {
        getNewLine();
    })
    function getNewLine() {
        var leftmain = $this.offset().left;
        lineEle.css({left: leftmain});
        for (var i = 0; i < tlen; i++) {
            lineEle.find('th').eq(i).width($this.find('tr').eq(0).find('th').eq(i).width() + 1);
        }
    }
}
/*首行冻结插件结束*/


/*textarea 字符限制*/
$.fn.textLimitPlugIn = function () {
    var _this = this;
    return _this.each(function () {
        var explorer = window.navigator.userAgent;
        var len = $(this).attr("maxlength");
        $(this).focus(function () {
            $(this).after("<span class='limit_tip' style='color:gray; margin-left:5px;'><span class='limit_tip_remain'></span>/<span class='limit_tip_total'></span></span>");
            $(".limit_tip_total").text(len);
            $(".limit_tip_remain").text($(this).val().length);
        })
        $(this).blur(function () {
            $(".limit_tip").remove();
        })

        $(this).keyup(function () {
            var str = $(this).val();
            var index = str.search(/\S/);
            if (index != -1) {
                str = str.substr(index);
                if (str.length > len) {
                    $(this).val(str);
                }
                $(".limit_tip_remain").text(str.length);
            } else {
                $(".limit_tip_remain").text(0);
            }
        });
    })
}
//这个函数的作用是让指定的地方的多行文本，根据传递进来的参数显示指定的行数多余的部分会用省略号代替
//使用的注意事项是    因为有指定的行数所以不要设置高度  
//本jq代码改编自http://targetkiller.net/mutiple-line-ellipsis
$.fn.mlellipsis = function (row) {
    //设置了title不丢失文本
    var str = $(this).html();
    //var title = $(this).attr("title");
    //if(title==null){$(this).attr("title",str);}
    $(this).css("height", "auto");
    var lineHeight = $(this).css("line-height");
    var height = $(this).height();
    //获得行高，
    if (lineHeight == "undefined")
        ;
    lineHeight = 20;
    if (!Number(lineHeight)) {
        lineHeight = lineHeight.substr(0, lineHeight.length - 2);

        Number(lineHeight);
    } else {
        Number(lineHeight);
    }
    var dheight = Math.floor(row * lineHeight);
    var midd;//中间变量
    if ($(this).height() >= dheight) {
        str = $(this).html();
        //首先做递减，如果超过太多为了减少循环首先做递减。
        while (dheight * 3 < $(this).height()) {
            $(this).html(str.substring(0, str.length / 2));
            str = $(this).html();
        }
        //循环递减字符串长度
        while (dheight < $(this).height()) {
            str = $(this).html();
            midd = str.substr(0, str.length - 9) + "......";
            $(this).html(midd);
        }
    }
}//-------20150209--------wangyu--------------over


//以上是写入cookie和读取cookie的封装方法------------20150212--------wangyu
//一下方法说明positionA_firfox函数的作用只在ff浏览器下实行，其效果是为单一目的实现，兼容的bug是在ff浏览器下a的描点链接不能准确实现。
//原理是：在点击的地方添加click方法在cookie中设置了参数，在效果页面从cookie中取出设定好的参数，并根据参数找出元素，比较以后定位。
function positionA_firfox(name) {
    var position_ff = getCookie(name);
    if (navigator.userAgent.indexOf("Firefox") > 0) {
        if (position_ff != "undefind" && position_ff != " ") {
            if ($("a[name=" + position_ff + "]").position().top < $(document).scrollTop()) {
                $(document).scrollTop($("a[name=" + position_ff + "]").position().top - 30);
            }
        }
    }
}


/*操作后的弹出提示框*/
function opdialog(mess) {
    var s = '';
    var title = '';

    /*默认显示操作成功*/
    if (!mess.hasOwnProperty('title')) {
        title = '操作成功';
    } else {
        title = mess['title'];
    }


    /*弹出框宽度*/
    if (!mess.hasOwnProperty('width')) {
        if ($(window).width() < 300) {
            _width = $(window).width() - 20;
        } else {
            _width = '300';
        }
    } else {
        _width = mess['width'];
    }

    s = '<div class="opdialog" id="edit" style="width:' + _width + 'px;position:fixed;left:50%;top:50%;margin-left:-' + _width / 2 + 'px;">';
    s += '	<div class="th">' + title + '</div>';
    s += '	<div class="dialogcontent">';
    s += '		<ul class="dialog_list">';
    s += mess['content'];
    s += '		</ul>';
    s += '	</div>';
    s += '</div>';



    $('body').append(s);

    _height = $('#edit').outerHeight() / 2;
    $('#edit').css('margin-top', -_height + 'px').show();

    closeedit();
}

/*弹出对话框*/
function dialog(mess) {
    var s = ''; //对话框容器
    var title = ''; //对话框标题

    /*默认显示操作成功*/
    if (mess.hasOwnProperty('title')) {
        title = mess['title'];
    } else {
        title = '';
    }

    /*弹出框宽度*/
    if (!mess.hasOwnProperty('width')) {
        if ($(window).width() < 300) {
            _width = $(window).width() - 20;
        } else {
            _width = '300';
        }
    } else {
        _width = mess['width'];
    }

    s = '<div class="opdialog" id="edit" style="width:' + _width + 'px;position:fixed;left:50%;top:50%;margin-left:-' + _width / 2 + 'px;">';

    if ('' !== title) {
        s += '	<div class="th">' + title + '</div>';
    }
    s += '	<div class="dialogcontent">';
    s += mess['content'];
    s += '	</div>';
    s += '</div>';

    /*if 没有遮罩层 then添加一个*/
    if (!$('#bg').length > 0) {
        mask();
    }

    $('body').append(s);

    _height = $('#edit').outerHeight() / 2;
    $('#edit').css('margin-top', -_height + 'px').show();

    closeedit();
}

/*弹出的对话框，错误信息提示
 * json : 错误信息列表
 */
function errdialog(json) {
    var mess = new Array();
    var s = '';

    mess['title'] = '提示';


    /*把错误信息，组合成一个字符串*/
    $.each(json['errmsg'], function (i, item) {
        s += '<li>' + item + '</li>';
    });

    mess['content'] = '<ul class="dialog_list">' + s + '</ul>';

    /*把错误input用颜色提示*/
    if (json['errinput']) {
        var a = json['errinput'].split(',');

        for (var i = 0; i < a.length; i++)
        {
						$('form.currentform input[name="' + a[i] + '"][type="text"]:first').addClass('false').one('focus', function () {
                $(this).removeClass('false');
            });
        }
    }



    dialog(mess);
}

/*弹出对话框，显示错误信息*/
function showerrdialog(json) {
    var mess = new Array();
    var s = '';

    mess['title'] = '提示';


    /*把错误信息，组合成一个字符串*/
    $.each(json['errmsg'], function (i, item) {
        s += '<li>' + item + '</li>';
    });

    mess['content'] = '<ul class="dialog_list">' + s + '</ul>';

    /*把错误input用颜色提示*/
    if (json['errinput']) {
        var a = json['errinput'].split(',');

        for (var i = 0; i < a.length; i++)
        {
            $('input[name="' + a[i] + '"]:first').addClass('false').one('focus', function () {
                $(this).removeClass('false');
            });
        }
    }



    showdialog({
        title: mess.title,
        content: mess.content
    });
}

/*弹出对话框*/
function showdialog(options) {
    var defaults = {
        title: '', //对话框标题
        content: '',
        layernum: 1, //第几层
        closeedit: false, //点关闭时是否关闭编辑层
        width: 300
    }

    var config = $.extend(defaults, options);

    var s = ''; //对话框容器




    /*弹出框宽度*/
    _width = config.width;
    if (_width < 300) {
        if ($(window).width() < 300) {
            _width = $(window).width() - 20;
        }
    }

    s = '<div class="showdialog" id="showdialog" style="width:' + _width + 'px;position:fixed;left:50%;top:50%;margin-left:-' + _width / 2 + 'px;">';

    if ('' != config.title) {
        s += '	<div class="th">' + config.title + '</div>';
    }
    s += '	<div class="dialogcontent">';
    s += config.content;
    s += '	</div>';
    s += '</div>';

    /*if 没有遮罩层 then添加一个*/
    //if(!$('#bg').length>0){
    //	mask();
    //}

    $('body').append(s);

    _height = $('#showdialog').outerHeight() / 2;
    $('#showdialog').css('margin-top', -_height + 'px').show();

    //$("#close").click(function(){
    //	doclose();
    //	return false;//解决Gif不动的Ie6 Bug
    //});

    /*点击空白区域关闭弹出窗口*/
    $('#bg2').click(function (e) {
        //var _con = $('#edit');   // 设置目标区域
        //if(!_con.is(e.target) && _con.has(e.target).length === 0){ // Mark 1

        if (ttt != "")
        {
            clearTimeout(ttt);
        }
        ;
        //$("#edit iframe").remove();
        //$("#edit").remove();

        $("#bg2").remove();

        $("#showdialog").remove();

        if (true == closeedit) {
            $('#bg').remove();
            $('#edit').remove();
        }

        return false;//解决Gif不动的Ie6 Bug
        //}
    })
}

/*reset from*/
function resetform() {
    var myid = $('body').data('currentform');
    $('#' + myid)[0].reset();

    if (ttt != "") {
        clearTimeout(ttt);
    }

    doclose();
}


/*判断是否微信浏览器打开*/
function is_weixin() {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}