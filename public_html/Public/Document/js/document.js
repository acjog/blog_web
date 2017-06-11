function loadurl(id,url){
  //json仅仅需要title content
  $.get(url,function (data){ 
      $(".layout header >  h1").html(data["title"]);
      content=data["content"];
      content=content.replace(/(<[^>]*>)\r\n/g,"$1");
      content=content.replace(/\r\n(?!\[\/code\])/g,"<br/>");
      content=content.replace(/(\[code[^\]]*\])<br\/>/g,"$1");
      content=content.replace(/\r\n(\[\/code])/g,"$1");
      content=content.replace(/\[(\[?)(source|code)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*(?:\[(?!\/\2\])[^\[]*)*)\[\/\2\])?)(\]?)/g,'<pre name="code" class="brush: java;">$5</pre>');
      $(".layout .content").html(content);
      $(".layout .url").html("日志链接: "+"http://cosx.me/p/"+id+".html");
      var d = new Date();
      d.setTime(Number(data['post_date'])*1000);
      $(".layout header > #post_date").html("日期: "+d.toLocaleString());
      d.setTime(Number(data['post_modified'])*1000);
      $(".layout header > #post_modified").html("最后更新日期: "+d.toLocaleString());
      highlightCode();
  },"json");
}

//默认html没有需要js高亮
function transform(){
      content=$(".layout .content").html();
      //console.log(content);
      content=content.replace(/(<[^>]*>)\r\n/g,"$1");
      content=content.replace(/\r\n(?!\[\/code\])/g,"<br/>");
      content=content.replace(/(\[code[^\]]*\])<br\/>/g,"$1");
      content=content.replace(/\r\n(\[\/code])/g,"$1");
      content=content.replace(/\[(\[?)(source|code)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*(?:\[(?!\/\2\])[^\[]*)*)\[\/\2\])?)(\]?)/g,'<pre name="code" class="brush: java;">$5</pre>');
      $(".layout .content").html(content);
      highlightCode();
}

function highlightCode(){
    //博客模式<br /> 当换行
    SyntaxHighlighter.config.bloggerMode = true;
    SyntaxHighlighter.config.strings.expandSource = 'show source';
    window.SyntaxHighlighter.highlight('pre');
}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function getPageId()
{
	var reg = new RegExp(".*\/([0-9]+)\.html.*");
	var r = window.location.href.match(reg);
	if (r != null) return r[1]; return "new";
}

function  tagordirectory(){
    var hash=window.location.hash;
    if (hash!=undefined && hash!=''){
        start=hash.indexOf('#',0);
        if (start>=0){
            start++;
        }
        s="a[data_id="+hash.substring(start,hash.length)+"]";
        $node=$(s);
        $tag_or_category=$node.parent().parent().parent().parent();
        if ($tag_or_category.hasClass('category')){
            $("#select").val("目录");
            $("#select").change();
        } else if ($tag_or_category.hasClass('post_tag')){
            $("#select").val("标签");
            $("#select").change();
        }
        $tag_or_category.toggleClass("closed",false);  //移除closed属性
        $tag_or_category.toggleClass("active",true);   //添加active属性
        $tag_or_category.toggleClass("active",false); //移除closed属性，因为只有一个active属性
        $li=$node.parent().parent();
        $li.toggleClass("closed",false);
        $li.toggleClass("active",true);
        start=hash.indexOf("_",0);
        if (start>=0){
            start++;
        }
        url=window.location.href;
        ret=url.search(/wiki.html/);
        if (-1!=ret ){
            id = hash.substring(start,hash.length);
            url="/man/"+id+".json";
            loadurl(id,url);
        } else {
            transform();
        }
    } else {
       transform();
    }
}

