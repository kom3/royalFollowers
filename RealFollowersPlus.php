<?php

class RealFollowersPlus {

	private $key = '53207678919046167893865556001283';
	public $username;
	public $cookies;

	public $msgError;
	public $new = false;
	public $last_getCoin = 0;

	/*
		Please set $username with your instagram username
		Berikan nilai true ke $new jika akun baru saja dipake atau digunakan
	*/
	public function __construct($username, $new = false){
		if(!empty($username)){
			$this->username = $username;
			$this->new = $new;
		}
	}
	/*
		Jika tidak tahu maksudnya
		Disarankan untuk menetapkan $cookies selalu false!! :)
	*/
	public function auth($cookies = false){
		$this->checkData();
		$body = 'login='.$this->username.'&auth_type=0&lang=id&need_update=0&old_login=null&app_v=1.0.0&device=0&package_name=com.rapidup.royal_followers';
		$base = 'http://insta.starfamous.ru/common/auth/';

		$response = $this->request($base, $body, $cookies);

		if($this->new === true) $this->setData();

		return $response;
	}
	public function getCoin(){
		$data = $this->auth(true);
		return $data['object']['cash']['deposit'];
	}

	/*
		Masukkan $type dari nilai 1 - 2 dan false
		1 = get 1 Coins
		2 = get 3 Coins
		false = random from aboce!
	*/
	public function miningMe($type = false){
		$this->checkData();
		$data = $this->getQuest($type);
		if(!empty($data['object']['quests']) AND count($data['object']['quests']) > 0){
			foreach($data['object']['quests'] as $datas){
				$id = $datas['id'];
				$type = $datas['type'];
				$b = $this->setReady($id, $type);
				$this->last_getCoin = $b['object']['price'];
			}
		}
	}
	/*
		Contoh URL : https://www.instagram.com/p/BFDyAufFqet/?taken-by=kouhota
		$id = Media ID
		$real_id = BFDyAufFqet
		$target_likes = Total Likes, 1 Likes = 3 Points
	*/
	public function orderLikes($id, $real_id, $target_likes){
		$this->checkData();
		$body = 'id='.$id.'&real_id='.$real_id.'&type=1&meta='.$real_id.'&head=https://scontent-sin6-1.cdninstagram.com/t51.2885-15/s480x480/e35/14733179_644578785722220_7549668795372011520_n.jpg&target_count=' . $target_likes;
		$base = 'http://insta.starfamous.ru/task/bid/';

		$response = $this->request($base, $body, true);
		return $response;
	}
	/*
		Contoh URL : https://www.instagram.com/kouhota/
		$id = Instagram ID
		$real_id = Instagram Username
		$target_likes = Total Followers, 1 Followers = 5 Points
	*/
	public function orderFollowers($id, $real_id, $target_likes){
		$this->checkData();
		$body = 'id='.$id.'&real_id='.$real_id.'&type=2&meta='.$real_id.'&head=https://igcdn-photos-e-a.akamaihd.net/hphotos-ak-xat1/t51.2885-19/11906329_960233084022564_1448528159_a.jpg&target_count=' . $target_likes;
		$base = 'http://insta.starfamous.ru/task/bid/';

		$response = $this->request($base, $body, true);
		return $response;
	}
	private function getQuest($type = false){
		if(!in_array($type, [1, 2, false])){
			return false;
		}
		if($type == false) $type = rand(1,2);
		$body = 'types='.$type.'&limits=1';
		$base = 'http://insta.starfamous.ru/task/get_quests/';

		$response = $this->request($base, $body, true);
		return $response;
	}
	private function setReady($id, $type){
		$body = 'id='.$id.'&type='.$type.'&needPay=1';
		$base = 'http://insta.starfamous.ru/task/set_ready/';

		$response = $this->request($base, $body, true);
		return $response;
	}

	private function setData(){
		$this->checkData();
		$body = 'email='.$this->username.'@gmail.com&phone_number=&birthday=null&gender=3&biography=&first_name='.$this->username.'&last_name=&external_url=&country_code=US';
		$base = 'http://insta.starfamous.ru/util/set_data/';
		$this->request($base, $body, true);
		return $this;
	}
	private function checkData(){
		$ce = ["username"];
		foreach($ce as $gg){
			if(empty($this->$gg)){
				throw new Exception('No Data : ' . $gg);
			}
		}
	}
	private function request($base, $fields, $cookies = false){
		$fields = "request=" . $this->encrypt($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $base);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_HEADER, 1);

		$headers = $this->getHeaders($cookies, $fields);
		curl_setopt($ch, CURLOPT_ENCODING , "gzip");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec ($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		curl_close ($ch);

		$cooki = $this->getStr('Set-Cookie: PHPSESSID=', ';', $header);
		if(!empty($cooki)) $this->cookies = $cooki;

		return $this->checkResponse($this->responseText($body));
	}
	private function checkResponse($body){
		$x = json_decode($body, true);
		if(!empty($x['status"']) AND $x['status"'] == 'err'){
			$this->msgError = $x['message'];
			return false;
		} else {
			return $x;
		}
	}
	private function responseText($body){
		$x = json_decode($body, true);
		return $this->decrypt($x['response']);
	}
	private function getHeaders($cookies=false, $fields){
		$x = 	['Host: insta.starfamous.ru',
				'Content-Length: '.strlen($fields),
				'Accept: application/json, text/javascript, */*; q=0.01',
				'Origin: file://',
				'x-wap-profile: http://218.249.47.94/Xianghe/MTK_Phone_KK_UAprofile.xml',
				'User-Agent: Mozilla/5.0 (Linux; Android 4.4.2; S5J+ Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36',
				'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
				'Accept-Encoding: gzip,deflate',
				'Accept-Language: id-ID,en-US;q=0.8',
				'X-Requested-With: com.rapidup.royal_followers',
				'Connection: close'
				];
		if($cookies) $x[] = 'Cookie: PHPSESSID=' . $this->cookies;
		return $x;

	}
	private function getStr($a, $b, $c){
		$d = explode($a, $c);
		$e = explode($b, $d[1]);
		return $e[0];
	}
	private function encrypt($text, $iv = "1234567891123456") {
	    return trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $text, MCRYPT_MODE_CBC, $iv)));
	}
	public function decrypt($text, $iv = "1234567891123456") { 
	    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, hex2bin($text), MCRYPT_MODE_CBC, $iv));
	}
}
