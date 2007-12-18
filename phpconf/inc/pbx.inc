<?php
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007 IKT <http://itison-ikt.de>.
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
 * Generates all Asterisk configuration files and (re)starts the Asterisk process
 */
function pbx_configure() {
	global $config, $g;

	if ($g['booting']) {
		echo "Starting Asterisk...\n";
	} else {
		killbypid("{$g['varrun_path']}/asterisk.pid");
	}

	
	$res = 0;
	if ($g['booting']) echo " - generating indications configuration...";
	$res |= indications_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating manager configuration...";
	$res |= manager_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating rtp configuration...";
	$res |= rtp_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating sip configuration...";
	$res |= sip_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating iax configuration...";
	$res |= iax_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating conferencing configuration...";
	$res |= conferencing_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating dialplan...";
	$res |= extensions_conf_generate();
	$res |= features_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating voicemail configuration...";
	$res |= voicemail_conf_generate();
	$res |= msmtp_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating isdn configuration...";
	$res |= isdn_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - applying isdn configuration...";
	$res |= isdn_configure();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - generating analog configuration...";
	$res |= analog_conf_generate();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - applying analog configuration...";
	$res |= analog_configure();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - configuring timing source...";
	$res |= pbx_configure_timing();
	if ($g['booting']) echo "done\n";
	
	if ($g['booting']) echo " - executing Asterisk...";
	$res |= mwexec("/usr/local/sbin/asterisk");
	if ($g['booting']) echo "done\n";

	if ($g['booting']) {
		if ($res == 0)
			echo "done\n";
		else
			echo "failed\n";
	}

	return $res;
}

/**
 * Loads an appropriate combination of kernel modules to provide a timing source
 * depending on what hardware is available.
 */
function pbx_configure_timing() {

	/* zaptel modules are all loaded at boot, 
	if nothing was detected we fall back to ztdummy for timing */
	if(!count(analog_get_recognized_ab_unit_numbers())) {
		mwexec("/sbin/kldunload wcfxo");
		mwexec("/sbin/kldunload wcfxs");
		//mwexec("/sbin/kldunload wct1xxp");
		//mwexec("/sbin/kldunload wct4xxp");
		mwexec("/sbin/kldload ztdummy");
	}
	return 0;
}

/**
 * Executes a command using the AMI
 */
function pbx_exec($cmd, $output=NULL) {

	$token = md5(uniqid(rand()));
	$errno = 0;
	$errstr = 0;
	$fp = fsockopen("localhost", 5038, &$errno, &$errstr, 20);
	if (!$fp) {
	  return 1;
	}
	
	fputs($fp, "Action: login\r\n");
	fputs($fp, "Username: admin\r\n");
	fputs($fp, "Secret: askozia\r\n");
	fputs($fp, "Events: off\r\n\r\n");
	usleep(500);
	
	fputs($fp, "Action: COMMAND\r\n");
	fputs($fp, "command: $cmd\r\n");
	fputs($fp, "ActionID: $token\r\n\r\n");
	usleep(500);
	
	$out = fread($fp, 38000); 
	while(strpos($out,"--END COMMAND--")==0) {
		$out .= fread($fp, 38000); 	  
	}
	fclose ($fp);
	
	$out = substr($out, strpos($out, "ActionID"));
	$out = substr($out, strpos($out, "\n") + 1);
	$out = substr($out, 0, strpos($out, "--END COMMAND--") - 1);

	// XXX : better command failure checking needed
	$output = $out;

	return 0; // XXX : this needs to return a value for SUCCESS/FAILURE
}

/**
 * Determines the validity of a given internal extension
 */
function pbx_is_valid_extension($extension) {
	if(!verify_is_numericint($extension) || (strlen($extension) < 1))
		return false;
	
	return true;
}

/**
 * Determines the validity of a given Caller ID
 * XXX : unimplemented
 */
function pbx_is_valid_callerid($id) {
	return true;
}

