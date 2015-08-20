<?php
/**
 * @package memberspamcheck
 */

/**
 * Added to {@link Member} in order to track serialized spam check results.
 * Adds to properties to the member record:
 * - "SpamCheckData": Serialized JSON object for spam values, keyed by class name of MemberSpamCheckService.
 *   The collected data is dependent on the service implementation, and may contribute to "SpamCheckScore".
 * - "SpamCheckScore": Combined value from different service ratings, from 0 to 100. 
 *    -1 indicates that the record hasn't been checked, 0 indicates that the record is not considered spam.
 */
class MemberSpamCheckExtension extends DataExtension {

	private static $db = array(
		'SpamCheckData' => 'Text',
		'SpamCheckScore' => 'MemberSpamCheck_Int',
	);

}
