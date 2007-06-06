#!/usr/local/bin/php
<?php 
/*
	$Id: phones_sip_edit.php 117 2007-06-04 12:13:10Z michael.iedema $
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

$pgtitle = array("Phones", "IAX", "Edit Account");
require("guiconfig.inc");

/* grab and sort the iax phones in our config */
if (!is_array($config['iax']['phone']))
	$config['iax']['phone'] = array();


iax_sort_phones();
$a_iaxphones = &$config['iax']['phone'];
$a_providers = asterisk_get_providers();

$pconfig['codec'] = array("ulaw");

$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

/* pull current config into pconfig */
if (isset($id) && $a_iaxphones[$id]) {
	$pconfig['extension'] = $a_iaxphones[$id]['extension'];
	$pconfig['callerid'] = $a_iaxphones[$id]['callerid'];
	$pconfig['secret'] = $a_iaxphones[$id]['secret'];
	$pconfig['provider'] = $a_iaxphones[$id]['provider'];
	$pconfig['voicemailbox'] = $a_iaxphones[$id]['voicemailbox'];
	if(!is_array($pconfig['codec'] = $a_iaxphones[$id]['codec']))
		$pconfig['codec'] = array("ulaw");
	$pconfig['descr'] = $a_iaxphones[$id]['descr'];
}

