#!/usr/bin/php
<?php 
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007-2009 IKT <http://itison-ikt.de>.
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
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

require("guiconfig.inc");

$pgtitle = array(gettext("Accounts"), gettext("Edit Analog Phone"));

$uniqid = $_GET['uniqid'];
if (isset($_POST['uniqid'])) {
	$uniqid = $_POST['uniqid'];
}

$carryovers = array(
	"uniqid"
);


if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;
	
	/* input validation */
	$reqdfields = explode(" ", "extension callerid");
	$reqdfieldsn = explode(",", "Extension,Caller ID");
	
	verify_input($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['extension'] && !pbx_is_valid_extension($_POST['extension']))) {
		$input_errors[] = gettext("A valid extension must be entered.");
	}
	if (($_POST['callerid'] && !pbx_is_valid_callerid($_POST['callerid']))) {
		$input_errors[] = gettext("A valid Caller ID must be specified.");
	}
	if (!isset($id) && in_array($_POST['extension'], pbx_get_extensions())) {
		$input_errors[] = gettext("A phone with this extension already exists.");
	}
	if (($_POST['voicemailbox'] && !verify_is_email_address($_POST['voicemailbox']))) {
		$input_errors[] = gettext("A valid e-mail address must be specified.");
	}
	if ($_POST['publicname'] && ($msg = verify_is_public_name($_POST['publicname']))) {
		$input_errors[] = $msg;
	}

	if (!$input_errors) {
		$ap = array();
		$ap['extension'] = $_POST['extension'];
		$ap['callerid'] = $_POST['callerid'];
		$ap['voicemailbox'] = verify_non_default($_POST['voicemailbox']);
		$ap['sendcallnotifications'] = $_POST['sendcallnotifications'] ? true : false;
		$ap['publicaccess'] = $_POST['publicaccess'];
		$ap['publicname'] = verify_non_default($_POST['publicname']);
		$ap['interface'] = $_POST['interface'];
		$ap['language'] = $_POST['language'];
		$ap['descr'] = verify_non_default($_POST['descr']);
		$ap['ringlength'] = verify_non_default($_POST['ringlength'], $defaults['accounts']['phones']['ringlength']);

		$a_providers = pbx_get_providers();
		$ap['provider'] = array();
		foreach ($a_providers as $provider) {
			if ($_POST[$provider['uniqid']] == true) {
				$ap['provider'][] = $provider['uniqid'];
			}
		}
		
		
		if (isset($id) && $a_analogphones[$id]) {
			$ap['uniqid'] = $a_analogphones[$id]['uniqid'];
			$a_analogphones[$id] = $ap;
		 } else {
			$ap['uniqid'] = "ANALOG-PHONE-" . uniqid(rand());
			$a_analogphones[] = $ap;
		}
		
		touch($g['analog_dirty_path']);
		
		write_config();
		
		header("Location: accounts_phones.php");
		exit;
	}
}

if ($uniqid) {
	$pconfig = analog_get_phone($uniqid);
} else {
	$pconfig = analog_generate_default_phone();
}
$ports = dahdi_get_ports("analog", "fxs");


include("fbegin.inc");

?><script type="text/JavaScript">
<!--
	<?=javascript_public_access_editor("functions");?>
	<?=javascript_notifications_editor("functions");?>
	<?=javascript_voicemail_editor("functions");?>

	jQuery(document).ready(function(){

		<?=javascript_public_access_editor("ready");?>
		<?=javascript_notifications_editor("ready");?>
		<?=javascript_voicemail_editor("ready");?>
		<?=javascript_advanced_settings("ready");?>

	});

//-->
</script><?

if ($input_errors) {
	display_input_errors($input_errors);
}

?><form action="phones_analog_edit.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr>
			<td colspan="2" valign="top" class="listtopic"><?=gettext("General");?></td>
		</tr>
		<tr>
			<td width="20%" valign="top" class="vncellreq"><?=gettext("Number");?></td>
			<td width="80%" class="vtable">
				<input name="extension" type="text" class="formfld" id="extension" size="20" value="<?=htmlspecialchars($pconfig['extension']);?>">
				<br><span class="vexpl"><?=gettext("The number used to dial this phone.");?></span>
			</td>
		</tr>

		<?
		display_caller_id_field($pconfig['callerid'], 1);
		display_channel_language_selector($pconfig['language'], 1);
		display_phone_ringlength_selector($pconfig['ringlength'], 1);
		?>

		<tr>
			<td valign="top" class="vncell"><?=gettext("Hardware Port");?></td>
			<td class="vtable">
				<select name="port" class="formfld" id="port"><?

				foreach ($ports as $port) {
					?><option value="<?=$port['uniqid'];?>" <?
					if ($port['uniqid'] == $pconfig['port']) {
						echo "selected";
					}
					?>><?=$port['name'];?></option><?
				}
				
				?></select>
				<br><span class="vexpl"><?=gettext("The hardware port this phone is connected to.");?></span>
			</td>
		</tr>

		<?
		display_description_field($pconfig['descr'], 1);
		?>

		<tr>
			<td colspan="2" class="list" height="12"></td>
		</tr>
		<tr>
			<td colspan="2" valign="top" class="listtopic"><?=gettext("Security");?></td>
		</tr>

		<?
		display_public_access_editor($pconfig['publicaccess'], $pconfig['publicname'], 1);
		display_provider_access_selector($pconfig['provider'], 1);
		?>

		<tr>
			<td colspan="2" class="list" height="12"></td>
		</tr>
		<tr>
			<td colspan="2" valign="top" class="listtopic"><?=gettext("Call Notifications & Voicemail");?></td>
		</tr>

		<?
		display_notifications_editor($pconfig['emailcallnotify'], $pconfig['emailcallnotifyaddress'], 1);
		display_voicemail_editor($pconfig['vmtoemail'], $pconfig['vmtoemailaddress'], 1);
		?>

		<tr> 
			<td valign="top">&nbsp;</td>
			<td>
				<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>"><?

				foreach ($carryovers as $co) {
					?>
					<input name="<?=$co;?>" id="<?=$co;?>" type="hidden" value="<?=$pconfig[$co];?>">
					<?
				}

			?></td>
		</tr>
	</table>
</form><?

include("fend.inc");
