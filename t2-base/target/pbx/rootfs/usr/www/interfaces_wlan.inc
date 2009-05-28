<?php 
/*
	$Id: interfaces_wlan.inc 216 2007-08-01 12:17:59Z mkasper $
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2007 Manuel Kasper <mk@neon1.net>.
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/


function wireless_get_standards($if) {
	$standards = array();
	
	$fd = popen("/sbin/ifconfig -m $if", "r");
	
	if ($fd) {
		while (!feof($fd)) {
			$line = trim(fgets($fd));
			
			if (preg_match("/media \S+ mode (11\S+)/", $line, $matches)) {
				$standards[] = $matches[1];
			}
		}
	}
	
	return array_unique($standards);
}

function wireless_get_channellist($if) {
	
	$chanlist = array();
	
	$fd = popen("/sbin/ifconfig $if list chan", "r");
	if ($fd) {
		while (!feof($fd)) {
			$line = trim(fgets($fd));
			
			/* could have two channels on this line */
			$chans = explode("Channel", $line);
			
			foreach ($chans as $chan) {
				if (preg_match("/(\d+)\s+:\s+(\d+)\s+Mhz\s+(.+)/", $chan, $matches)) {
					$chaninfo = array();
					$chaninfo['chan'] = $matches[1];
					$chaninfo['freq'] = $matches[2];
					$chaninfo['mode'] = trim($matches[3]);
					
					$chanlist[$chaninfo['chan']] = $chaninfo;
				}
			}
		}
		pclose($fd);
	}
	
	ksort($chanlist, SORT_NUMERIC);
	
	return $chanlist;
}


?>