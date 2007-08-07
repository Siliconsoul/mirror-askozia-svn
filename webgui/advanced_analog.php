#!/usr/local/bin/php
<?php 
/*
	$Id: advanced_iax.php 143 2007-07-03 14:34:07Z michael.iedema $
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
	
	THIS SOFTWARE IS PROVIDED ''AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
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

$needs_scriptaculous = true;

$pgtitle = array("Advanced", "Analog");
require("guiconfig.inc");

$analogconfig = &$config['services']['analog'];

if (!is_array($analogconfig['loadzone'])) {
	$pconfig['loadzone'][] = "us";
} else {
	$pconfig['loadzone'] = $analogconfig['loadzone'];
}


if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;
	
	parse_str($_POST['loadzones']);
	$pconfig['loadzone'] = $gme;
	if (!is_array($pconfig['loadzone'])) {
		$pconfig['loadzone'][] = "us";
	}

	/* input validation *//*
	$reqdfields = explode(" ", "nationalprefix internationalprefix");
	$reqdfieldsn = explode(",", "National Prefix, International Prefix");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);


	// is valid nationalprefix
	if ($_POST['nationalprefix'] && !is_numericint($_POST['nationalprefix'])) {
		$input_errors[] = "A valid national prefix must be specified.";
	}
	// is valid internationalprefix
	if ($_POST['internationalprefix'] && !is_numericint($_POST['internationalprefix'])) {
		$input_errors[] = "A valid international prefix must be specified.";
	}*/

	if (!$input_errors) {
		$analogconfig['loadzone'] = $pconfig['loadzone'];
		
		write_config();
		touch($d_analogconfdirty_path);
		header("Location: advanced_analog.php");
		exit;
	}
}

if (file_exists($d_analogconfdirty_path)) {
	$retval = 0;
	if (!file_exists($d_sysrebootreqd_path)) {
		config_lock();
		$retval |= analog_conf_generate();
		$retval |= analog_configure();
		config_unlock();
		
		$retval |= asterisk_configure();
	}

	$savemsg = get_std_save_message($retval);
	if ($retval == 0) {
		unlink($d_analogconfdirty_path);
	}
}

?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<form action="advanced_analog.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr> 
			<td width="20%" valign="top" class="vncell">Tone Zones</td>
			<td width="40%" class="vtable" valign="top"><strong>Loaded</strong>&nbsp;<i>(drag-and-drop)</i>
				<ul id="gme" class="gme" style="min-height:50px"><? 

				foreach ($pconfig['loadzone'] as $loadzone) {
					if (array_key_exists($loadzone, $zaptel_loadzones)) {
						?><li class="gme" id="gme_<?=$loadzone;?>"><?=htmlspecialchars($zaptel_loadzones[$loadzone]);?></li><?
					}
				}

				?></ul>
			</td>
			<td width="40%" class="vtable" valign="top"><strong>Inactive</strong>
				<ul id="gmd" class="gmd" style="min-height:50px"><?

				foreach ($zaptel_loadzones as $abbreviation=>$friendly) {
					if (!in_array($abbreviation, $pconfig['loadzone'])) {
						?><li class="gmd" id="gmd_<?=$abbreviation;?>"><?=htmlspecialchars($zaptel_loadzones[$abbreviation]);?></li><?
					}
				}

				?></ul>
			</td>
		</tr>
		<tr>
			<td width="20%" valign="top" class="vncell">&nbsp;</td>
			<td width="40%" class="vtable" valign="top" colspan="2">
				<span class="vexpl"><strong><span class="red">Note:</span></strong>
				Select all indication tone zones the analog hardware should support. The first tone zone will be the default tonezone.</span>
			</td>
		</tr>
		<tr> 
			<td valign="top">&nbsp;</td>
			<td colspan="2">
				<input name="Submit" type="submit" class="formbtn" value="Save" onclick="save_loadzone_states()">
				<input id="loadzones" name="loadzones" type="hidden" value="">
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript" charset="utf-8">
// <![CDATA[

Sortable.create("gme",
	{dropOnEmpty:true,containment:["gme","gmd"],constraint:false});
Sortable.create("gmd",
	{dropOnEmpty:true,containment:["gme","gmd"],constraint:false});

function save_loadzone_states() {
	var gms = document.getElementById('loadzones');
	gms.value = Sortable.serialize('gme');
}
// ]]>			
</script>
<?php include("fend.inc"); ?>
