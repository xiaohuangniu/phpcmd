<?php
/*
 +----------------------------------------------------------------------
 + Title        : 汇卡通 - 第四方支付 - 测试
 + Author       : 冯俊豪
 + Version      : V1.0.0.1
 + Initial-Time : 2017-06-14 10:01
 + Last-time    : 2017-06-14 10:01 + 冯俊豪
 + Desc         : 
 +----------------------------------------------------------------------
*/

header("Content-type: text/html; charset=utf-8");

# 引入API接口
require_once('huika/Log.php');
require_once('huika/Pay_1.php');
require_once('huika/Refund_1.php');
require_once('anxin/Return.php');

class HuikaAction extends Action {
    private $LOG;    // 日志实例
    private $PAY;    // 支付实例
    private $REFUND; // 退款实例

    public function __construct(){
        header('Access-Control-Allow-Origin:*');
        $this->LOG    = new Log_Ax();
        $this->PAY    = new Pay();
        $this->REFUND = new Refund();
        parent::__construct();
    }
    # 清空日志记录
    public function delFile(){
        $this->LOG->deleteAll($this->PAY->Config['LOG_PATH']);
    }

    /**
     * 支付测试
     */
    public function pay(){
        /************************************* 接受数据 *********************************************/
        $num    = 0.01;         // 金额
        $key    = 'FANHUA';     // 使用泛华的支付配置
        $openid = 'oL5-Es5sxER7_uprkg1SIFI5IhPM';  // 微信OPENID
        # 前台回调地址
        $url    = 'http://chexian.jupincc.com/index.php';

        /************************************* 组合请求参数 *****************************************/
        $info = $this->PAY->goPay($num, $key, $openid, $url);
        $this->LOG->addLog_File('pay_add'.time(), '支付生成 - 回调数据：',$info);   // 记录日志文件

        $res = json_decode($info,true);
        $this->assign('info', json_decode($res['payInfo'],true));
        $this->display();
    }

    public function backPay(){
        $content = file_get_contents('php://input'); // 接受POST + GET提交参数
        $this->LOG->addLog_File('pay_back'.time(), '支付通知 - 回调数据：',$content);   // 记录日志文件

        echo 'success';
        return true;
    }

    /**
     * 退款测试
     */
    public function refund(){
        $num    = 0.01;         // 金额
        $key    = 'FANHUA';     // 使用泛华的支付配置

        /************************************* 组合请求参数 *****************************************/
        $info = $this->REFUND->goRefund($num, $key, 'FANHUA201706220954128931');
        dump($info);
    }

    public function backRefund(){
        $content = file_get_contents('php://input'); // 接受POST + GET提交参数
        $this->LOG->addLog_File('refund_back'.time(), '退款通知 - 回调数据：',$content);   // 记录日志文件

        echo 'success';
        return true;
    }

    /**
     * @param string $orderId 订单号
     * @param int    $num 支付的金额 单位元
     * @param string $openId  微信openid
     * @return mixed
     */

    public function payVip($orderId='',$num=0,$openId=''){

        $num     = empty($num)     ? $_GET['amount'] : $num;
        $openId  = empty($openId)  ? $_GET['openId'] : $openId;
        $orderId = empty($orderId) ? $_GET['orderId']: $orderId;
        if(empty($num) || empty($openId)){
            ajaxReturn(-1,'网络错误，请重试~');
        }

        $key     = 'CXVIP';     // 使用车险VIP的支付配置
        $url     = 'http://chexian.jupincc.com/index.php';  # 前台回调地址 //todo 改成订单详情页

        $info = $this->PAY->goPay($num, $key, $openId, $url);
        $this->LOG->addLog_File('pay_add'.time(), '支付生成 - 回调数据：',$info);   // 记录日志文件
        $res = json_decode($info,true);
        if($res['respCode'] == '00'){ //支付请求成功返回数据
            //将order_no 入库，回调时根据它找到订单
            $update = M('order_vip')->where('order_id="'.$orderId.'"')->data(array('order_no'=>$res['merchOrderNo']))->save();
            if(!$update) return false;
        }
        return $res['payInfo']; //jsapi支付需要的参数

//        $this->assign('info', json_decode($res['payInfo'],true)); //jsapi支付需要的参数

    }

    /**
     * 购买保险vip支付回调入口
     */
    public function backPayVip(){
        $content = file_get_contents('php://input'); // 接受POST + GET提交参数
        $this->LOG->addLog_File('pay_back'.time(), '支付通知 - 回调数据：',$content);   // 记录日志文件
        file_put_contents('/var/www/chexian/test/evan_huaka.txt',$content,FILE_APPEND.PHP_EOL);
        $data = json_decode($content,true);

        /******************************** 后台处理逻辑开始 **********************************/
        if($data['respCode'] == '00'){ //支付成功
            $orderNo = $data['merchOrderNo']; //订单号
            $order = M('order_vip')->where('order_no="'.$orderNo.'"')->find();
            if($order){
               $res1 = M('order_vip')->where('order_no="'.$orderNo.'"')
                    ->data(array('status'=>1,'pay_time'=>strtotime($data['payTime'])))->save();
               $res2 =  M('user')->where('id="'.$order['uid'].'"')->data(array('vip'=>'1','vip_start'=>time()))->save();
               if($res1 && $res2){
                   echo 'success';
                   return true;
               }
            }
        }
    }

}