/**
 * Determines the validity of a given Caller ID string
 * XXX : unimplemented
 */
function pbx_is_valid_callerid_string($string) {
	return true;
}

/**
 * Determines the validity of a given secret
 * XXX : unimplemented
 */
function pbx_is_valid_secret($secret) {
	return true;
}

/**
 * Determines the validity of a given username
 * XXX : unimplemented
 */
function pbx_is_valid_username($username) {
	return true;
}

/**
 * Checks is the supplied dialpattern already exists.
 */
function pbx_dialpattern_exists($dialpattern, &$return_provider_name, $sending_provider_id=false) {
	$allpatterns = pbx_get_dialpatterns();

	if (is_array($allpatterns)) {
		foreach ($allpatterns as $provider_id => $patterns) {
			if ($provider_id == $sending_provider_id) {
				continue;
			}
			if (is_array($patterns) && in_array($dialpattern, $patterns)) {
				$return_provider_name = pbx_uniqid_to_name($provider_id);
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Determines the validity of a given dialpattern
 */
function pbx_is_valid_dialpattern($dialpattern, &$error, $incoming=false) {
	
	$incoming_regex = "/^[XNZ0-9\-\[\]\.\!]+$/";
	$outgoing_regex = "/^[XNZ0-9\-\[\]\.\!\|\+\*\#]+$/";
	
	$invalid_regex = $incoming ? $incoming_regex : $outgoing_regex;
	
	// invalid character
	if (!preg_match($invalid_regex, $dialpattern)) {
		$error = "It contains an invalid character";
		return false;
	}
	
	// doubled character
	if (substr_count($dialpattern, "|") > 1) {
		$error = "It contains an doubled \"|\" character, only one is allowed.";
		return false;
	}
	if (substr_count($dialpattern, "+") > 1) {
		$error = "It contains an doubled \"+\" character, only one is allowed.";
		return false;
	}
	if (substr_count($dialpattern, "-") > 1) {
		$error = "It contains an doubled \"-\" character, only one is allowed.";
		return false;
	}
	
	// bracketing
	$len = strlen($dialpattern);
	$open = false;
	$open_index = 0;
	for($i = 0; $i < $len; $i++) {
		if ($dialpattern[$i] == "[") {
			if ($open) {
				$error = "Nested square brackets are not allowed.";
				return false;
			} else {
				$open = true;
				$open_index = $i;
			}
		} else if ($dialpattern[$i] == "]") {
			if (!$open) {
				$error = "Unmatched square bracket.";
				return false;
			} else if (($i - $open_index) < 3) {
				$error = "Brackets are not needed for less than 2 characters.";
				return false;
			} else {
				$open = false;
			}
		}
	}
	
	// invalid characters in brackets
	/* XXX : not working...
	if (preg_match("/\[[XNZ\.\|\+]+\]/", $dialpattern)) {
		$error = "An invalid character is contained within square brackets, only digits and hyphens are allowed.";
		return false;
	}
	*/
	
	// invalid appended prefix
	if (($plus_offset = strpos($dialpattern, "+")) !== false) {
		if (!preg_match("/^[0-9\*\#]+\+/", $dialpattern)) {
			$error = "Appended prefixes may only contain digits, '*' and '#'.";
			return false;
		}
		$dialpattern = substr($dialpattern, $plus_offset+1);
	}
	
	// invalid chopped prefix
	if (($pipe_offset = strpos($dialpattern, "|")) !== false) {
		if (strpos(substr($dialpattern, 0, $pipe_offset), ".") !== false) {
			$error = "The wildcard character \".\" is not allowed before a prefix which is to be removed.";
			return false;
		}
		if (strpos(substr($dialpattern, 0, $pipe_offset), "!") !== false) {
			$error = "The wildcard character \"!\" is not allowed before a prefix which is to be removed.";
			return false;
		}
	}

	return true;
}

/**
 * Merges all *get_extensions() into a single array
 * XXX : should be reimplemented to have an optional "technology" parameter
 * so each of these sub _get_ functions can be generally implemented here
 */
function pbx_get_extensions() {
	global $config;

	return array_merge(
		sip_get_extensions(), 
		iax_get_extensions(),
		isdn_get_extensions(),
		analog_get_extensions(),
		conferencing_get_extensions(),
		external_get_extensions(),
		callgroups_get_extensions(),
		applications_get_extensions()
	);
}

/**
 * Merges all *get_providers() into a single array
 * XXX : should be reimplemented to have an optional "technology" parameter
 * so each of these sub _get_ functions can be generally implemented here
 */
function pbx_get_providers() {
	global $config;
	
	return pbx_sort_providers(
				array_merge(
					sip_get_providers(),
					iax_get_providers(),
					isdn_get_providers(),
					analog_get_providers()
				));
}

/**
 * Sorts providers by name
 */
function pbx_sort_providers($providers) {
	usort($providers, "pbx_sort_by_name");

	return $providers;
}

/**
 * Merges all *get_phones() into a single array
 * XXX : should be reimplemented to have an optional "technology" parameter
 * so each of these sub _get_ functions can be generally implemented here
 */
function pbx_get_phones() {
	global $config;
	
	return pbx_sort_phones(
				array_merge(
					sip_get_phones(),
					iax_get_phones(),
					isdn_get_phones(),
					analog_get_phones()
				));
}

/**
 * Returns a phone based on its uniqid
 */
function pbx_get_phone($uniqid) {

	if (strstr($uniqid, "SIP-PHONE")) {
		return sip_get_phone($uniqid);
			
	} else if (strstr($uniqid, "IAX-PHONE")) {
		return iax_get_phone($uniqid);

	} else if (strstr($uniqid, "ISDN-PHONE")) {
		return isdn_get_phone($uniqid);

	} else if (strstr($uniqid, "ANALOG-PHONE")) {
		return analog_get_phone($uniqid);

	} else if (strstr($uniqid, "EXTERNAL-PHONE")) {
		return external_get_phone($uniqid);

	} else {
		return "ERROR: unmatched uniqid in pbx_get_phone!";
	}
}

/**
 * Sorts phones by Caller ID
 * XXX : this sort differs in behavior from the individual _sort_phones() functions
 */
function pbx_sort_phones($phones) {
	usort($phones, "_a_sortphones");

	return $phones;
}

/* XXX : replace with "sort_by_callerid_field" */
function _a_sortphones($a, $b) {
	return strcmp($a['callerid'], $b['callerid']);
}

/**
 * Merges all *get_dialpatterns() into a single array
 * XXX : should be reimplemented to have an optional "technology" parameter
 * so each of these sub _get_ functions can be generally implemented here
 */
function pbx_get_dialpatterns() {
	global $config;

	return array_merge(
		sip_get_dialpatterns(),
		iax_get_dialpatterns(),
		isdn_get_dialpatterns(),
		analog_get_dialpatterns()
	);
}

/**
 * usort callback for sorting an array alphabetically by name field
 */
function pbx_sort_by_name($a, $b) {
	return strcmp($a['name'], $b['name']);
}

/**
 * usort callback for sorting an array numerically by extension field
 */
function pbx_sort_by_extension($a, $b) {
    if ($a['extension'] == $b['extension']) {
        return 0;
    }
    return ($a['extension'] < $b['extension']) ? -1 : 1;
}

/**
 * usort callback for sorting an array numerically by unit field
 */
function pbx_sort_by_unit($a, $b) {

    if ($a['unit'] == $b['unit']) {
        return 0;
    }
    return ($a['unit'] < $b['unit']) ? -1 : 1;
}

/**
 * Parses "core show channels" output to return active calls, channels and a channel list
 */
function pbx_get_active_calls(&$active_calls, &$active_channels, &$channel_list) {
	
	pbx_exec("core show channels", &$output);
	$lines = explode("\n", $output);

	$n = count($lines) - 3;
	for ($i = 1; $i <= $n; $i++) {
		$channel_list[] = $lines[$i];
	}
	$active_channels = substr($lines[$n+1], 0, strpos($lines[$n+1], " "));
	$active_calls = substr($lines[$n+2], 0, strpos($lines[$n+2], " "));

	return 0;
}

/**
 * Returns a dialstring based on the supplied uniqid and misc. options
 * XXX : needs to be made more granular
 * XXX : very clunky / hacky / junky
 */
function pbx_uniqid_to_dial($exten, $uniqid, $wait=false, $override=false, $readbacknum=false) {
	global $config;

	// handling for impromptu groups
	if (is_array($uniqid)) {
		$groupmembers = $uniqid;
		$uniqid = "CALLGROUP-PARALLEL-impromptu";
	}

	$id = split("-", $uniqid);
	
	$dial = "exten => $exten,1,NoOp()\n";

	if ($wait) {
		$dial .= "exten => $exten,n,Ringing()\n";
		$dial .= "exten => $exten,n,Wait($wait)\n";
	}
	if ($override == "replacenamewithnum") {
		$dial .= "exten => $exten,n,Set(CALLERID(name)=$\{CALLERID(number)})\n";
	}
	
	if ($id[0] == "SIP" && $id[1] == "PHONE") {
		$phone = sip_get_phone($uniqid);
		$readbacknum = ($readbacknum) ? $readbacknum : $phone['extension'];
		$dial .= extensions_generate_missed_call_notifications_line($exten, $phone);
		$dial .= "exten => $exten,n,".
			"Macro(vm|SIP/{$phone['extension']}|{$phone['extension']}|$readbacknum|to)\n";
			
	} else if ($id[0] == "IAX" && $id[1] == "PHONE") {
		$phone = iax_get_phone($uniqid);
		$readbacknum = ($readbacknum) ? $readbacknum : $phone['extension'];
		$dial .= extensions_generate_missed_call_notifications_line($exten, $phone);
		$dial .= "exten => $exten,n,".
			"Macro(vm|IAX2/{$phone['extension']}|{$phone['extension']}|$readbacknum|to)\n";

	} else if ($id[0] == "ISDN" && $id[1] == "PHONE") {
		$phone = isdn_get_phone($uniqid);
		$readbacknum = ($readbacknum) ? $readbacknum : $phone['extension'];
		$dial .= extensions_generate_missed_call_notifications_line($exten, $phone);
		$dial .= "exten => $exten,n,".
			"Macro(vm|CAPI/{$phone['interface']}/{$phone['extension']}|{$phone['extension']}|$readbacknum|to)\n";

	} else if ($id[0] == "ANALOG" && $id[1] == "PHONE") {
		$phone = analog_get_phone($uniqid);
		$readbacknum = ($readbacknum) ? $readbacknum : $phone['extension'];
		$dial .= extensions_generate_missed_call_notifications_line($exten, $phone);
		$dial .= "exten => $exten,n,".
			"Macro(vm|ZAP/{$phone['interface']}/{$phone['extension']}|{$phone['extension']}|$readbacknum|to)\n";

	} else if ($id[0] == "CONFERENCE" && $id[1] == "ROOM") {
		$room = conferencing_get_room($uniqid);
		$dial .= "exten => $exten,n,Answer()\n";
		$dial .= "exten => $exten,n,MeetMe({$room['number']},M)\n";
		$dial .= "exten => $exten,n,Hangup()\n";
		
	} else if ($id[0] == "EXTERNAL" && $id[1] == "PHONE") {
		$phone = external_get_phone($uniqid);
		$readbacknum = ($readbacknum) ? $readbacknum : $phone['extension'];
		$technology = pbx_uniqid_to_technology($phone['dialprovider']);
		$dial .= extensions_generate_missed_call_notifications_line($exten, $phone);
		// XXX : CAPI exception needed here!‚
		if ($technology == "ZAP") {
			$zprov = analog_get_provider($phone['dialprovider']);
			$dial .= "exten => $exten,n,".
				"Macro(vm|$technology/{$zprov['interface']}/{$phone['dialstring']}|{$phone['extension']}|$readbacknum|to)\n";
		} else {
			$dial .= "exten => $exten,n,".
				"Macro(vm|$technology/{$phone['dialprovider']}/{$phone['dialstring']}|{$phone['extension']}|$readbacknum|to)\n";
		}

	} else if ($id[0] == "CALLGROUP" && $id[1] == "PARALLEL") {
		// if this is a stored group, grab its info
		if ($id[2] != "impromptu") {
			$group = callgroups_get_group($uniqid);
			$groupmembers = $group['groupmember'];
		}

		// generate the group's dialstring
		$group_dialstring = extensions_phone_uniqids_to_dialstring($groupmembers);
		// set the voicemail phone to the first group entry
		$vm_phone = pbx_get_phone($groupmembers[0]);
		// set the read back number
		$readbacknum = ($readbacknum) ? $readbacknum : $vm_phone['extension'];

		$dial .= extensions_generate_missed_call_notifications_line($exten, $vm_phone);
		$dial .= "exten => $exten,n,".
			"Macro(vm|$group_dialstring|{$vm_phone['extension']}|$readbacknum|to)\n";

	} else if ($id[0] == "APPLICATION" && $id[1] == "MAPPING") {
		$app = applications_get_app($uniqid);
		
		$dial .= "exten => $exten,n,Answer()\n";
		if ($app['name'] == "WakeMe") {
			$conf .= "exten => $exten,n,Set(CHANNEL(language)=en)\n";
		}
		$dial .= "exten => $exten,n,{$app['name']}()\n";
		$dial .= "exten => $exten,n,Hangup()\n";
	}
	
	return $dial;
}

/**
 * Replace a string's contents with the name equivalents of the uniqids contained within.
 */
function pbx_replace_uniqids_with_names($string) {
	
	$uniqid_regex = "/(SIP|IAX|ISDN|ANALOG|CONFERENCE|CALLGROUP|EXTERNAL){1}-(PROVIDER|PHONE|ROOM|PARALLEL){1}-[0-9a-zA-Z]+/";

	preg_match_all($uniqid_regex, $string, $uniqids);
	if (!is_array($uniqids)) {
		return;
	}

	$uniqids = array_keys(array_flip($uniqids[0]));

	$names = array();
	$n = count($uniqids);
	for ($i = 0; $i < $n; $i++) {
		$names[$i] = pbx_uniqid_to_name($uniqids[$i]);
	}

	return str_replace($uniqids, $names, $string);
}

/**
 * Returns an appropriate "name" for the supplied uniqid.
 */
function pbx_uniqid_to_name($uniqid) {
	global $config, $uniqid_map;
	
	$pair = explode("-", substr(strtolower($uniqid), 0 , strrpos($uniqid, "-")));

	if ($pair[1] == "phone" && $pair[0] == "external") {
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	} else if ($pair[1] == "phone") {
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['callerid'];
	}
	if ($pair[1] == "provider") {
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}
	if ($pair[1] == "room") {
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}
	if ($pair[1] == "callgroup") {
		return $config[$pair[0]][$pair[1]][$uniqid_map[$uniqid]]['name'];
	}

	return "ERROR:UNIQID_TO_NAME_FAILED($uniqid)";
}

/**
 * Returns the channel technology associated with the supplied uniqid.
 */
function pbx_uniqid_to_technology($uniqid) {
	
	$parts = explode("-", $uniqid);
	switch($parts[0]) {
		case "SIP":
			return "SIP";
		case "IAX":
			return "IAX2";
		case "ISDN":
			return "CAPI";
		case "ANALOG":
			return "ZAP";
	}

	return "ERROR:UNIQID_TO_TECHNOLOGY_FAILED($uniqid)";
}

?>