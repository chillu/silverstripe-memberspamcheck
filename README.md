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
 * PHP 5.3 or newer (with JSON support)
 * PHP curl extension

## Usage ##

Run `php sapphire/cli-script.php MemberSpamCheckTask` on the commandline.

## TODO ##

 * Integrate with forum module moderation on posts, and allow posting back own results to various APIs

## License ##

	Copyright © 2011 Ingo Schommer (ingo at silverstripe dot com) and SilverStripe Limited (www.silverstripe.com). All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	Neither the name of Ingo Schommer nor SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.