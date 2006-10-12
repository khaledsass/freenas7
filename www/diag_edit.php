#!/usr/local/bin/php
<?php
/*
    diag_edit.php
    Copyright (C) 2004, 2005 Scott Ullrich
    All rights reserved.

    Adapted for FreeNAS by Volker Theile (votdev@gmx.de)
    Copyright � 2006 Volker Theile

    Using dp.SyntaxHighlighter for syntax highlighting
    http://www.dreamprojections.com/SyntaxHighlighter 
    Copyright � 2004-2006 Alex Gorbatchev. All rights reserved.

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

global $g;

$pgtitle = array(_DIAG_NAME, _DIAGEDITPHP_NAMEDESC);

if (($_POST['submit'] == "Load") && file_exists($_POST['savetopath']) && is_file($_POST['savetopath'])) {
	$fd = fopen($_POST['savetopath'], "r");
	$content = fread($fd, filesize($_POST['savetopath']));
	fclose($fd);
	$edit_area="";
	if(stristr($_POST['savetopath'], ".php") == true)
		$language = "php";
	else if(stristr($_POST['savetopath'], ".inc") == true)
		$language = "php";
	else if(stristr($_POST['savetopath'], ".sh") == true)
		$language = "core";
	else if(stristr($_POST['savetopath'], ".xml") == true)
		$language = "xml";
	else if(stristr($_POST['savetopath'], ".js") == true)
		$language = "js";
	else if(stristr($_POST['savetopath'], ".css") == true)
		$language = "css";
} else if (($_POST['submit'] == "Save")) {
	conf_mount_rw();
	$content = ereg_replace("\r","",$_POST['code']) ;
	$fd = fopen($_POST['savetopath'], "w");
	fwrite($fd, $content);
	fclose($fd);
	$edit_area="";
	$savemsg = _DIAGEDITPHP_SAVEMSG . " " . $_POST['savetopath'];
	if($_POST['savetopath'] == "{$g['cf_conf_path']}/config.xml")
		unlink_if_exists("/tmp/config.cache");
	conf_mount_ro();
} else if (($_POST['submit'] == "Load") && (!file_exists($_POST['savetopath']) || !is_file($_POST['savetopath']))) {
	$savemsg = _DIAGEDITPHP_FILENOTFOUND . " " . $_POST['savetopath'];
	$content = "";
	$_POST['savetopath'] = "";
}

if($_POST['highlight'] <> "") {
	if($_POST['highlight'] == "yes" or
	  $_POST['highlight'] == "enabled") {
		$highlight = "yes";
	} else {
		$highlight = "no";
	}
} else {
	$highlight = "no";
}

if($_POST['rows'] <> "")
	$rows = $_POST['rows'];
else
	$rows = 30;

if($_POST['cols'] <> "")
	$cols = $_POST['cols'];
else
	$cols = 66;
?>

<?php include("fbegin.inc"); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<form action="diag_edit.php" method="post">
  <div id="inputerrors"></div>
  <div id="shapeme">
    <table width="100%" cellpadding='9' cellspacing='9' bgcolor='#eeeeee'>
      <tr>
        <td>
          <?=_DIAGEDITPHP_FILEPATH; ?>:
	        <input size="42" id="savetopath" name="savetopath" value="<?php echo $_POST['savetopath']; ?>" />
          <input name="browse" type="button" class="button" id="Browse" onClick='ifield = form.savetopath; filechooser = window.open("filechooser.php?p="+escape(ifield.value), "filechooser", "toolbar=no,menubar=no,statusbar=no,width=500,height=300"); filechooser.ifield = ifield; window.ifield = ifield;' value="..." \> 
	        <input name="submit" type="submit" class="button" id="Load" value="<?=_LOAD;?>" /> 
          <input name="submit" type="submit" class="button" id="Save" value="<?=_SAVE;?>" />
	        <hr noshade="noshade" />
        	<?php if($_POST['highlight'] == "no"): ?>
          <?=_DIAGEDITPHP_ROWS; ?>: <input size="3" name="rows" value="<? echo $rows; ?>" />
        	<?=_DIAGEDITPHP_COLS; ?>: <input size="3" name="cols" value="<? echo $cols; ?>" />
        	|
        	<?php endif; ?>
        	<?=_DIAGEDITPHP_HIGHLIGHTING; ?>:
          <input id="highlighting_enabled" name="highlight" type="radio" value="yes" <?php if($highlight == "yes") echo " checked=\"checked\""; ?> />
          <label for="highlighting_enabled"><?=_DIAGEDITPHP_HIGHLIGHTINGENABLED; ?></label>
        	<input id="highlighting_disabled" name="highlight" type="radio" value="no"<?php if($highlight == "no") echo " checked=\"checked\""; ?> />
          <label for="highlighting_disabled"><?=_DIAGEDITPHP_HIGHLIGHTINGDISABLED; ?></label>
        </td>
      </tr>
    </table>
  </div>
  <br/>
  <table width='100%'>
    <tr>
      <td valign="top" class="label">
	      <div style="background: #eeeeee;" id="textareaitem">
          <!-- NOTE: The opening *and* the closing textarea tag must be on the same line. -->
	        <textarea style="width: 98%; margin: 7px;" class="<?php echo $language; ?>:showcolumns" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" name="code"><?php echo htmlentities($content); ?></textarea>
	      </div>
      </td>
    </tr>
  </table>
</form>
<script class="javascript" src="syntaxhighlighter/shCore.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushCSharp.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushPhp.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushJScript.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushJava.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushVb.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushSql.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushXml.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushDelphi.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushPython.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushRuby.js"></script>
<script class="javascript" src="syntaxhighlighter/shBrushCss.js"></script>
<script class="javascript">
<!--
  // Set focus.
  document.forms[0].savetopath.focus();
  
  // Append css for syntax highlighter.
  var head = document.getElementsByTagName("head")[0];
  var linkObj = document.createElement("link");
  linkObj.setAttribute("type","text/css");
  linkObj.setAttribute("rel","stylesheet");
  linkObj.setAttribute("href","syntaxhighlighter/SyntaxHighlighter.css");
  head.appendChild(linkObj);

  // Activate dp.SyntaxHighlighter?
  <?php
  if($_POST['highlight'] == "yes") {
    echo "dp.SyntaxHighlighter.HighlightAll('code', true, true);\n";
    // Disable 'Save' button.
    echo "document.forms[0].Save.disabled = 1;\n";
  }
?>
//-->
</script>
<?php include("fend.inc"); ?>
