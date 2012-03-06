<?php
/**
 * Identifies a user as "spam" based on criteria like its nickname,
 * email address or IP used for registration.
 * Works on additional properties to {@link Member}, e.g. added by the forum
 * module as other custom code.
 */
class MemberSpamCheck {
	
	/**
	 * @var MemberSpamCheckService
	 */
	protected $service = null;
	
	/**
	 * @var int The minimum score at which to mark a user at spam.
	 * See {@link MemberSpamCheckExtension} "SpamScore" property.
	 */
	protected $minScore = 30;
		
	/**
	 * @param MemberSpamCheckService
	 */
	function setService($service) {
		$this->service = $service;
	}
	
	/**
         * 
	 * @return MemberSpamCheckService
	 */
	function getService() {
		if(!$this->service) $this->service = new MemberSpamCheckService_StopForumSpamOrg();
		return $this->service;
	}
	
	/**
	 * @param DataObjectSet
	 * @param Boolean write the members
	 * @return Array Map of IDs to a score (greater than 0 means spam)
	 */
	function update($members) {
		// TODO This could aggregate results from multiple services in the future
		return $this->getService()->update($members);
	}

}