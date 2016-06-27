<?php

/**
 * 上传文件.
 * 
 * @author  YilinSun
 * @version 1.0
 * @package main
 */
/* 用户组接口 */
require_once( __DIR__ . '/../../../global.php');
require_once syspath . '_inc/cls_api.php';
require_once ApiPath . '_adminxxx1/_main.php';


require_once('ci_upload_.php');
require_once('ci_image_.php');

class myapi extends cls_api {

   function __construct() {
      parent::__construct();
       $this->modulemain = new cls_modulemain();

        /* 检测权限 */
        if (!$this->modulemain->haspower()) {
            $this->output();
            return false;
        }
      $this->act = $this->main->ract();


      switch ($this->act) {
         case '':
            break;

         case 'showclass' : ShowClass();
            break; //显示分类

         case 'list' :
            $this->HtmlList();
            $this->output();
            break;

         case 'saveimage' :
            $this->saveimage();
            break;

         case 'del' : DoDel();
            break;

         case 'edittitle' : FormTitle();
            break;

         case 'savetitle' : SaveTitle();
            break;

         /*
           '<syl>分类管理<by syl>
           Case "showclass" : showclass() '<syl>显示分类<by syl>
           Case "nsaveclass" : SaveClass(false) '<syl>保存用户分类<by syl>

           Case "class" : ListClass() '<syl>管理分类, 添加,修改, 删除<by syl>
           Case "delclass" : DelClass()
           Case "editclass"	: FormClass() '<syl>编辑分类<by syl>
           Case "esaveclass"	: SaveClass(true)
          */
      }
   }

   function HtmlList() {
      $ftype = 1;
		
		$classid = $this->main->rqid('classid');
		$fic = $this->main->ract('fic');
      
      $sql = 'select * from `'.sh.'_uplist` where 1 ';
      $sql .= ' and isdel=0';
      $sql .= ' and ftype=:ftype';
      $sql .= ' and mytype=10';


		
		/*有分类时按分类显示*/
		if($classid>-1){
			$sql .= ' and myclassid=:classid';
			$para[':classid'] = $classid;
		}

		if('' != $fic){
			$sql .= ' and fic=:fic ';			
			$para[':fic'] = $fic;
		}

		$sql .= ' order by id desc ';

      //$sql .= ' and uid='.$GLOBALS['we']->u_id;
      
      $para[':ftype'] = $ftype;
      
      $this->j['list'] = $this->main->exers($sql, $para, 12);
      
      
   }

   function saveimage() {
      $andpreimg = 1;
      $ftype = 1;
		$fic = $this->main->ract('fic');

      $myclassid = $this->main->rqid('myclassid');

      if ($myclassid < 0) {
         $myclassid = 0;
      }

      $upload = new CI_Upload();

      $path = $this->getuploadpath($ftype);

      $uploadurl = $path['relative']; //相对路径
      $uploadpath = $path['psy'];


      $config['upload_path'] = $uploadpath;
      /* init */

      $config['is_image'] = TRUE;

      /* 这里准备加参数设置 */
      $config['allowed_types'] =ALLOWED_TYPES;
      $config['max_size'] = '2048';
      $config['max_width'] = '1920';
      $config['max_height'] = '3000';



      /* 文件名 = 用户ID, 当前日期时间, 随机数 */
      $config['file_name'] = $this->main->user['id'] . '_' . date('dHis') . rand(1000, 9999);

      $upload->initialize($config);

      $upload->do_upload('file1');

      if (count($upload->error_msg) > 0) {          
         $this->ckerr($upload->error_msg[0]);  
         
                //print_r($GLOBALS['errmsg']);die;
        
         return false;
         //showerr($upload->error_msg[0]);
      } else {
         $data = $upload->data();

         /* 上传完了,进行处理 */

         /* 宽或高大于200,生成预览图 */
         if ($upload->image_width > 260 OR $upload->image_height > 260) {

            /* 开始生成预览图 */
            $config['image_library'] = 'gd2';
            $config['source_image'] = $uploadpath . $data['file_name'];
            $config['new_image'] = $uploadpath . 'thumb/';
            $config['create_thumb'] = TRUE;
            $config['thumb_marker'] = '';
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 260;
            $config['height'] = 260;

            $img = new CI_Image_lib($config);

            $img->resize();

            if ($img->display_errors() == '') {
               //缩略成功,生成一个预览图路径
               //$urlthumb = $thumb_path .$upload->file_name;  //多余

               $rs["urlthumb"] = $uploadurl . 'thumb/' . $data['file_name'];
            } else {
               $this->ckerr($img->display_errors());
               return false;
               //echo ( $img->display_errors());
               //$urlthumb = $full_path;
               //缩略图失败,用原图做缩略图路径
            }

            unset($img);
         } else {
            $rs["urlthumb"] = $uploadurl . $data['file_name'];
         }

         /* 向上传列表中添加图片地址及属性 */



         /* 取得原始文件名, 不带后缀 */
         $title = $upload->client_name;
         $title = explode('.', $title)[0];

         //把路径和文件信息插入数据库
         $rs["uid"] = $this->main->user['id'];
         $rs["u_nick"] = $this->main->user['u_nick'];

         /* 暂时用文件名做描述 */
         $rs["title"] = $title;
         $rs["urlfile"] = $uploadurl . $data['file_name'];

         $rs["ftype"] = $ftype;
			$rs['mytype'] = 10;
			$rs['fic'] = $fic;


         $rs["stime"] = time();
         $rs["filesize"] = $upload->file_size;
         $rs["ufilewidth"] = $upload->image_width?$upload->image_width:0;
         $rs["ufileheight"] = $upload->image_height?$upload->image_height:0;
         //如果上传的不是图片 则获取不到长宽  获取不到的情况下不设置默认值0 写入数据库则会报错
         $rs["myclassid"] = $myclassid;




         $this->pdo->insert(sh . '_uplist', $rs);
      }


      unset($upload);

      $this->j['success'] = 'y';
   }

   /*
    * 生成上传路径*
    * 输入 $ftype=文件类型*
    * 输出字符串路径, 输出前进行检测, 不存在则创建路径*
    * 返回字典  relative psy路径
    */

   function getuploadpath($ftype) {
      $a = array();

      /* 根上传路径 */
      $s = '_upload/';

      switch ($ftype) {
         case 1:
            $s .= 'images/';
            break;
         case 2:
            $s .= 'flash/';
            break;
         case 3:
            $s .= 'files/';
            break;
         default :
            showerr('文件类型错误!');
            break;
      }

      $s .= date('Ym') . '/';


      $relativefile = '/' . $s;
      $psyfile = syspath . $s;

      /* if 路径不存在,则新建文件夹 */
      if (!file_exists($psyfile)) {
         mkdir($psyfile, 0777);
      }

      /* if 预览路径不存在,则新建预览文件夹路径 */
      if (!file_exists($psyfile . 'thumb/')) {
         mkdir($psyfile . 'thumb/', 0777);
      }

      $a['relative'] = $relativefile;
      $a['psy'] = $psyfile;

      return $a;
   }

}

$myapi = new myapi;
unset($myapi);
