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

require_once("functions.inc");

$needs_scriptaculous = true;

$pgtitle = array("Providers", "SIP", "Edit Account");
require("guiconfig.inc");

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
	$pconfig['username'] = $a_sipproviders[$id]['username'];
	$pconfig['authuser'] = $a_sipproviders[$id]['authuser'];
	$pconfig['secret'] = $a_sipproviders[$id]['secret'];
	$pconfig['host'] = $a_sipproviders[$id]['host'];
	$pconfig['port'] = $a_sipproviders[$id]['port'];
	$pconfig['prefix'] = $a_sipproviders[$id]['prefix'];
	$pconfig['dtmfmode'] = $a_sipproviders[$id]['dtmfmode'];
	$pconfig['incomingextension'] = $a_sipproviders[$id]['incomingextension'];
	if(!is_array($pconfig['codec'] = $a_sipproviders[$id]['codec']))
		$pconfig['codec'] = array("ulaw", "gsm");
}

if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;
	$pconfig['codec'] = array("ulaw", "gsm");
	
	parse_str($_POST['a_codecs']);
	parse_str($_POST['v_codecs']);

	/* input validation */
	$reqdfields = explode(" ", "name username host prefix");
	$reqdfieldsn = explode(",", "Name,Username,Host,Prefix");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['username'] && !asterisk_is_valid_username($_POST['username']))) {
		$input_errors[] = "A valid username must be specified.";
	}
	if (($_POST['secret'] && !asterisk_is_valid_secret($_POST['secret']))) {
		$input_errors[] = "A valid secret must be specified.";
	}