if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;
	$pconfig['codec'] = array("ulaw");
	
	parse_str($_POST['a_codecs']);
	parse_str($_POST['v_codecs']);

	/* input validation */
	$reqdfields = explode(" ", "extension callerid secret");
	$reqdfieldsn = explode(",", "Extension,Caller ID,Secret");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['extension'] && !asterisk_is_valid_extension($_POST['extension']))) {
		$input_errors[] = "An extension must be a one to four digit number.";
	}
	if (($_POST['callerid'] && !asterisk_is_valid_callerid($_POST['callerid']))) {
		$input_errors[] = "A valid Caller ID must be specified.";
	}
	if (($_POST['secret'] && !asterisk_is_valid_secret($_POST['secret']))) {
		$input_errors[] = "A valid secret must be specified.";
	}
	if (!isset($id) && in_array($_POST['extension'], asterisk_get_extensions())) {
		$input_errors[] = "A phone with this extension already exists.";
	}
	if (($_POST['port'] && !is_port($_POST['port']))) {
		$input_errors[] = "A valid port must be specified.";
	}
	if (($_POST['voicemailbox'] && !is_email_address($_POST['voicemailbox']))) {
		$input_errors[] = "A valid e-mail address must be specified.";
	}
	
	//TODO: check if voicemailbox is valid)


	if (!$input_errors) {
		$sp = array();
		$sp['extension'] = $_POST['extension'];
		$sp['callerid'] = $_POST['callerid'];
		$sp['secret'] = $_POST['secret'];
		$sp['voicemailbox'] = $_POST['voicemailbox'];
		$sp['descr'] = $_POST['descr'];

		$sp['provider'] = array();
		foreach ($a_providers as $provider)
			if($_POST[$provider['uniqid']] == true)
				$sp['provider'][] = $provider['uniqid'];
		
		$sp['codec'] = array();
		$sp['codec'] = array_merge($ace, $vce);

		if (isset($id) && $a_iaxphones[$id]) {
			$sp['uniqid'] = $a_iaxphones[$id]['uniqid'];
			$a_iaxphones[$id] = $sp;
		 } else {
			$sp['uniqid'] = "IAX-PHONE-" . uniqid(rand());
			$a_iaxphones[] = $sp;
		}
		
		touch($d_iaxconfdirty_path);
		
		write_config();
		
		header("Location: phones_iax.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
	<form action="phones_iax_edit.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<tr> 
				<td width="20%" valign="top" class="vncellreq">Extension</td>
				<td width="80%" colspan="2" class="vtable">
					<input name="extension" type="text" class="formfld" id="extension" size="20" maxlength="4" value="<?=htmlspecialchars($pconfig['extension']);?>"> 
					<br><span class="vexpl">Up to four digits. This is also this account's username.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq">Caller ID</td>
				<td colspan="2" class="vtable">
					<input name="callerid" type="text" class="formfld" id="callerid" size="40" value="<?=htmlspecialchars($pconfig['callerid']);?>"> 
					<br><span class="vexpl">Text to be displayed for Caller ID.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncellreq">Secret</td>
				<td colspan="2" class="vtable">
					<input name="secret" type="password" class="formfld" id="secret" size="40" value="<?=htmlspecialchars($pconfig['secret']);?>"> 
                    <br><span class="vexpl">This account's password.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell">Voicemail Address</td>
				<td colspan="2" class="vtable">
					<input name="voicemailbox" type="text" class="formfld" id="voicemailbox" size="40" value="<?=htmlspecialchars($pconfig['voicemailbox']);?>"> 
					<br><span class="vexpl">An e-mail address. If entered, voicemail will be enabled on this extension. Incoming messages will be sent to the given address.</span>
				</td>
			</tr>
			<tr> 
				<td valign="top" class="vncell">Providers</td>
				<td colspan="2" class="vtable">
				<? foreach ($a_providers as $provider): ?>
					<input name="<?=$provider['uniqid']?>" id="<?=$provider['uniqid']?>" type="checkbox" value="yes" <?php if (in_array($provider['uniqid'], $pconfig['provider'])) echo "checked"; ?>><?=$provider['name']?><br>
				<? endforeach; ?>
				&nbsp;
				</td>
			</tr>
			<tr> 
				<td width="20%" valign="top" class="vncell">Audio Codecs</td>
				<td width="40%" class="vtable" valign="top"><strong>Enabled</strong>
					<ul id="ace" class="ace" style="min-height:50px">
					<? foreach ($pconfig['codec'] as $codec): ?>
						<? if (array_key_exists($codec, $audio_codecs)): ?>
						<li class="ace" id="ace_<?=$codec;?>"><?=$audio_codecs[$codec];?></li>
						<? endif; ?>
					<? endforeach; ?>
					</ul>
				</td>
				<td width="40%" class="vtable" valign="top"><strong>Disabled</strong>
					<ul id="acd" class="acd" style="min-height:50px">
					<? foreach ($audio_codecs as $codec=>$friendly): ?>
						<? if (!in_array($codec, $pconfig['codec'])): ?>
						<li class="acd" id="acd_<?=$codec;?>"><?=$friendly;?></li>
						<? endif; ?>
					<? endforeach; ?>
					</ul>
				</td>
			</tr>
			<tr> 
				<td width="20%" valign="top" class="vncell">Video Codecs</td>
				<td width="40%" class="vtable" valign="top"><strong>Enabled</strong>
					<ul id="vce" class="vce" style="min-height:50px">
						<? foreach ($pconfig['codec'] as $codec): ?>
							<? if (array_key_exists($codec, $video_codecs)): ?>
							<li class="vce" id="vce_<?=$codec;?>"><?=$video_codecs[$codec];?></li>
							<? endif; ?>
						<? endforeach; ?>
					</ul>
				</td>
				<td width="40%" class="vtable" valign="top"><strong>Disabled</strong>
					<ul id="vcd" class="vcd" style="min-height:50px">
					<? foreach ($video_codecs as $codec=>$friendly): ?>
						<? if (!in_array($codec, $pconfig['codec'])): ?>
						<li class="vcd" id="vcd_<?=$codec;?>"><?=$friendly;?></li>
						<? endif; ?>
					<? endforeach; ?>
					</ul>
				</td>
			</tr>			
			<tr> 
				<td valign="top" class="vncell">Description</td>
				<td colspan="2" class="vtable">
					<input name="descr" type="text" class="formfld" id="descr" size="40" value="<?=htmlspecialchars($pconfig['descr']);?>"> 
					<br><span class="vexpl">You may enter a description here 
					for your reference (not parsed).</span>
				</td>
			</tr>
			<tr> 
				<td valign="top">&nbsp;</td>
				<td colspan="2">
					<input name="Submit" type="submit" class="formbtn" value="Save" onclick="save_codec_states()">
					<input id="a_codecs" name="a_codecs" type="hidden" value="">
					<input id="v_codecs" name="v_codecs" type="hidden" value="">
					<?php if (isset($id) && $a_iaxphones[$id]): ?>
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
