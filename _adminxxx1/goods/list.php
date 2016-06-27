<?php
/* 商品管理 */

require_once(__DIR__ . '/../../global.php');
require_once(syspath . '/_style/cls_template.php');
require_once(adminpath . 'public.php');

class myclass extends cls_template {

    function headplus() {
        echo '<script src="/ckeditor/ckeditor.js"></script>' . PHP_EOL;
    }

    function __construct() {
        parent::__construct();

        /* 什么情况下必须返回json格式 */
        $jsonact = array(
            'esave'
            , 'nsave'
            , 'del'
        );
        if (in_array($this->act, $jsonact)) {
            $_POST['outtype'] = 'json'; //输出json格式						
        }


        /* 访问接口 */
        require_once(AdminApiPath . 'goods/_list.php');

        require_once( adminpath . 'checkpower.php' ); //检测权限

        $this->addcrumb('商品管理');
        $this->addcrumb('平台商品');
        $this->act = $this->ract();

        switch ($this->act) {
            case '':
                $this->fname = 'mylist'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'creat':
                $this->fname = 'myform'; //主内容区
                require_once( adminpath . 'main.php' ); //主模板

                break;
            case 'edit':
                $this->fname = 'formedit';
                require_once( adminpath . 'main.php' ); //主模板
                break;
            case 'admin':
                $this->fname = 'formadmin';
                require_once( adminpath . 'main.php' ); //主模板			
                break;


            /* 下面几个不需要渲染 */
            case 'nsave':
            case 'esave':
            case 'savepass':
            case 'del':
                break;
        }
    }

    /* 商品列表 */

    function mylist() {
        $j = & $GLOBALS['j'];

        $list = & $j['list'];



        crumb($this->crumb);
        ?>

        <div class="navoperate">
            <ul>
                <li><a href="?act=creat">添加商品</a></li>
            </ul>
        </div>

        <div class="listfilter">

            <form id="formsearch" style="display:inline" action="?" method="get">
                &nbsp; 

                <input type="text" name="title" value="<?php echo $j['search']['title'] ?>" placeholder="商品名称" />
                <input type="hidden" name="act" id="act" value="" />

                <input type="submit" id="btnsearch" value=" 搜索 " > &nbsp; 
            </form>
        </div>

<?php
           if (false == $list['rs'] ){
                echo '没有找到此商品';
              
            }else{
?>
        <table class="table1 j_list" cellspacing="0">
            <tr>
                <th width="80">预览图</th>
                <th width="40">商品ID</th>
                <th width="*">商品IC</th>
                <th width="200">名称</th>
                <th width="*">组合</th>                
                <th width="100">操作</th>
            </tr>

            <?php
 
                foreach ($list['rs'] as $v) {
                    echo '<tr class="j_parent">' . PHP_EOL;
                    echo '<td style="overflow:hidden"><img src="' . $v['preimg'] . '" height="40" alt="" /></td>' . PHP_EOL;
                    echo '<td>' . $v['id'] . '</td>' . PHP_EOL;
                    echo '<td>' . $v['ic'] . '</td>' . PHP_EOL;
                    echo '<td class="omit" title=' . $v['title'] . '>' . $v['title'] . '</td>' . PHP_EOL;

                    echo '<td>' . PHP_EOL;
                    $this->showmygroup($v['mygroup'], $j['listsingle']);
                    echo '</td>' . PHP_EOL;


                    echo '<td>' . PHP_EOL;
                    echo '  <a href="?act=edit&amp;id=' . $v['id'] . '">编辑</a> &nbsp; ' . PHP_EOL;
                    echo '  <a href="?act=del&amp;comid=' . $v['comid'] . '&amp;id=' . $v['id'] . '" title="删除' . $v['title'] . '" class="j_del">删除</a></td>' . PHP_EOL;
                    echo '</tr>' . PHP_EOL;
                
            }
            ?>

        </table>
        <?php
        $this->pagelist($j['list']['total']);

				}
    }

    /* 添加商品 */

