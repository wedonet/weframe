<!--酒店业务选项卡，酒店业务共用-->

<div class="tab1" id="j_tabbiz">
	<ul>
		<li id="j_goods"><a href="goods.php?comid=<?php echo $comid ?>">商品管理</a></li>

		<li id="j_goods"><a href="goodsaccount.php?comid=<?php echo $comid ?>">商品统计</a></li>

		<li id="j_roomcode"><a href="place.php?comid=<?php echo $comid ?>">铺位</a></li>
		
		<li id="j_device"><a href="device.php?comid=<?php echo $comid ?>">设备管理</a></li>	
		
		<li id="j_order"><a href='order.php?comid=<?php echo $comid ?>'>E神灯定单管理</a></li>
		<li id="j_hotel_order"><a href='hotel_order.php?comid=<?php echo $comid ?>'>店内有售定单管理</a></li>

		<li id="j_logoperate" style="display:none"><a href='logoperate.php?comid=<?php echo $comid ?>'>操作日志</a></li>
		
		<li id="j_account"><a href='account.php?comid=<?php echo $comid ?>'>财务管理</a></li>

		<li id="j_user"><a href='user.php?comid=<?php echo $comid ?>'>操作员</a></li>
      
      <li id="j_store"><a href='store.php?comid=<?php echo $comid ?>'>出入库记录</a></li>
                
		<li id="j_import" style="display:none"><a href='' >酒店会员</a></li>
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
