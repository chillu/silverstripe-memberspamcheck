<?php
/**
 * @package memberspamcheck
 */

class MemberSpamCheckTest extends SapphireTest {
	
	static $fixture_file = 'memberspamcheck/tests/MemberSpamCheckTest.yml';
	
	protected $origPropertyMap;
	
	function setUp() {
		parent::setUp();
		
		$this->origPropertyMap = MemberSpamCheckService::$default_property_map;
		MemberSpamCheckService::$default_property_map = array(
			'Nickname' => 'Nickname',
			'Email' => 'Email',
			'IP' => 'IP',
		);
	}
	
	function tearDown() {
		MemberSpamCheckService::$default_property_map = $this->origPropertyMap;
		
		parent::tearDown();
	}
	
	protected $requiredExtensions = array(
		'Member' => array('MemberSpamCheckExtension', 'MemberSpamCheckService_MemberDecorator')
	);
	
	function testCheckStopForumSpam() {
		$spam1 = $this->objFromFixture('Member', 'spam1');
		$spam2 = $this->objFromFixture('Member', 'spam2');
		$spam3 = $this->objFromFixture('Member', 'spam3');
		$ham1 = $this->objFromFixture('Member', 'ham1');
		$ham2 = $this->objFromFixture('Member', 'ham2');
		
		$this->assertEquals(-1, $spam1->SpamCheckScore, 'Spam marked as not checked by default');
		$this->assertEquals(-1, $ham1->SpamCheckScore, 'Ham marked as not checked by default');

		$check = new MemberSpamCheck();
		$check->setService(new MemberSpamCheckService_StopForumSpamOrgMock());
		$members = new DataObjectSet(array($spam1, $spam2, $spam3, $ham1, $ham2));
		$checks = $check->update($members);

		// SpamCheckScore
		$this->assertGreaterThan(0, $checks[$spam1->ID]['score'], 'Marks by username');
		$this->assertGreaterThan(0, $checks[$spam2->ID]['score'], 'Marks by email');
		$this->assertGreaterThan(0, $checks[$spam3->ID]['score'], 'Marks by ip');		
		$this->assertEquals(0, $checks[$ham1->ID]['score'], "Doesn't mark users not in service result");
		$this->assertEquals(0, $checks[$ham2->ID]['score'], "Doesn't mark users not in service result");
		
		// SpamCheckData
		$this->assertFalse(empty($checks[$spam1->ID]['data']), 'Aggregates check data');
		$spam1Data = $checks[$spam1->ID]['data'];
		// $this->assertObjectHasAttribute('MemberSpamCheckService_StopForumSpamOrgMock', $spam1Data);
		// $spam1ServiceData = $spam1Data->MemberSpamCheckService_StopForumSpamOrgMock;
		$this->assertEquals('2011-07-13 05:09:08', $spam1Data['username']['lastseen']);
	}
	
	function testGetScoreFromFrequency() {
		$service = new MemberSpamCheckService_StopForumSpamOrgMock();
		$method = new ReflectionMethod($service, 'getScoreFromFrequency');
		$method->setAccessible(true);
		$this->assertEquals(-1, $method->invoke($service, 0));
		$this->assertEquals(10, $method->invoke($service, 1));
		$this->assertEquals(20, $method->invoke($service, 2));
		$this->assertEquals(100, $method->invoke($service, 10));
		$this->assertEquals(100, $method->invoke($service, 20));
	}
}

class MemberSpamCheckService_StopForumSpamOrgMock extends MemberSpamCheckService_StopForumSpamOrg implements TestOnly {
	protected function request($url) {
		return <<<JSON
{
	"success": 1,
	"username": [
		{
			"value": "spam1",
			"lastseen": "2011-07-13 05:09:08",
			"frequency": 2,
			"appears": 1
		}, {
			"value": "spam2",
			"frequency": 0,
			"appears": 0
		}, {
			"value": "spam3",
			"frequency": 0,
			"appears": 0
		}, {
			"value": "ham1",
			"frequency": 0,
			"appears": 0
		}, {
			"value": "ham2",
			"frequency": 0,
			"appears": 0
		}
	],
	"email": [
		{
			"value": "spam1@test.com",
			"frequency": 0,
			"appears": 0
		},{
			"value": "spam2@test.com",
			"normalized": "waspigi@gmail.com",
			"lastseen": "2011-03-07 07:00:58",
			"frequency": 134,
			"appears": 1
		}, {
			"value": "spam3@test.com",
			"frequency": 0,
			"appears": 0
		},{
			"value": "ham1@test.com",
			"frequency": 0,
			"appears": 0
		}, {
			"value": "ham2@test.com",
			"frequency": 0,
			"appears": 0
		}
	],
	"ip": [
		{
			"value": "1.1.1.1",
			"frequency": 0,
			"appears": 0
		},
		{
			"value": "1.1.1.2",
			"frequency": 0,
			"appears": 0
		},
		{
			"value": "1.1.1.3",
			"lastseen": "2011-07-15 14:02:46",
			"frequency": 17,
			"appears": 1
		},
		{
			"value": "2.1.1.1",
			"frequency": 0,
			"appears": 0
		},
		{
			"value": "2.1.1.2",
			"frequency": 0,
			"appears": 0
		}
	]
}
JSON;
	}
}

/**
 * Necessary to add fields to member record without relying on existence of forum module etc.
 */
class MemberSpamCheckService_MemberDecorator extends DataObjectDecorator implements TestOnly {
	function extraStatics() {
		return array(
			'db' => array(
				'Nickname' => 'Varchar',
				'IP' => 'Varchar',
			)
		);
	}
}