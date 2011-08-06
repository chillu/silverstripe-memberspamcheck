<?php
/**
 * @package memberspamcheck
 */

/**
 * Takes a set of members and checks for spammyness based on {@link MemberSpamCheck}.
 * Limits the amount of members, to avoid memory problems and hitting traffic limits of external APIs.
 */
class MemberSpamCheckTask extends CliController {
	
	/**
	 * @var int How many members to query at a time.
	 */
	static $limit = 5;
	
	function process() {
		require_once(BASE_PATH . '/sapphire/thirdparty/Zend/Log/Writer/Stream.php');
		
		// SS_Log::add_writer(new Zend_Log_Writer_Stream('php://stdout'), SS_Log::NOTICE, '<=');
		// SS_Log::log(new Exception('bla'));

		$members = $this->getMembers();
		if(!$members) {
			$this->output('No members found');
			exit();
		}

		$this->output(sprintf('Checking %d members (limit: %d)', $members->Count(), self::$limit));
		
		$checker = new MemberSpamCheck();
		$checks = $checker->update($members);
		$markedAsSpam = array();
		if($checks) foreach($checks as $id => $check) {
			$member = $members->find('ID', $id);

			// Its important to fall back to a rating of 0, to avoid querying the same members successively
			// (e.g. when service APIs fail to respond). 
			// TODO Add a way to force re-checking of members
			$member->SpamCheckScore = ($check['score']) ? $check['score'] : 0;
			$memberData = $member->SpamCheckData ? (array)json_decode($member->SpamCheckData) : array();
			$memberData[get_class($this)] = (array)$check['data'];
			$member->SpamCheckData = json_encode($memberData);
			$member->write();
			
			if($member->SpamCheckScore > 0) $markedAsSpam[] = $id;
		}
		
		$this->output(sprintf('Marked %d/%d members as spam', count($markedAsSpam), $members->Count()));
	}
	
	/**
	 * @todo Should use SS_Log::log(), but that class is seriously messed up...
	 */
	protected function output($msg) {
		if(!SapphireTest::is_running_test()) echo $msg . "\n";
	}
	
	/**
	 * Get newest members first
	 * 
	 * @return DataObjectSet
	 */
	protected function getMembers() {
		return DataObject::get('Member', '"SpamCheckScore" = -1', '"Created" DESC', null, self::$limit);
	}
}