var TimeFn = null;  //单击和双击并存
var click_state = 0; //表示单击和双击的状态
var g_rel_url = "";
function init(){
    //点击加载内容
    $("nav ul:first li  > div > a").dblclick( function(){
        clearTimeout(TimeFn);
        click_state = 0;
        id=$(this).attr('data_id');
        start=id.indexOf('_',0);
        if (start>0){
            start++;
            window.open("/manage.php?p="+id.substring(start,id.length));
        }
        return false;
    });

    $("nav ul:first li > div > a").click( function(){
		url=window.location.href;
		tourl=$(this).attr('href');
		ret=url.search(/wiki.html/);
		if (-1==ret && '#'!=tourl && 0==click_state){
		    g_rel_url = tourl;
            click_state = 1;
            TimeFn = setTimeout(function(){
		        //window.open(g_rel_url);
				window.location = g_rel_url;
                click_state = 0;
            },600);
		    return false;
		}
		data_id=$(this).attr('data_id');
		if (''!=data_id){
		    start=data_id.indexOf('_',0);
		    if (start>=0){
			start++;
		    }
		    id = data_id.substring(start,data_id.length);
		    url='/man/' + id + ".json";
		    loadurl(id,url);
		    window.location.hash=$(this).attr("data_id");  //这里要用data_id，否则有锚点
		}
		$li = $(this).parent().parent();
		var is_active = $li.hasClass("active");  //记录去掉active之前li节点的状态
		$("li.active").toggleClass("active",false);
		if ($li.hasClass("closed")){
		      $li.toggleClass("closed",false);
		      $li.toggleClass("active",true);
		} else {
		      if (is_active){ 
			  $li.toggleClass("active",false);
			  $li.toggleClass("closed",true);
		      } else {
			  $li.toggleClass("active",true);
		      }
		}
        return false;
    });
   
    //点击折叠与展开
    $(".tree-icon-switch").click( function(){
        $li = $(this).parent().parent();
        if ($li.hasClass("closed")){
            $li.toggleClass("closed",false);
            $li.toggleClass("active",true);
            $li.toggleClass("active",false);
        } else {
            $li.toggleClass("active",false);
            $li.toggleClass("closed",true);  // li属性
        }
    });
    //搜索
    $("#search").keypress( function (event){
        if(event.keyCode==13) {
            val=$("#search").val();
            if (undefined!=val && ''!=val) {
                post_val='keywords='+val+''
                $.ajax({
                    type: 'POST',
                    url: '/search.php',
                    data: post_val,
                    success: function (data){
                      $(".layout header >  h1").html("关键字: "+val + " 搜索结果");
                      $(".layout .content").html(data);
                      $(".layout header > #post_date").html("");
                      $(".layout header > #post_modified").html("");
                    } ,
                    dataType: 'html'
                });
            }
            return false;
        }
    });
	//写笔记
	$("#new").click( function() {
		id = getPageId();
		window.location = "/manage.php?p=" + id;	
		r =  "/manage.php?p=" + id;
		console.log(r);
	}
	);
    //目录与标签选择
    $("#select").change( function () {
        var checkvalue=$("#select").val();
        if (checkvalue=="目录"){
             $(".post_tag").toggleClass("disappear",true);
             $(".category").toggleClass("disappear",false);
        }else{
             $(".post_tag").toggleClass("disappear",false);
             $(".category").toggleClass("disappear",true);
        }
    });
    //全部展开与折叠
    $("#fold").click( function() {
        var closed="closed";
        var val="全部展开";
        if ($(this).attr("value")=="全部展开"){
            closed="active";
            val="全部折叠";
        }
        if (closed=="closed"){
            $("ul li").toggleClass("active",false);
            $("ul li").addClass("closed");
        }else{
	    $("ul li").toggleClass("closed",false);
            $("ul li").addClass("active");
            $("ul li").removeClass("active");  //去掉选中状态
        }
        $(this).attr("value",val);
    });
    //默认标签查看，触发change事件
    $("#select").val("目录");
    $("#select").change();
    //标签或目录高亮
    tagordirectory();
}

function createTree($li,data)
{
    $li.append("<ul></ul>");
    for (var i=0;i<data.length;i++) {
            $child = $li.children().last(); 
            url="#";
            if (data[i]['url']!=undefined){
                url=data[i]['url'];
            }
            taxonomy_type=""
            if (data[i]['type']!=undefined){
                taxonomy_type=data[i]['type'];
            }
            id="";
            if (data[i]['id']!=undefined){
                    id = data[i]['id'];
            }
            $child.append("<li class='closed "+taxonomy_type+"' ><div><i class='tree-icon-switch'></i><i class='tree-icon-item'></i><a href='"+url+"' data_id='"+id+"' >"+data[i]['name']+"</a></div></li>");
            if ((data[i]["data"]!=undefined) && (data[i]["data"].length>=1)){
                $child = $child.children().last();
                createTree($child,data[i]["data"]);
            }
    }
}

function initTree(json)
{
    var $menue = $('nav');
    createTree($menue,json["data"]);
    $('nav ul:first').attr("class","thinktree-showline"); //显示线条
}

function main(){
    init();
}
