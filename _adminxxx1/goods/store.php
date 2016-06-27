<?php
/* 商品管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function __construct() {
        parent::__construct();

        /* 什么情况下必须返回json格式 */
        $jsonact = array(
            'json',
            'doin',
            'doout',
            'dodelivery',
            'savealarm'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        /* 访问接口 */
        require_once(AdminApiPath . 'goods/_store.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('商品仓库');

        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'pagemain'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'in': //入库
                $this->formin();
                break;
            case 'out': //出库
                $this->formout();
                break;
            case 'delivery': //发货
                $this->formdelivery();
                break;

            case 'history': //出入库记录
                $this->fname = 'history';
                require_once( adminpath . 'main.php' ); //主模板
                break;

            case 'alarm': //警戒库存
                $this->formalarm();
                break;
        }
    }

    /* 商品列表 */

    function pagemain() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];
        $jsonstr = json_encode($j['search']); //将数组字符串化

        crumb($this->crumb);
        ?>

        <div class="navoperate">
            <ul>
                <li><a href="?act=history">库存记录</a></li>
            </ul>
        </div>
        <form id="formsearch" action="?" method="get">
            状态
            <select name="statusjb" id="statusjb">
                <option value="" selected="selected">全部</option>
                <option value="正常" >正常</option>
                <option value="报警">报警</option>
                <input type="text" name="ic" value="<?php echo $j['search']['ic'] ?>" placeholder="商品ic" />
                <input type="text" name="title" value="<?php echo $j['search']['title'] ?>" placeholder="商品名称" />
                <input type="hidden" name="act" id="act" value="" />
            </select>

            <input id="btnsearch" type="submit" value="搜索"/>           
        </form>
        <!-- 将选中数据的值更新到#mystatus-->   

        <script type="text/javascript">
            $(document).ready(function() {

                var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
                //alert(jsonstr.mytype);
                $('#statusjb').val(jsonstr.statusjb);

            })

        </script>
        <?php
        if (false == $list['rs']) {
            echo '没有找到此商品';
        } else {
            ?>    

            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="80">预览图</th>
                    <th width="40">商品ID</th>
                    <th width="*">商品IC</th>
                    <th width="*">名称</th>
                    <th width="*">警戒库存</th>  
                    <th width="80">平台库存</th>      
                    <th width="80">状态</th>         
                    <th width="80">总库存</th>
                    <th width="150">操作</th>
                </tr>

                <?php
                // print_r($list);die;
                if (is_array($list['rs'])) {
                    foreach ($list['rs'] as $v) {
                        echo '<tr class="j_parent">' . PHP_EOL;
                        echo '<td style="overflow:hidden"><img src="' . $v['preimg'] . '" height="40" alt="" /></td>' . PHP_EOL;
                        echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                        echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                        echo '<td class="omit" title=' . $v['title'] . '>' . $v['title'] . '</td>' . PHP_EOL;
                        echo '<td><a href="?act=alarm&amp;id=' . $v['id'] . '" class="j_open"> &nbsp; ' . $v['inventoriesalarm'] . ' &nbsp; </a></td>' . PHP_EOL;

                        if ($v['inventories'] < $v['inventoriesalarm']) {
                            echo '<td class="red">' . $v['inventories'] . '</td>' . PHP_EOL;
                            echo '<td>报警</td>' . PHP_EOL;
                        } else {
                            echo '<td>' . $v['inventories'] . '</td>' . PHP_EOL;
                            echo '<td>正常</td>' . PHP_EOL;
                        }
                        echo '<td>' . $v['inventoriessum'] . '</td>' . PHP_EOL;
                        echo '<td>' . PHP_EOL;
                        if (0 == $v['isgroup']) {
                            echo '  <a href="?act=in&amp;id=' . $v['id'] . '&amp;title=' . $v['title'] . '" class="j_open">入库</a> &nbsp; ' . PHP_EOL;
                            echo '  <a href="?act=out&amp;id=' . $v['id'] . '&amp;title=' . $v['title'] . '"  class="j_open">出库</a> &nbsp; ' . PHP_EOL;
                            echo '  <a href="?act=delivery&amp;id=' . $v['id'] . '&amp;title=' . $v['title'] . '"  class="j_open">发货</a> &nbsp; ' . PHP_EOL;
                            //echo '  <a href="?act=undelivery&amp;id=' . $v['id'] . '&amp;title=' . $v['title'] . '"  class="j_open">回收</a>' . PHP_EOL;
                        } else {
                            //echo '  <span class="gray">入库</span> &nbsp; ' . PHP_EOL;
                        }
                        echo '</td>';
                        echo '</tr>' . PHP_EOL;
                    }
                }
                ?>

            </table>

            <?php
            $this->pagelist($j['list']['total']);
        }
    }

    function showmygroup($mygroup, &$listgoods) {
//print_r($listgoods);die;        
        if ('' !== $mygroup . '') {
            echo '(';
            $a = explode(',', $mygroup);
            foreach ($a as $v) {
//echo $v;
                echo $listgoods[$v]['title'] . ' &nbsp; ';
            }
            echo ')';
        }
    }

    function formin() {
        ?>
        <form method="post" action="?act=doin&amp;goodsid=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">入库</th></tr>

                <tr>                 
                    <td>品名</td>    
                    <td><?php echo $this->get('title') ?></td>    
                </tr>


                <tr>                 
                    <td>凭证号</td>    
                    <td><input name="formcode" size="20" /></td>    
                </tr>

                <tr>                 
                    <td>数量</td>    
                    <td><input name="mycount" size="20" maxlength="7"/></td>    
                </tr>

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit1"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').on('submit', function() {
                    j_repost($(this), function(json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    function formout() {
        ?>
        <form method="post" action="?act=doout&amp;goodsid=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">出库</th></tr>

                <tr>                 
                    <td>品名</td>    
                    <td><?php echo $this->get('title') ?></td>    
                </tr>


                <tr>                 
                    <td>凭证号</td>    
                    <td><input name="formcode" size="20" /></td>    
                </tr>

                <tr>                 
                    <td>数量</td>    
                    <td><input name="mycount" size="20" maxlength="7" /></td>    
                </tr>

                <tr>                 
                    <td>原因</td>    
                    <td><textarea name="other" cols="30" rows="3"></textarea></td>    
                </tr>

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').on('submit', function() {
                    j_repost($(this), function(json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    /* 发货 */

    function formdelivery() {
        ?>
        <form method="post" action="?act=dodelivery&amp;goodsid=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">发货</th></tr>

                <tr>                 
                    <td>品名</td>    
                    <td><?php echo $this->get('title') ?></td>    
                </tr>


                <tr>                 
                    <td>凭证号</td>    
                    <td><input name="formcode" size="20" /></td>    
                </tr>

                <tr>                 
                    <td>数量</td>    
                    <td><input name="mycount" size="20" maxlength="7" /></td>    
                </tr>

                <tr>                 
                    <td>店铺ID</td>    
                    <td><input name="comid" size="20" /></td>    
                </tr>

                <tr>                 
                    <td>店铺IC</td>    
                    <td><input name="comic" size="20" /></td>    
                </tr>

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit1"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').on('submit', function() {
                    j_repost($(this), function(json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    function history() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];

        $this->addcrumb('库存记录');
           
        $jsonstr = json_encode($j['search']); //将数组字符串化
        crumb($this->crumb);
        ?>
        <form id="formsearch" action="?" method="get">
            类型
            <select name="mytype" id="mytype">
                <option value="all">全部</option>
                <option value="in" >入库</option>
                <option value="out">出库</option>
                <option value="delivery">发货</option>
                <option value="toplat">退回</option>
                <input type="text" name="ic" value="<?php echo $j['search']['ic'] ?>" placeholder="商品ic" />
                <input type="text" name="title" value="<?php echo $j['search']['title'] ?>" placeholder="商品名称" />
                <input type="hidden" name="act" value="history" />
            </select>

            <input id="btnsearch" type="submit" value="搜索"/>             
        </form>
        <!-- 将选中数据的值更新到#mytype-->   

        <script type="text/javascript">
            var jsonstr = <?php echo $jsonstr ?>;//json数据,无''
            $(document).ready(function() {                
                //alert(jsonstr.mytype);
                $('#mytype').val(jsonstr.mytype);

            })

        </script>
        <?php
//        print_r( $list['rs']);die;
        if (false == $list['rs']) {
            echo '没有满足您搜索条件的商品';
        } else {
            ?>  


            <table class="table1 j_list" cellspacing="0">
                <tr>
                    <th width="40">ID</th>
                    <th width="80">预览图</th>
                    <th width="40">商品ID</th>
                    <th width="40">商品IC</th>
                    <th width="*">商品名称</th>
                    <th width="*">店铺IC</th>
                    <th width="*">店铺名称</th>
                    <th width="40">类型</th>
                    <th width="*">凭证</>
                    <th width="80">数量</th> 
                    <th width="80">时间</th> 
                </tr>

                <?php
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td style="overflow:hidden"><img src="' . $v['preimg'] . '" height="40" alt="" /></td>' . PHP_EOL;
                    echo '<td>' . $v['goodsid'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                    echo '<td class="omit" title=' . $v['title'] . '>' . $v['title'] . '</td>' . PHP_EOL;
                    echo '<td>';
                    if ('0' != $v['comic']) {
                        echo $v['comic'];
                    } else {
                        echo '';
                    }
                    '</td>' . PHP_EOL;
                    echo '<td>' . $v['comname'] . '</td>' . PHP_EOL;

                    echo '<td>';
                    switch ($v['mytype']) {
                        case 'in':
                            echo '入库';
                            break;
                        case 'out':
                            echo '出库';
                            break;
                        case 'delivery':
                            echo '发货';
                            break;
                        case 'toplat':
                            echo '退回';
                            break;
                    }
                    '</td>' . PHP_EOL;

                    echo '<td>' . $v['formcode'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['mycount'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['stime'] . '</td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                }
                ?>

            </table>
            <?php
            $this->pagelist($j['list']['total']);
        }
    }

    function formalarm() {
        $j = & $GLOBALS['j'];
        ?>
        <form method="post" action="?act=savealarm&amp;goodsid=<?php echo $this->get('id') ?>" id="myform" style="width:500px;">
            <table class="table1" cellspacing="0" >
                <tr><th colspan="2">警戒库存</th></tr>

                <tr>                 
                    <td>品名</td>    
                    <td><?php echo $j['data']['title'] ?></td>    
                </tr>


                <tr>                 
                    <td>警戒库存</td>    
                    <td><input name="inventoriesalarm" size="6"  maxlength="7" value="<?php echo $j['data']['inventoriesalarm'] ?>" /></td>    
                </tr>                

                <tr> 
                    <td colspan="2"><input type="submit" name="submit" value=" 提 交 " class="submit"/></td>
                </tr>
            </table>		
        </form>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').on('submit', function() {
                    j_repost($(this), function(json) {
                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            document.location.reload();
                        } else { //保存失败，显示失败信息
                            showerrdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

}

$tp = new myclass();
unset($tp);