    function myform() {

        $j = & $GLOBALS['j'];

        $this->addcrumb('添加');

        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="nsave" />	
            <table class="table1" cellspacing="0" >

                <tr>
                    <td>商品名称</td>
                    <td><input type="text" name="title" value="" size="80" maxlength="20"/></td>
                </tr>
                <tr>
                    <td>商品ic</td>
                    <td><input type="text" name="ic" value="" size="80" /></td>
                </tr>
                <tr>
                    <td>商品简介</td>
                    <td><input type="text" name="readme" value="" size="80" /></td>
                </tr>


                <tr>
                    <td>商品图片</td>
                    <td>
                        <input type="hidden" name="preimg" id="preimg" />
                        <a href="/_inc/upload/upload.php?ftype=1&amp;ispre=1&amp;preid=pre&amp;obj=preimg" class="j_open"><img class="pre" id="pre" src="/_images/selimg.png" /></a>
                        <br/>（图片尺寸：宽430*高320）
                    </td>
                </tr>

                <tr>
                    <td>属性</td>
                    <td>
                        <input type="radio" name="isgroup" value="0" size="80" checked="checked" class="vmiddle" /> 单品 &nbsp;
                        <input type="radio" name="isgroup" value="1" size="80" class="vmiddle" /> 组合 &nbsp; &nbsp; 

                        <span style="display:none;" id="inputmygroup">
                            组合品id*数量,用半角逗号分隔 
                            <input type="text" name="mygroup" value="" size="20" />
                        </span>
                    </td>
                </tr>

                <tr>
                    <td>商品描述</td>
                    <td>
                        <textarea id="content" name="content" rows="10" cols="60"></textarea>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>	

        </form>

        <!--在线编辑器-->
        <script>

            $(document).ready(function() {
                wedoeditor();

                /*选组合品显示组合品输入框*/
                $('input[name=isgroup]').on('click', function() {

                    if ("1" == $(this).val()) {
                        $('#inputmygroup').show();
                    } else {
                        $('#inputmygroup').hide();
                    }
                })
            })

            function wedoeditor() {
                CKEDITOR.replace('content', {
                    filebrowserBrowseUrl: '/_inc/upload/upload.php?fromeditor=1', //来自在线编辑器
                    filebrowserWindowWidth: '830',
                    filebrowserWindowHeight: '585'
                });
            }

        </script>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';


                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    function formedit() {

        $j = & $GLOBALS['j'];

        $data = $j['data'];

        $js['isgroup'] = $data['isgroup'];


        $jsonstr = json_encode($js);

        $this->addcrumb('编辑');
        crumb($this->crumb);
        ?>
        <form method="post" action="?" id="myform">
            <input type="hidden" name="act" value="esave" />	
            <input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
            <table class="table1" cellspacing="0" >

                <tr>
                    <td>商品名称</td>
                    <td><input type="text" name="title" value="<?php echo $data['title'] ?>" size="80"  maxlength="20" /></td>
                </tr>		

                <tr>
                    <td>商品ic</td>
                    <td><input type="text" name="ic" value="<?php echo $data['ic'] ?>" size="80" /></td>
                </tr>	
                <tr>
                    <td>商品简介</td>
                    <td><input type="text" name="readme" value="<?php echo $data['readme'] ?>" size="80" /></td>
                </tr>
                <tr>
                    <td>商品图片</td>
                    <td>
                        <input type="hidden" name="preimg" id="preimg"  value="<?php echo $data['preimg'] ?>" />
                        <a href="/_inc/upload/upload.php?ftype=1&amp;ispre=1&amp;preid=pre&amp;obj=preimg" class="j_open"><img class="pre" id="pre" src="<?php echo $data['preimg'] ?>"/></a>
                        <br/>（图片尺寸：宽430*高320）
                    </td>
                </tr>

                <tr>
                    <td>属性</td>
                    <td>
                        <input type="radio" name="isgroup" value="0" size="80" class="vmiddle" /> 单品 &nbsp;
                        <input type="radio" name="isgroup" value="1" size="80" class="vmiddle" /> 组合 &nbsp; &nbsp; 

                        <span style="display:none;" id="inputmygroup">
                            组合品id*数量,用半角逗号分隔 
                            <input type="text" name="mygroup" value="<?php echo $this->jsontostring($data['mygroup']) ?>" size="20" />
                        </span>
                    </td>
                </tr>

                <tr>
                    <td>商品描述</td>
                    <td>
                        <textarea id="content" name="content" rows="10" cols="60"><?php echo $data['content'] ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value=" 提 交 "  /></td>
                </tr>
            </table>		
        </form>

        <!--在线编辑器-->
        <script>
            var jsonstr = <?php echo $jsonstr ?>;//json数据,无''

            $(document).ready(function() {
                wedoeditor();

                checkradio('isgroup', jsonstr.isgroup);

                /*如果设了属性是组合，就显示出组合内容*/
                if (1 == jsonstr.isgroup) {
                    $('#inputmygroup').show();
                }

                /*选组合品显示组合品输入框*/
                $('input[name=isgroup]').on('click', function() {

                    if ("1" == $(this).val()) {
                        $('#inputmygroup').show();
                    } else {
                        $('#inputmygroup').hide();
                    }
                })
            })

            function wedoeditor() {
                CKEDITOR.replace('content', {
                    filebrowserBrowseUrl: '/_inc/upload/upload.php?fromeditor=1', //来自在线编辑器
                    filebrowserWindowWidth: '830',
                    filebrowserWindowHeight: '585'
                });
            }

        </script>

        <script type="text/javascript">

            $(document).ready(function() {

                $('#myform').bind('submit', function() {
                    j_post($(this), function(json) {
                        /*给当前表单添加on,表示正提交这个表单，加到date里*/


                        /*保存成功*/
                        if ('y' == json.success)
                        {
                            /*继续添加reset,有返回列表链接，无操作2秒自动返回列表*/

                            ttt = setTimeout("window.location.href='?'", 2000);

                            var mess = new Array();

                            mess['content'] = '<li><a href="?">二秒后自动返回列表.</a></li>';


                            /*弹出对话框*/
                            opdialog(mess);
                        } else { //保存失败，显示失败信息
                            errdialog(json);
                        }
                    })
                    return false;
                })
            })

        </script>
        <?php
    }

    function showmygroup($mygroup, &$listgoods) {
        if ('' !== $mygroup . '') {
            $a = json_decode($mygroup, true);

            if (is_array($a)) {


                $count = count($a);

                $i = 1;

                foreach ($a as $v) {
                    if (array_key_exists($v['id'], $listgoods)) {
                        echo $listgoods[$v['id']]['title'] . ' * ' . $v['count'];

                        if ($i < $count) {
                            echo ' , ';
                        }
                    }
                    $i++;
                }
            }
        }
    }

    function jsontostring($json) {
        if ('' == $json) {
            return;
        }

        $a = json_decode($json, true);
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            echo $a[$i]['id'] . '*' . $a[$i]['count'];
            if ($i < ($count - 1)) {
                echo ',';
            }
        }
    }

}

$tp = new myclass();
