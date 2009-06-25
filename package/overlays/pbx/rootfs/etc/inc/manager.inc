<?php
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007-2008 IKT <http://itison-ikt.de>.
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

/* include all configuration functions */
require_once("functions.inc");

/**
 * Generates manager.conf which contains extra AMI users defined in the GUI
 */
function manager_conf_generate() {
	global $config;
	
	$fd = fopen("/etc/asterisk/manager.conf", "w");
	if (!$fd) {
		printf("Error: cannot open manager.conf in manager_conf_generate().\n");
		return 1;
	}
	
	$managerconfig = $config['services']['manager'];

	$conf = <<<EOD

[general]
displaysystemname = yes
enabled = yes
port = 5038
bindaddr = 0.0.0.0

[admin]
secret = askozia
deny=0.0.0.0/0.0.0.0
permit=127.0.0.1/255.255.255.255
read = system,call,log,verbose,command,agent,user,config
write = system,call,log,verbose,command,agent,user,config



EOD;

	// XXX : replace with a function, no more direct $config access
	if (is_array($config['services']['manager']['manager-user'])) {
		foreach ($config['services']['manager']['manager-user'] as $user) {
			$conf .= "[" . $user['username'] . "]" . "\n";
			$conf .= "secret = " . $user['secret'] . "\n";
			$conf .= "deny=" . $user['denyip'] . "/" . $user['denynetmask'] . "\n";
			$conf .= "permit=" . $user['permitip'] . "/" . $user['permitnetmask'] . "\n";
			if (is_array($user['read-permission'])) {
				$conf .= "read = " . implode(",", $user['read-permission']) . "\n";
			}
			if (is_array($user['write-permission'])) {
				$conf .= "write = " . implode(",", $user['write-permission']) . "\n";
			}
			$conf .= "\n";
		}
	}
	$conf .= "\n";
	
	fwrite($fd, $conf);
	fclose($fd);

	return 0;
}

/**
 * Sorts the defined Asterisk manager users by username.
 */
function manager_sort_users() {
	global $config;

	usort($config['services']['manager']['manager-user'], "_a_sortmanagerusers");
}

/* XXX : replace with "sort_by_username_field" */
function _a_sortmanagerusers($a, $b) {
	return strcmp($a['username'], $b['username']);
}

?>