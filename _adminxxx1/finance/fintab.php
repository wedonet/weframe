<!--酒店业务选项卡，酒店业务共用-->

<div class="tab1" id="j_tabbiz">
	<ul>
            <li id="j_goods"><a href="commoney.php?comid=<?php echo $comid ?>">神灯财务</a></li>

            <li id="j_goods"><a href="commoneydn.php.php?comid=<?php echo $comid ?>">店内有售财务</a></li>

	
             
	</ul>
	<div class="clear"></div>
</div>


<!--<script type="text/javascript">

	/*给当前菜单加on*/
	function Menuon(){
		var url = this.location.pathname;	//取路径和文件名

		
		if(url.indexOf('.')<1){  //如果“.”的位置小于1（没有），就不执行添加on的效果 
			return(false);
		}

		/*取url文件名*/
		var a = url.split('/');

		var filename = a[a.length-1];

		$("#j_tabbiz a").each(function(){
			var o = $(this);
			if ( o.attr('href').indexOf(filename)>-1 )
			{

				o.addClass('on');
			}
		})
	}

	$(document).ready(function(){
		Menuon();

	})

</script>-->

<script type="text/javascript">

	/*给当前菜单加on*/
	function Menuon(){
            
		var url = this.location.pathname;	//取路径和文件名
		
                
		if(url.indexOf('.')<1){  //如果“.”的位置小于1（没有），就不执行添加on的效果 
			return(false);
		}

		/*取url文件名*/
		a = url.substr(url.lastIndexOf("/")+1);
                
//		var filename = a[a.length-1];

		$("#j_tabbiz a").each(function(){
			var o = $(this);
                        var f=o.attr('href').replace(/(.+)[\\/]/,"");
                        var b = f.split('?');

			if (a==b[0])
			{

				o.addClass('on');
			}
		})
	}

	$(document).ready(function(){
		Menuon();

	})

</script>