/*	if (($_POST['host'] && !is_hostname($_POST['host']))) {
		$input_errors[] = "A valid host must be specified.";
	}*/
	if (($_POST['port'] && !is_port($_POST['port']))) {
		$input_errors[] = "A valid port must be specified.";
	}	
	if (!isset($id) && in_array($_POST['prefix'], asterisk_get_prefixes())) {
		$input_errors[] = "A provider with this prefix already exists.";
	} else if (!asterisk_is_valid_prefix($_POST['prefix'])) {
		$input_errors[] = "A valid prefix must be specified.";
	}
	// TODO: more checking on optional fields

	if (!$input_errors) {
		$sp = array();		
		$sp['name'] = $_POST['name'];
		$sp['username'] = $_POST['username'];
		if (isset($_POST['authuser']))
			$sp['authuser'] = $_POST['authuser'];
		$sp['secret'] = $_POST['secret'];
		$sp['host'] = $_POST['host'];
		if (isset($_POST['port']))
			$sp['port'] = $_POST['port'];
		$sp['prefix'] = $_POST['prefix'];
		$sp['dtmfmode'] = $_POST['dtmfmode'];
		$sp['incomingextension'] = $_POST['incomingextension'];
		
		$sp['codec'] = array();
		$sp['codec'] = array_merge($ace, $vce);

		if (isset($id) && $a_sipproviders[$id]) {
			$sp['uniqid'] = $a_sipproviders[$id]['uniqid'];
			$a_sipproviders[$id] = $sp;
		 } else {
			$sp['uniqid'] = "SIP-PROVIDER-" . uniqid(rand());
			$a_sipproviders[] = $sp;
		}
		
		touch($d_sipconfdirty_path);
		
		write_config();
		
		header("Location: providers_sip.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
	<form action="providers_sip_edit.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr> 
				<td width="20%" valign="top" class="vncellreq">Name</td>
				<td width="80%" colspan="2" class="vtable">
					<input name="name" type="text" class="formfld" id="name" size="40" value="<?=htmlspecialchars($pconfig['name']);?>"> 
					<br><span class="vexpl">Descriptive name of this provider.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq">Prefix</td>
				<td colspan="2" class="vtable">
					<input name="prefix" type="text" class="formfld" id="prefix" size="40" value="<?=htmlspecialchars($pconfig['prefix']);?>"> 
					<br><span class="vexpl">Dialing prefix to access this provider.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq">Username</td>
				<td colspan="2" class="vtable">
					<input name="username" type="text" class="formfld" id="username" size="40" value="<?=htmlspecialchars($pconfig['username']);?>">
				</td>
			</tr>
                <tr> 
                  <td valign="top" class="vncell">Authuser</td>
                  <td colspan="2" class="vtable">
					<input name="authuser" type="text" class="formfld" id="authuser" size="40" value="<?=htmlspecialchars($pconfig['authuser']);?>"> 
					<br><span class="vexpl">Some providers require a seperate authorization name.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell">Secret</td>
				<td colspan="2" class="vtable">
					<input name="secret" type="password" class="formfld" id="secret" size="40" value="<?=htmlspecialchars($pconfig['secret']);?>"> 
					<br><span class="vexpl">This account's password.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq">Host</td>
				<td colspan="2" class="vtable">
					<input name="host" type="text" class="formfld" id="host" size="40" value="<?=htmlspecialchars($pconfig['host']);?>">
					:
					<input name="port" type="text" class="formfld" id="port" size="20" maxlength="5" value="<?=htmlspecialchars($pconfig['port']);?>"> 
					<br><span class="vexpl">SIP proxy host URL or IP address and optional port.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell">DTMF Mode</td>
				<td colspan="2" class="vtable">
					<select name="dtmfmode" class="formfld" id="dtmfmode">
					<? foreach ($dtmfmodes as $dtmfmode) : ?>
					<option value="<?=$dtmfmode;?>" <?
					if ($pconfig['dtmfmode'] == $dtmfmode)
						echo "selected"; ?>
					><?=$dtmfmode;?></option>
					<? endforeach; ?>
					</select>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell">Incoming Extension</td>
				<td colspan="2" class="vtable">
					<select name="incomingextension" class="formfld" id="incomingextension">
					
						<option></option>
						<?php $have_extension = false; ?>
					
						<?php if (count($a_sipphones = sip_get_phones()) > 0): ?>
						<?php $have_extension = true; ?>
						<option>SIP Phones</option>
						<?php foreach ($a_sipphones as $phone): ?>
						<option value="<?=$phone['uniqid'];?>" <?php 
						if ($phone['uniqid'] == $pconfig['incomingextension']) 
							echo "selected"; ?>> 
						<? echo "&nbsp;&nbsp;{$phone['callerid']} <{$phone['extension']}>"; ?></option>
						<?php endforeach; ?>
						<?php endif; ?>
						
						<?php if (count($a_iaxphones = iax_get_phones()) > 0): ?>
						<?php $have_extension = true; ?>
						<option>IAX Phones</option>
						<?php foreach ($a_iaxphones as $phone): ?>
						<option value="<?=$phone['uniqid'];?>" <?php 
						if ($phone['uniqid'] == $pconfig['incomingextension']) 
							echo "selected"; ?>> 
						<? echo "&nbsp;&nbsp;{$phone['callerid']} <{$phone['extension']}>"; ?></option>
						<?php endforeach; ?>
						<?php endif; ?>
					
						<?php if (count($a_rooms = conferencing_get_rooms()) > 0): ?>
						<?php $have_extension = true; ?>
						<option>Conference Rooms</option>
						<?php foreach ($a_rooms as $room): ?>
						<option value="<?=$room['uniqid'];?>" <?php 
						if ($room['uniqid'] == $pconfig['incomingextension']) 
							echo "selected"; ?>> 
						<? echo "&nbsp;&nbsp;{$room['name']} <{$room['number']}>"; ?></option>
						<?php endforeach; ?>
						<?php endif; ?>
					
						<?php if (!$have_extension): ?>
						<option>no incoming extensions available</option>
						<?php endif; ?>

                    </select>
					<br><span class="vexpl">Directs incoming phone calls from this provider to the specified extension.</span>
				</td>
			</tr>
			<? asterisk_display_audio_codecs($pconfig['codec']); ?>
			<? asterisk_display_video_codecs($pconfig['codec']); ?>
			<tr> 
				<td valign="top">&nbsp;</td>
				<td colspan="2">
					<input name="Submit" type="submit" class="formbtn" value="Save" onclick="save_codec_states()">
					<input id="a_codecs" name="a_codecs" type="hidden" value="">
					<input id="v_codecs" name="v_codecs" type="hidden" value="">					 
					<?php if (isset($id) && $a_sipproviders[$id]): ?>
					<input name="id" type="hidden" value="<?=$id;?>"> 
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</form>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

	Sortable.create("ace",
		{dropOnEmpty:true,containment:["ace","acd"],constraint:false});
	Sortable.create("acd",
		{dropOnEmpty:true,containment:["ace","acd"],constraint:false});
	Sortable.create("vce",
		{dropOnEmpty:true,containment:["vce","vcd"],constraint:false});
	Sortable.create("vcd",
		{dropOnEmpty:true,containment:["vce","vcd"],constraint:false});	

	function save_codec_states() {
		var acs = document.getElementById('a_codecs');
		acs.value = Sortable.serialize('ace');
		var vcs = document.getElementById('v_codecs');
		vcs.value = Sortable.serialize('vce');
	}
// ]]>			
</script>
<?php include("fend.inc"); ?>
