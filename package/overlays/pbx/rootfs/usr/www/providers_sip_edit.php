#!/usr/bin/php
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

require("guiconfig.inc");

$pgtitle = array(gettext("Providers"), gettext("Edit SIP Account"));

if (!is_array($config['sip']['provider']))
	$config['sip']['provider'] = array();

sip_sort_providers();
$a_sipproviders = &$config['sip']['provider'];

$a_sipphones = sip_get_phones();

$pconfig['codec'] = array("ulaw");


$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

/* pull current config into pconfig */
if (isset($id) && $a_sipproviders[$id]) {
	$pconfig['name'] = $a_sipproviders[$id]['name'];
	$pconfig['readbacknumber'] = $a_sipproviders[$id]['readbacknumber'];
	$pconfig['username'] = $a_sipproviders[$id]['username'];
	$pconfig['authuser'] = $a_sipproviders[$id]['authuser'];
	$pconfig['fromuser'] = $a_sipproviders[$id]['fromuser'];
	$pconfig['secret'] = $a_sipproviders[$id]['secret'];
	$pconfig['host'] = $a_sipproviders[$id]['host'];
	$pconfig['fromdomain'] = $a_sipproviders[$id]['fromdomain'];
	$pconfig['noregister'] = $a_sipproviders[$id]['noregister'];
	$pconfig['manualregister'] = $a_sipproviders[$id]['manualregister'];
	$pconfig['port'] = $a_sipproviders[$id]['port'];
	$pconfig['prefix'] = $a_sipproviders[$id]['prefix'];
	$pconfig['dialpattern'] = $a_sipproviders[$id]['dialpattern'];
	$pconfig['dtmfmode'] = $a_sipproviders[$id]['dtmfmode'];
	$pconfig['natmode'] = $a_sipproviders[$id]['natmode'];
	$pconfig['language'] = $a_sipproviders[$id]['language'];
	$pconfig['qualify'] = $a_sipproviders[$id]['qualify'];
	$pconfig['calleridsource'] = 
		isset($a_sipproviders[$id]['calleridsource']) ? $a_sipproviders[$id]['calleridsource'] : "phones";
	$pconfig['calleridstring'] = $a_sipproviders[$id]['calleridstring'];
	$pconfig['incomingextensionmap'] = $a_sipproviders[$id]['incomingextensionmap'];
	$pconfig['override'] = $a_sipproviders[$id]['override'];
	$pconfig['overridestring'] = $a_sipproviders[$id]['overridestring'];
	if(!is_array($pconfig['codec'] = $a_sipproviders[$id]['codec']))
		$pconfig['codec'] = array("ulaw");
	$pconfig['manual-attribute'] = $a_sipproviders[$id]['manual-attribute'];
}

if ($_POST) {

	unset($input_errors);
	$_POST['dialpattern'] = split_and_clean_lines($_POST['dialpattern']);
	$_POST['manualattributes'] = split_and_clean_lines($_POST['manualattributes']);
	$_POST['incomingextensionmap'] = gather_incomingextensionmaps($_POST);
	$pconfig = $_POST;
	parse_str($_POST['a_codecs']);
	parse_str($_POST['v_codecs']);
	$pconfig['codec'] = array_merge($ace, $vce);

	/* input validation */
	$reqdfields = explode(" ", "name username host");
	$reqdfieldsn = explode(",", "Name,Username,Host");
	
	verify_input($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['username'] && !pbx_is_valid_username($_POST['username']))) {
		$input_errors[] = gettext("A valid username must be specified.");
	}
	if (($_POST['fromuser'] && !pbx_is_valid_username($_POST['fromuser']))) {
		$input_errors[] = gettext("A valid \"fromuser\" must be specified.");
	}
	if (($_POST['secret'] && !pbx_is_valid_secret($_POST['secret']))) {
		$input_errors[] = gettext("A valid secret must be specified.");
	}
