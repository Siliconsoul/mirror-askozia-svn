#!/usr/local/bin/php
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

$pgtitle = array("Advanced", "SIP");
require("guiconfig.inc");

$sipconfig = &$config['services']['sip'];

$pconfig['port'] = isset($sipconfig['port']) ? $sipconfig['port'] : "5060";
$pconfig['defaultexpiry'] = isset($sipconfig['defaultexpiry']) ? $sipconfig['defaultexpiry'] : "120";
$pconfig['minexpiry'] = isset($sipconfig['minexpiry']) ? $sipconfig['minexpiry'] : "60";
$pconfig['maxexpiry'] = isset($sipconfig['maxexpiry']) ? $sipconfig['maxexpiry'] : "3600";


if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = explode(" ", "port");
	$reqdfieldsn = explode(",", "Port");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['port'] && !is_port($_POST['port']))) {
		$input_errors[] = "A valid port must be specified.";
	}
	
	if (($_POST['defaultexpiry'] && !is_numericint($_POST['defaultexpiry']))) {
		$input_errors[] = "A whole number of seconds must be entered for the \"Default Registration Expiration\" timeout.";
	}
	if (($_POST['minexpiry'] && !is_numericint($_POST['minexpiry']))) {
		$input_errors[] = "A whole number of seconds must be entered for the \"Minimum Registration Expiration\" timeout.";
	}
	if (($_POST['maxexpiry'] && !is_numericint($_POST['maxexpiry']))) {
		$input_errors[] = "A whole number of seconds must be entered for the \"Maximum Registration Expiration\" timeout.";
	}

	if (!$input_errors) {
		$sipconfig['port'] = $_POST['port'];
		$sipconfig['defaultexpiry'] = $_POST['defaultexpiry'];
		$sipconfig['minexpiry'] = $_POST['minexpiry'];
		$sipconfig['maxexpiry'] = $_POST['maxexpiry'];

		write_config();
		
		config_lock();
		$retval |= sip_conf_generate();
		config_unlock();
		
		$retval |= sip_reload();
		
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<form action="advanced_sip.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr>
			<td width="20%" valign="top" class="vncell">Binding Port</td>
			<td width="80%" class="vtable">
				<input name="port" type="text" class="formfld" id="port" size="10" maxlength="5" value="<?=htmlspecialchars($pconfig['port']);?>">
			</td>
		</tr>
		<tr> 
			<td valign="top" class="vncell">Registration Expiration Timeouts</td>
			<td class="vtable">
				min:&nbsp;<input name="minexpiry" type="text" class="formfld" id="minexpiry" size="10" value="<?=htmlspecialchars($pconfig['minexpiry']);?>">&nbsp;&nbsp;max:&nbsp;<input name="maxexpiry" type="text" class="formfld" id="maxexpiry" size="10" value="<?=htmlspecialchars($pconfig['maxexpiry']);?>">&nbsp;&nbsp;default:&nbsp;<input name="defaultexpiry" type="text" class="formfld" id="defaultexpiry" size="10" value="<?=htmlspecialchars($pconfig['defaultexpiry']);?>">
				<br><span class="vexpl">The minimum, maximum and default number of seconds that incoming and outgoing registrations and subscriptions remain valid.
				<br>Default values are 60, 3600 and 120 respectively.</span>
			</td>
		</tr>
		<tr> 
			<td valign="top">&nbsp;</td>
			<td>
				<input name="Submit" type="submit" class="formbtn" value="Save">
			</td>
		</tr>
	</table>
</form>
<?php include("fend.inc"); ?>
