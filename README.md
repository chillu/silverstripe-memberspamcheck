# Member Spam Checker Module #

## Overview ##

Checks existing member records for signs of spam, based on their user data.
Helpful addition to the forum module in order to combat spam signups which
get past standard captch techniques.

By default, it hooks into the free API of [stopforumspam.org](http://stopforumspam.org)
(only for non-commercial use, see [terms of service](http://stopforumspam.com/usage)).

## Maintainer ##

 * Ingo Schommer <ingo at silverstripe dot com>

## Requirements ##

 * SilverStripe 2.4 or newer
 * PHP 5.2 or newer (with JSON support)
 * PHP curl extension

## Usage ##

Run `php sapphire/cli-script.php MemberSpamCheckTask` on the commandline,
which will run a check against a predefined amount of `Member` records,
starting with the newest by creation date. It writes the `SpamCheckScore` and 
`SpamCheckData` properties for each record, based on the implemented check classes.

## Spam Score ##

A score of `-1` means the record hasn't been checked, `0` means its not detected as spam,
and `1-100` is the aggregated spam score based on various criteria in the implemented check classes.

## Supported Member Properties ##

By default, three properties are supported on the `Member` class: `Email`, `Nickname` and `IP`
(see `MemberSpamCheckService::$default_property_map`). Only `Email` is activated by default.
The other two fields depend on your usage (and extension) of the `Member` class.
The [forum module](http://www.silverstripe.org/forum-module) adds `Nickname`.

`IP` tracking has to be defined in custom code (e.g. in your signup logic).
You can use the `SS_HTTPRequest->getIP()` method to retrieve the client IP.
It is highly recommended to use this flag, as the originating IP is one of the
strongest criteria to determine spam scores. See "Howto: Track IP signups on the forum module" below.

## Howto ##

### Track IP signups on forum ###

The [forum module](http://www.silverstripe.org/forum-module) has an `onForumRegister()`
hook which is invoked on a new `Member` record. We can use this to track `IP` information:

MyMemberDecorator.php:

	<?php
	class MyMemberDecorator extends DataObjectDecorator {

		function extraStatics() {
			return array(
				'db' => array(
					'IP' => 'Varchar(200)',
				)
			);
		}

		function onForumRegister($request) {
			// Check for weird IP address formats like "97.72.127.18, 97.73.64.151". see http://www.regular-expressions.info/examples.html
			$ip = $request->getIP();
			if($ip && !preg_match('/^\b(?:\d{1,3}\.){3}\d{1,3}\b$/', $ip)) {
				// Write first detected IP, rather than a comma-separated list
				$this->owner->IP = trim(array_pop(preg_split('/\s*,\s*/', $ip)));
				$this->owner->write();
			}
		}
	}
	
mysite/_config.php

	DataObject::add_extension('Member', 'MyMemberDecorator');
	

### Suspend spammy members on the forum module ###

By default, the detected spam score has no effect on functionality such as denying log in,
posting comments or other user actions. In case you are using the forum module,
it comes with a built-in `SuspendedUntil` date that we can use to lock out spammy users from posting.
In order to write this property, we subclass `MemberSpamCheckTask` as follows:

	class MyMemberSpamCheckTask extends MemberSpamCheckTask {

		protected function updateMembers($members) {
			$spamMembers = parent::updateMembers($members);
			foreach($spamMembers as $spamMember) {
				// We don't have a plain "suspended flag", just make it a reaaaaallly long time.
				// On the other hand, its useful to work back to when a member was flagged.
				$spamMember->SuspendedUntil = date('Y-m-d', strtotime('+10 years', SS_Datetime::now()->Format('U')));
				$spamMember->write();
			}

			return $spamMembers;
		}

		/**
		 * Limit to members which aren't already suspended.
		 */
		protected function getMembers() {
			return DataObject::get('Member', '"SpamCheckScore" = -1 AND "SuspendedUntil" IS NULL', '"Created" DESC', null, $this->getLimit());
		}

	}
	
Run the task like before, but with the new name: `php sapphire/cli-script.php MyMemberSpamCheckTask`.


## TODO ##

 * Integrate with forum module moderation on posts, and allow posting back own results to various APIs

## License ##

	Copyright © 2011 Ingo Schommer (ingo at silverstripe dot com) and SilverStripe Limited (www.silverstripe.com). All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	Neither the name of Ingo Schommer nor SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.