/*	if (($_POST['host'] && !verify_is_hostname($_POST['host']))) {
		$input_errors[] = "A valid host must be specified.";
	}*/
	if (($_POST['port'] && !verify_is_port($_POST['port']))) {
		$input_errors[] = gettext("A valid port must be specified.");
	}
	if (($_POST['qualify'] && !verify_is_numericint($_POST['qualify']))) {
		$input_errors[] = gettext("A whole number of seconds must be entered for the \"qualify\" timeout.");
	}
	if ($_POST['calleridsource'] == "string" && !pbx_is_valid_callerid_string($_POST['calleridstring'])) {
		$input_errors[] = gettext("A valid Caller ID string must be specified.");
	}
	if (($_POST['override'] == "prepend" || $_POST['override'] == "replace") && !$_POST['overridestring']) {
		$input_errors[] = gettext("An incoming Caller ID override string must be specified.");
	}
	if ($msg = verify_readback_number($_POST['readbacknumber'])) {
		$input_errors[] = $msg;
	}
	
	// pattern validation
	if (isset($id)) {
		$current_provider_id = $a_sipproviders[$id]['uniqid'];
	}
	if (is_array($_POST['dialpattern'])) {
		foreach($_POST['dialpattern'] as $p) {
			/*if (pbx_dialpattern_exists($p, &$return_provider_name, $current_provider_id)) {
				$input_errors[] = "The dial-pattern \"$p\" already exists for \"$return_provider_name\".";
			}*/
			if (!pbx_is_valid_dialpattern($p, &$internal_error)) {
				$input_errors[] = sprintf(gettext("The dial-pattern \"%s\" is invalid. %s"), $p, $internal_error);
			}
		}
	}
	if (is_array($_POST['incomingextensionmap'])) {
		foreach($_POST['incomingextensionmap'] as $map) {
			/* XXX : check for duplicates */
			if ($map['incomingpattern'] && !pbx_is_valid_dialpattern($map['incomingpattern'], &$internal_error, true)) {
				$input_errors[] = sprintf(gettext("The incoming extension pattern \"%s\" is invalid. %s"), $map['incomingpattern'], $internal_error);
			}
		}
	}
	if ($msg = verify_manual_attributes($_POST['manualattributes'])) {
		$input_errors[] = $msg;
	}

	// this is a messy fix for properly and encoding the content
	$pconfig['manual-attribute'] = array_map("base64_encode", $_POST['manualattributes']);

	if (!$input_errors) {
		$sp = array();		
		$sp['name'] = $_POST['name'];
		$sp['readbacknumber'] = verify_non_default($_POST['readbacknumber']);
		$sp['username'] = $_POST['username'];
		$sp['fromuser'] = $_POST['fromuser'];
		$sp['authuser'] = $_POST['authuser'];
		$sp['secret'] = $_POST['secret'];
		$sp['host'] = $_POST['host'];
		$sp['port'] = $_POST['port'];
		$sp['fromdomain'] = $_POST['fromdomain'];
		$sp['noregister'] = $_POST['noregister'];
		$sp['manualregister'] = $_POST['manualregister'];

		$sp['dialpattern'] = $_POST['dialpattern'];

		$sp['dtmfmode'] = $_POST['dtmfmode'];
		$sp['natmode'] = verify_non_default($_POST['natmode'], $defaults['sip']['natmode']);
		$sp['language'] = $_POST['language'];
		$sp['qualify'] = $_POST['qualify'];
		
		$sp['calleridsource'] = $_POST['calleridsource'];
		$sp['calleridstring'] = $_POST['calleridstring'];
		
		$sp['incomingextensionmap'] = $_POST['incomingextensionmap'];
		$sp['override'] = ($_POST['override'] != "disable") ? $_POST['override'] : false;
		$sp['overridestring'] = verify_non_default($_POST['overridestring']);

		$sp['codec'] = $pconfig['codec'];

		$sp['manual-attribute'] = array_map("base64_encode", $_POST['manualattributes']);

		if (isset($id) && $a_sipproviders[$id]) {
			$sp['uniqid'] = $a_sipproviders[$id]['uniqid'];
			$a_sipproviders[$id] = $sp;
		 } else {
			$sp['uniqid'] = "SIP-PROVIDER-" . uniqid(rand());
			$a_sipproviders[] = $sp;
		}
		
		touch($g['sip_dirty_path']);
		
		write_config();
		
		header("Location: accounts_providers.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<script type="text/JavaScript">
<!--
	<?=javascript_codec_selector("functions");?>

	jQuery(document).ready(function(){

		<?=javascript_advanced_settings("ready");?>
		<?=javascript_generate_passwd("ready");?>
		<?=javascript_codec_selector("ready");?>

	});

//-->
</script>
<?php if ($input_errors) display_input_errors($input_errors); ?>
	<form action="providers_sip_edit.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr> 
				<td width="20%" valign="top" class="vncellreq"><?=gettext("Name");?></td>
				<td width="80%" colspan="2" class="vtable">
					<input name="name" type="text" class="formfld" id="name" size="40" value="<?=htmlspecialchars($pconfig['name']);?>"> 
					<br><span class="vexpl"><?=gettext("Descriptive name for this provider.");?></span>
				</td>
			</tr>
			<? display_readback_number_field($pconfig['readbacknumber'], 2); ?>
			<? display_provider_dialpattern_editor($pconfig['dialpattern'], 2); ?>
			<tr> 
				<td valign="top" class="vncellreq"><?=gettext("Username");?></td>
				<td colspan="2" class="vtable">
					<input name="username" type="text" class="formfld" id="username" size="40" value="<?=htmlspecialchars($pconfig['username']);?>">
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell"><?=gettext("Password");?></td>
				<td colspan="2" class="vtable">
					<input name="secret" type="password" class="formfld" id="secret" size="40" value="<?=htmlspecialchars($pconfig['secret']);?>">
					<? display_passwd_generation(); ?>
					<br><span class="vexpl"><?=gettext("This account's password.");?></span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq"><?=gettext("Host");?></td>
				<td colspan="2" class="vtable">
					<input name="host" type="text" class="formfld" id="host" size="40" value="<?=htmlspecialchars($pconfig['host']);?>">
					:
					<input name="port" type="text" class="formfld" id="port" size="10" maxlength="5" value="<?=htmlspecialchars($pconfig['port']);?>"> 
					<br><span class="vexpl"><?=gettext("SIP proxy host URL or IP address and optional port.");?></span>
				</td>
			</tr>
			<? display_outgoing_callerid_options($pconfig['calleridsource'], $pconfig['calleridstring'], 2); ?>
			<? display_channel_language_selector($pconfig['language'], 2); ?>
			<? display_incoming_extension_selector(2); ?>
			<? display_audio_codec_selector($pconfig['codec']); ?>
			<? display_video_codec_selector($pconfig['codec']); ?>
			<? display_advanced_settings_begin(2); ?>
			<tr> 
				<td valign="top" class="vncell"><?=gettext("Authorization User");?></td>
				<td class="vtable">
					<input name="authuser" type="text" class="formfld" id="authuser" size="40" value="<?=htmlspecialchars($pconfig['authuser']);?>"> 
					<br><span class="vexpl"><?=gettext("Some providers require a seperate authorization username.<br>Defaults to username entered above.");?></span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell"><?=gettext("From User");?></td>
				<td class="vtable">
					<input name="fromuser" type="text" class="formfld" id="fromuser" size="40" value="<?=htmlspecialchars($pconfig['fromuser']);?>">
					<br><span class="vexpl"><?=gettext("Some providers require a seperate 'from' user.<br>Defaults to username entered above.");?></span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell"><?=gettext("From Domain");?></td>
				<td class="vtable">
					<input name="fromdomain" type="text" class="formfld" id="fromdomain" size="40" value="<?=htmlspecialchars($pconfig['fromdomain']);?>">
					<br><span class="vexpl"><?=gettext("Some providers require a seperate 'from' domain. <br>Defaults to host entered above.");?></span>
				</td>
			</tr>
			<? display_natmode_selector($pconfig['natmode'], 1); ?>
			<? display_qualify_options($pconfig['qualify'], 1); ?>
			<? display_dtmfmode_selector($pconfig['dtmfmode'], 1); ?>
			<? display_registration_options($pconfig['noregister'], $pconfig['manualregister'], 1); ?>
			<? display_incoming_callerid_override_options($pconfig['override'], $pconfig['overridestring'], 1); ?>
			<? display_manual_attributes_editor($pconfig['manual-attribute'], 1); ?>
			<? display_advanced_settings_end(); ?>
			<tr> 
				<td valign="top">&nbsp;</td>
				<td>
					<input id="submit" name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>">
					<?php if (isset($id) && $a_sipproviders[$id]): ?>
					<input name="id" type="hidden" value="<?=$id;?>"> 
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</form>
<script type="text/javascript" charset="utf-8">

	<? javascript_incoming_extension_selector($pconfig['incomingextensionmap']); ?>

</script>
<?php include("fend.inc"); ?>
