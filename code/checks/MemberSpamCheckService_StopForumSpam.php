<?php
/**
 * @package memberspamcheck
 */

/**
 * Uses http://stopforumspam.org API.
 */
class MemberSpamCheckService_StopForumSpamOrg extends MemberSpamCheckService {
	
	static $service_url = "http://www.stopforumspam.com/api?f=json";
	
	/**
	 * @var int See http://stopforumspam.com/usage:
	 * "API queries against my server are currently limited to 20,000 per day."
	 * "Each bulk query of 5 field tests consumes one query and 0.2 for each field over 5".
	 * 
	 * With the three fields we're checking, this roughly works out as 5 member records.
	 */
	static $service_limit = 5;
	
	/**
	 * @var Int Convert a infinite frequency count into a
	 * finit spam score from 0 to 100. The score is calculated
	 * as a percentage of this limit.
	 */
	public $spamscoreFrequencyLimit = 10;
	
	protected $requiredExtensions = array('Member' => array('MemberSpamCheckExtension'));
	
	/**
	 * @param DataObjectSet
	 * @return Array Map of member IDs to an array of 'score' and 'data' values.
	 */
	function update($members) {
		$return = array();
		
		// Process members in chunks, as the API only allows querying up to 15 items at a time.
		// "Item" means property in this case, so we're conservative and querying only 5 members (3 props x 5 members)
		$memberChunks = array_chunk($members->toArray(), self::$service_limit);
		foreach($memberChunks as $memberChunk) {
			// TODO Not a terribly efficient way to chunk members...
			$return = $return + (array)$this->updateChunk(new DataObjectSet($memberChunk));
		}

		return $return;
	}
	
	/**
	 * @param DataObjectSet
	 * @return Array See {@link update()}
	 */
	protected function updateChunk($members) {
		$return = array();
		
		$map = $this->getPropertyMap();
		$isEmptyFn = create_function('$val', 'return !empty($val);');
		$ips = (@$map['IP']) ? array_filter($members->column($map['IP']), $isEmptyFn) : array();
		$emails = (@$map['Email']) ? array_filter($members->column($map['Email']), $isEmptyFn) : array();
		$nicknames = (@$map['Nickname']) ? array_filter($members->column($map['Nickname']), $isEmptyFn) : array();
		
		$ips = array_map('urlencode', $ips);
		$nicknames = array_map('urlencode', $nicknames);
		$emails = array_map('urlencode', $emails);
		
		$url = self::$service_url;
		if($ips) $url .= '&ip[]=' . implode('&ip[]=', $ips);
		if($nicknames) $url .= '&username[]=' . implode('&username[]=', $nicknames);
		if($emails) $url .= '&email[]=' . implode('&email[]=', $emails);
		
		$this->output("stopforumspam.org: Requesting " . $url);

		$response = $this->request($url);
		if(!$response) return false;
		
		
		$resultObj = json_decode($response);
		foreach(array('Nickname' => 'username', 'IP' => 'ip', 'Email' => 'email') as $objectField => $serviceField) {
			if(@$map[$objectField] && isset($resultObj->$serviceField)) {
				foreach($resultObj->$serviceField as $check) {
					$member = $members->find($map[$objectField], $check->value);
					if(!$member) continue;
					
					$this->output(sprintf('stopforumspam.org: Checking Member #%d (Email: %s) against %s', $member->ID, $member->Email, $serviceField));

					// Aggregates the results of different checks
					if(!isset($return[$member->ID])) $return[$member->ID] = array('score' => 0, 'data' => array());
					
					// Calculate score (see $spamscore_frequency_limit)
					$newScore = $this->getScoreFromFrequency($check->frequency);
					$existingScore = $return[$member->ID]['score'];
					// Takes the biggest score of the available ones
					if($newScore > $existingScore) $return[$member->ID]['score'] = $newScore;
					
					$return[$member->ID]['data'][$serviceField] = (array)$check;
				}
			}
		}

		return $return;
	}
	
	/**
	 * @param Int Absolute, positive value
	 * @return Int Value between -1 and 100
	 */
	protected function getScoreFromFrequency($frequency) {
		$max = 100;
		if($frequency == 0) return -1;
		else return min(round(($frequency / $this->spamscoreFrequencyLimit) * $max), $max);
	}
	
	/**
	 * @param String $url
	 * @return String|false
	 */
	protected function request($url) {
		// TODO Detect 403s from API (most likely meaning the traffic limit has been reached)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'SilverStripe MemberSpamCheckService Module');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		
		$response = curl_exec($ch);
		
		$curlError = curl_error($ch);
		if($curlError) {
			// SS_Log::log($curlError, SS_Log::NOTICE);
			$this->output($curlError);
			return false;
		}
		
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if((int)$statusCode > 399) {
			// SS_Log::log('Request error: ' . $statusCode, SS_Log::NOTICE);
			$this->output('Request error: ' . $statusCode . ' (Body: ' . $response . ')');
			return false;
		}
		
		return $response;
	}
	
	/**
	 * @todo Should use SS_Log::log(), but that class is seriously messed up...
	 */
	protected function output($msg) {
		if(!SapphireTest::is_running_test()) echo $msg . "\n";
	}
}