#!/usr/bin/php
<?php 
/*
	$Id$
	part of AskoziaPBX (http://askozia.com/pbx)
	
	Copyright (C) 2007-2010 tecema (a.k.a IKT) <http://www.tecema.de>.
	All rights reserved.
	
	Askozia®PBX is a registered trademark of tecema. Any unauthorized use of
	this trademark is prohibited by law and international treaties.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	
	3. Redistribution in any form at a charge, that in whole or in part
	   contains or is derived from the software, including but not limited to
	   value added products, is prohibited without prior written consent of
	   tecema.
	
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

$pgtitle = array(gettext("Accounts"), gettext("Edit Analog Provider"));


if ($_POST) {
	unset($input_errors);

	$provider = analog_verify_provider(&$_POST, &$input_errors);
	if (!$input_errors) {
		analog_save_provider($provider);
		header("Location: accounts_providers.php");
		exit;
	}
}


$colspan = 1;
$carryovers[] = "uniqid";

$uniqid = $_GET['uniqid'];
if (isset($_POST['uniqid'])) {
	$uniqid = $_POST['uniqid'];
}

if ($_POST) {
	$form = $_POST;
} else if ($uniqid) {
	$form = analog_get_provider($uniqid);
} else {
	$form = analog_generate_default_provider();
}

include("fbegin.inc");
d_start("providers_analog_edit.php");

	// General
	d_header(gettext("General Settings"));
	d_field(gettext("Name"), "name", 40,
		gettext("Descriptive name for this provider."), "required");
	?><tr>
		<td width="20%" valign="top" class="vncellreq"><?=gettext("Number");?></td>
		<td width="80%" colspan="1" class="vtable">
			<input name="number" type="text" class="formfld" id="number" size="40" value="<?=htmlspecialchars($form['number']);?>">
			<br><span class="vexpl"><?=gettext("Telephone number assigned to this line.");?></span>
		</td>
	</tr><?
	display_channel_language_selector($form['language'], 1);
	d_hwport_selector($form['port'], "analog", "fxo");
	d_spacer();


	// Call Routing
	d_header(gettext("Call Routing"));
	display_provider_dialpattern_editor($form['dialpattern'], 1);
	display_incoming_extension_selector(1);
	d_incoming_fax_editor();
	d_failover_provider($form['failover']);
	d_spacer();


	// Caller ID Options
	d_collapsible(gettext("Caller ID Options"));
	display_outgoing_callerid_options($form['calleridsource'], $form['calleridstring'], 1);
	display_incoming_callerid_override_options($form['override'], $form['overridestring'], 1);
	d_collapsible_end();
	d_spacer();


	// Advanced Options
	d_collapsible(gettext("Advanced Options"));
	d_manualattributes_editor($form['manualattributes']);
	d_collapsible_end();
	d_spacer();


d_submit();

?><script type="text/javascript" charset="utf-8"><?
	javascript_incoming_extension_selector($form['incomingextensionmap']);
?></script><?

include("fend.inc");
