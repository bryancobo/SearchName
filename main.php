<?php 

class mtime{
	
	public $userArr = array();
	public $proxyArr = array();
	public $is_proxy = true;
	public $proxynum = '';
	public $cookiepath = 'cookie/';
	public $proxyfile= 'proxy.txt';
	public $userfile = 'user.txt';
	
	public function __construct(){
		$root = dirname(__FILE__)."/";
		$this->cookiepath = $root.$this->cookiepath;
		$this->proxyfile = $root.$this->proxyfile;
		$this->userfile = $root.$this->userfile;
		$this->init();
	}
	
	public function init(){
		$this->userArr = file($this->userfile);
		$this->proxyArr = file($this->proxyfile);
		$this->proxynum = count($this->proxyArr)-1;
	}
	
	public function run(){
		$this->login();
		$this->getPrice();
			
	}
	
	
	
	public function login(){
		foreach($this->userArr as $k=>$val){
			$info = explode('----',$val);
			$post = "email=<user>&password=<pass>";
			$poststr = strtr($post,array('<user>'=>urlencode(trim($info[0])),'<pass>'=>urlencode(trim($info[1]))));
			$poststr = urlencode($poststr);
			$cookiefile = $this->cookiepath.$info[0].'.cookie';
			if(file_exists($cookiefile) && (time()-filemtime($cookiefile))<3600){
				continue;
			}
			if($this->is_proxy){
				$n = rand(0,$this->proxynum);
				$p = trim($this->proxyArr[$n]);
				$proxy = " -x $p ";
			}else{
				$proxy='';
			}
			$date = date('YnjHis');
			$cmd = "curl -sL {$proxy} \"http://piao.m.mtime.cn/Service/callback.mi\" -d \"Ajax_CallBack=true&Ajax_CallBackType=Mtime.Mobile.Pages.CallbackService&Ajax_CallBackMethod=RemoteCallbackSameDomain&Ajax_RequestUrl=http%3A%2F%2Fpiao.m.mtime.cn%2Fevent%2F%3FredirectUrl%3Dhttp%253A%252F%252Ftf4.mtime.cn%252Fh5%252F%26mainnav%3D0%23!%2Fsignin&t={$date}62819&Ajax_CallBackArgument0=mobileApi&Ajax_CallBackArgument1=%2FMobile%2FSignIn.api&Ajax_CallBackArgument2={$poststr}\" -c {$cookiefile}";
			echo $cmd."\n";
			$this->ppopen($cmd);
			@chmod($cookiefile,0777);
		}
		
		
	}
	
	
	public function getPrice(){
		foreach($this->userArr as $k=>$val){
			$info = explode('----',$val);
			$cookie = $this->cookiepath.$info[0].'.cookie';
			for($i=1;$i<=10;$i++){
				if($this->is_proxy){
					$n = rand(0,$this->proxynum);
					$p = trim($this->proxyArr[$n]);
					$proxy = " -x $p ";
				}else{
					$proxy='';
				}
				$date = time().rand(000,999);		
				$cmd = "curl -sL {$proxy} \"http://api.mtime.cn/Service/Lottery.api?Ajax_CallBack=true&Ajax_CallBackType=Mtime.Api.Pages.LotteryService&Ajax_CallBackMethod=UserLottery&Ajax_CrossDomain=1&t={$date}&Ajax_RequestUrl=http%3A%2F%2Ftf4.mtime.cn%2Fh5%2F&Ajax_CallBackArgument0=1&Ajax_CallBackArgument1=100049\" -b $cookie";
				$res = $this->ppopen($cmd);

				file_put_contents('log.log',date('Y-m-d H:i:s ').$info[0].'==='.$cmd."\n",FILE_APPEND);
				if(preg_match('/msg"\s*:\s*"(.*?)"/is',$res,$m)){
					$miconv = iconv('utf-8','gbk',$m[1]);
				}else{
					$miconv = '';
					echo $res."\n";
				}
				if($miconv=='今天的抽奖次数超出限制'){
					echo $info[0].'==='.$miconv."\n";
					break;
				}else{
					echo $info[0].'==='.$miconv."\n";
					$d = rand(4,9);
					echo 'sleep '.$d." seconds \n";
					sleep($d);
					file_put_contents('log.log',date('Y-m-d H:i:s ').$info[0].'==='.$miconv."\n",FILE_APPEND);
					
				}
				
			}
			//查看红包数量
			$this->checkPrice($cookie, $info[0]);
		}
	}
	
	public function checkPrice($cookie,$user){
		if($this->is_proxy){
			$n = rand(0,$this->proxynum);
			$p = trim($this->proxyArr[$n]);
			$proxy = " -x $p ";
		}else{
			$proxy='';
		}		
		
		$cmd = "curl -sL {$proxy} \"http://api.mtime.cn/Service/Lottery.api?Ajax_CallBack=true&Ajax_CallBackType=Mtime.Api.Pages.LotteryService&Ajax_CallBackMethod=GetLotteryUserInfo&Ajax_CrossDomain=1&Ajax_RequestUrl=http%3A%2F%2Ftf4.mtime.cn%2Fh5%2F&Ajax_CallBackArgument0=1\" -b $cookie";
		$res = $this->ppopen($cmd);
		preg_match('/"totalLotteryAwardValue":(\d+\.?\d+)/is',$res,$m);
		$msg = "{$user} 您的红包数量为：{$m[1]} 元\n";
		file_put_contents('success.log',date('Y-m-d H:i:s ').$msg,FILE_APPEND);
		file_put_contents('log.log',date('Y-m-d H:i:s ').$res."\n",FILE_APPEND);
	}
	
	public function ppopen($cmd){
		$ft = popen($cmd,'r');
		$res = '';
		while(!feof($ft)){
			$res.=fgets($ft,2048);
		}
		pclose($ft);
		return $res;
	}
	
}

$mtime = new mtime();
$mtime->run();




?>
