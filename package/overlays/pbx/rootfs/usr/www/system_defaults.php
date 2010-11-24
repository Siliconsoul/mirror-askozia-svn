#!/usr/bin/php
<?php 
/*
	$Id$
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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

$pgtitle = array(gettext("System"), gettext("Factory Defaults"));

$default_password = "askozia";
if (file_exists("{$g['etc_path']}/brand.password")) {
	$default_password = chop(file_get_contents("{$g['etc_path']}/brand.password"));
}

if ($_POST) {
	if ($_POST['Yes']) {
		reset_factory_defaults();
		system_reboot();
		$rebootmsg = gettext("The system has been reset to factory defaults and is now rebooting. This may take a minute.");
	} else {
		header("Location: index.php");
		exit;
	}
}

include("fbegin.inc");

if ($rebootmsg) {
	echo display_info_box($rebootmsg, "keep");
} else {

	?><form action="system_defaults.php" method="post">
		<p><strong><?=sprintf(gettext("If you click &quot;Yes&quot;, the PBX will be reset to factory defaults and will reboot immediately. The entire system configuration will be overwritten. The LAN IP address will be reset to use DHCP and the password will be set to '%s'."), $default_password);?><br>
		<br>
		<?=gettext("Are you sure you want to proceed?");?></strong></p>
		<p><input name="Yes" type="submit" class="formbtn" value=" <?=gettext("Yes");?> ">
		<input name="No" type="submit" class="formbtn" value=" <?=gettext("No");?> "></p>
	</form><?

}

include("fend.inc");
