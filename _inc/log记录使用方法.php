<?php
exit();

//�뿴����




//������־��¼�������  ���Է���config.php ������

$GLOBALS['start_log']=$config['start_log']=true;//�Ƿ�����־��¼
$config['mongodbhost']='192.168.0.22';//mongo����
$config['mongodbport']="27017";//mongo�˿�
$config['log_type']='USER|ERROR|INFO|ERR|FETCH|INSERT|DOSQL|UPDATE|DEL|EXECUTE';
//��Ҫ��¼����־���� �����������õĲ������¼
if($config['start_log']){
    require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'log.php');
    require_once( syspath . '_inc' . DIRECTORY_SEPARATOR . 'ClsMongoDb.php');
    try{
         $GLOBALS['mongodb']=new ClsMongoDb(['host'=>$config['mongodbhost'],'port'=>$config['mongodbport']]);
    } catch (MongoException  $ex) {
    //��ʼ��mongodb ʧ�� �����ֿ���ѡ��
    // $GLOBALS['start_log']=FALSE;       //�޷�����mongo�򲻽�����־��¼
        
    //����ʹ���ı���ʱ��¼
    $GLOBALS['log']=new Log(["do_action"=>'do_action_by_txt',"log_name" =>"log.log",'log_dir'=>__DIR__."/_cache/",'log_type'=>$config['log_type']]);
    $GLOBALS['log']->add("ERROR","�޷�����mongodb", __FILE__, __LINE__);
        
        
    //�����׳��쳣  die()������
    //throw new MongoException ("�޷�����mongo");die();
    }
    
    if(!isset($GLOBALS['log'])){//���mongo��ʼ����������mongo��¼��ʽ��ʼ����־��¼
        $GLOBALS['log']=new Log(["do_action"=>'do_action_by_mongodb',"mongodbname" =>"shengdenglog",'log_type'=>$config['log_type']]);
    }    
}

/*----------------------------------------------------------------------*/

//����Ҫ�����¼��Ϣ�ĵط� �����ֵ����

if(isset($GLOBALS['start_log']) && !empty($GLOBALS['start_log'])){//����������Ƿ�����¼
	//����һ  ȫ����д ��¼��Ϣ���� ���������������� ���������¼
	//������ sql��� 
	//������ sql��丽���Ĳ���
	//������ �����嵱ǰ�ļ������к� һ��Ĭ�ϼ���
    $GLOBALS['log']->autosql('FETCH',$sql,$para,__FILE__,__LINE__);	
	
	//����һ  ȫ����д ��¼��Ϣ���� ���������������� ���������¼
	//������ Ҫ��¼����Ϣ
	//������ ������ ��ǰ�ļ������к� һ��Ĭ�ϼ���
    $GLOBALS['log']->add('INFO',"��ѯ�ɹ�",__FILE__,__LINE__);

	
    //������ѡ��һ  ����¼                
}