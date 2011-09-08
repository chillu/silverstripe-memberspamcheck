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
strongest criteria to determine spam scores.

## TODO ##

 * Integrate with forum module moderation on posts, and allow posting back own results to various APIs

## License ##

	Copyright © 2011 Ingo Schommer (ingo at silverstripe dot com) and SilverStripe Limited (www.silverstripe.com). All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	Neither the name of Ingo Schommer nor SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.