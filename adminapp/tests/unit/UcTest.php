<?php
class UcTest extends TestCase {
	
	public function __construct() {
		parent::__construct();
		$this->logic = ObjectCreater::create('MemberLogic');
	}

	public function testAutoLogin() {
		$ticket = '';
		$member = $this->logic->get_logined_member_by_ticket($ticket);
		$this->assertNull($member);

		$ticket = '11';
		$member = $this->logic->get_logined_member_by_ticket($ticket);
		$this->assertArrayHasKey('code', $member);

		$ticket = 'N2YyZTQyYkpLbHR0R3ZEempnR2s1VUI3aXZCVnpQWC80dXp0aTRpNzg0R1VvV3NvTUQwfGI4NjY5ZjFiYTJiZjZjNWR      jYmUwMjZjNGNmYWQ1YzEwODQyNjMyMGE%3D';
		$member = $this->logic->get_logined_member_by_ticket($ticket);
		$this->assertEquals(407, $member['code']);

		// 前端模拟登录
		$cookie = 'saltkey=s6AFJIYh;auth=7283Rp8dQQ8h%2FPAjScMbwze6juWE7E4X82uGp6R2t3NywUA0FlM';
		$agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36';
		$referer = DOMAIN.'2/index.html';
		$headers = array('User-Agent: '.$agent, 'Referer: '.$referer, 'Cookie: '.$cookie);

		// no ticket
		$json = HelperUtils::http_get('https://test.cn/index.php?mod=sso&action=index&ticket=0&_='.time(), $headers);
		$data = json_decode(trim($json, '()'), true);
		$this->assertArrayHasKey('uid', $data['data']);
		$this->assertArrayHasKey('username', $data['data']);
		$this->assertArrayHasKey('avatar', $data['data']);
		$this->assertArrayHasKey('ticket', $data['data']);
		$this->assertEmpty($data['data']['ticket']);

		// has ticket
		$json = HelperUtils::http_get('https://test.cn/index.php?mod=sso&action=index&ticket=1&_='.time(), $headers);
		$data = json_decode(trim($json, '()'), true);
		$this->assertEquals(200, $data['code']);
		$this->assertArrayHasKey('uid', $data['data']);
		$this->assertArrayHasKey('username', $data['data']);
		$this->assertArrayHasKey('avatar', $data['data']);
		$this->assertArrayHasKey('ticket', $data['data']);
		$this->assertFalse(!$data['data']['ticket']);

		// get userinfo by ticket
		$ticket = $data['data']['ticket'];
		$member = $this->logic->get_logined_member_by_ticket($ticket);
		$this->assertEquals(200, $member['code']);
		$this->assertArrayHasKey('uid', $member['data']);
		$this->assertArrayHasKey('username', $member['data']);

		// get detail userinfo by ticket
		$json = HelperUtils::http_get('https://test.cn/index.php?mod=sso&action=index&ticket=1&_='.time(), $headers);
		$data = json_decode(trim($json, '()'), true);
		$this->assertEquals(200, $data['code']);
		$this->assertArrayHasKey('uid', $data['data']);
		$this->assertArrayHasKey('username', $data['data']);
		$this->assertArrayHasKey('avatar', $data['data']);
		$this->assertArrayHasKey('ticket', $data['data']);
		$this->assertFalse(!$data['data']['ticket']);
		$ticket = $data['data']['ticket'];
		$member = $this->logic->get_logined_member_by_ticket($ticket, 'all_userinfo');
		$this->assertEquals(200, $member['code']);
		$this->assertArrayHasKey('uid', $member['data']);
		$this->assertArrayHasKey('username', $member['data']);
		$this->assertArrayHasKey('email', $member['data']);
		$this->assertArrayHasKey('phone', $member['data']);
		$this->assertArrayHasKey('emailstatus', $member['data']);
	}
}