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
$pgtitle = array(gettext("Accounts"), gettext("Edit IAX Phone"));


if ($_POST) {
	unset($input_errors);

	$phone = iax_verify_phone(&$_POST, &$input_errors);
	if (!$input_errors) {
		iax_save_phone($phone);
		header("Location: accounts_phones.php");
		exit;
	}
}


$colspan = 2;
$carryovers[] = "uniqid";

$uniqid = $_GET['uniqid'];
if (isset($_POST['uniqid'])) {
	$uniqid = $_POST['uniqid'];
}

if ($_POST) {
	$form = $_POST;
} else if ($uniqid) {
	$form = iax_get_phone($uniqid);
} else {
	$form = iax_generate_default_phone();
}


include("fbegin.inc");
d_start("phones_iax_edit.php");


	// General
	d_header(gettext("General Settings"));

	d_field(gettext("Number"), "extension", 20,
		gettext("The number used to dial this phone.") . " " .
		gettext("Use this number as your username."), "required");

	d_field(gettext("Caller ID"), "callerid", 40,
		gettext("Text to be displayed for Caller ID."), "required");

	display_channel_language_selector($form['language'], 2);

	display_phone_ringlength_selector($form['ringlength'], 2);

	d_field(gettext("Description"), "descr", 40,
		gettext("You may enter a description here for your reference (not parsed)."));
	d_spacer();


	// Security
	d_header(gettext("Security"));

	d_field(gettext("Password"), "secret", 20,
		gettext("This account's password."), "required");

	?><tr>
		<td valign="top" class="vncell"><?=gettext("Authentication Method");?></td>
		<td colspan="2" class="vtable">
			<select name="authentication" class="formfld" id="authentication">
				<option value="plaintext" <? 
					if ($form['authentication'] == "plaintext") 
						echo "selected"; 
					?>><?=gettext("plaintext");?></option>
				<option value="md5" <? 
					if ($form['authentication'] == "md5") 
						echo "selected"; 
					?>><?=gettext("md5");?></option>
			</select>
		</td>
	</tr><?

	display_public_access_editor($form['publicaccess'], $form['publicname'], 2);

	d_provider_access_selector($form['provider']);
	d_spacer();


	// Call Notifications & Voicemail
	d_header(gettext("Call Notifications & Voicemail"));

	d_notifications_editor($form['emailcallnotify'], $form['emailcallnotifyaddress']);

	d_voicemail_editor($form['vmtoemail'], $form['vmtoemailaddress']);
	d_spacer();


	// Codecs
	d_collapsible(gettext("Codecs"));

	display_audio_codec_selector($form['codec']);

	display_video_codec_selector($form['codec']);
	d_collapsible_end();
	d_spacer();


	// Advanced Options
	//d_collapsible(gettext("Advanced Options"));

	//display_qualify_options($form['qualify'], 2);
	//d_collapsible_end();
	//d_spacer();


d_submit();
include("fend.inc");
