<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["disable_message_class"] = True;
  $phpgw_info["flags"]["disable_send_class"] = True;
  $phpgw_info["flags"]["currentapp"] = "admin";
  include("../header.inc.php");

  $phpgw->template->set_file(array("form"	=> "application_form.tpl"));

  if ($submit) {
     $phpgw->templateotalerrors = 0;
  
     if (! $n_app_name)
        $error[$phpgw->templateotalerrors++] = lang("You must enter an application name.");
     
     if (! $n_app_title)
        $error[$phpgw->templateotalerrors++] = lang("You must enter an application title.");

     if ($old_app_name != $n_app_name) {
        $phpgw->db->query("select count(*) from applications where app_name='"
     			   	. addslashes($n_app_name) . "'");
        $phpgw->db->next_record();
     
        if ($phpgw->db->f(0) != 0) {
           $error[$phpgw->templateotalerrors++] = lang("That application name already exsists.");
        }
     }
        
     if (! $phpgw->templateotalerrors) {
        $phpgw->db->query("update applications set app_name='" . addslashes($n_app_name) . "',"
			    . "app_title='" . addslashes($n_app_title) . "', app_enabled='"
			    . "$n_app_enabled' where app_name='$old_app_name'");

        Header("Location: " . $phpgw->link("applications.php"));
        exit;
     }
  }
  $phpgw->db->query("select * from applications where app_name='$app_name'");
  $phpgw->db->next_record();

  if ($phpgw->templateotalerrors) {
     $phpgw->common->phpgw_header();
     $phpgw->common->navbar();

     $phpgw->template->set_var("error","<p><center>" . $phpgw->common->error_list($error) . "</center><br>");
  } else {
     $phpgw->template->set_var("error","");
     
     $n_app_name    = $phpgw->db->f("app_name");
     $n_app_title   = $phpgw->db->f("app_title");
     $n_app_enabled = $phpgw->db->f("app_enabled");
     $old_app_name  = $phpgw->db->f("app_name");
  }
 
  $phpgw->template->set_var("lang_header",lang("Edit application"));
  $phpgw->template->set_var("hidden_vars",'<input type="hidden" name="old_app_name" value="' . $old_app_name . '">');

  $phpgw->template->set_var("form_action",$phpgw->link("editapplication.php"));
  $phpgw->template->set_var("lang_app_name",lang("application name"));
  $phpgw->template->set_var("lang_app_title",lang("application title"));
  $phpgw->template->set_var("lang_enabled",lang("enabled"));
  $phpgw->template->set_var("lang_submit_button",lang("edit"));

  $phpgw->template->set_var("app_name_value",$n_app_name);
  $phpgw->template->set_var("app_title_value",$n_app_title);
  $phpgw->template->set_var("app_enabled_checked",($n_app_enabled?" checked":""));

  $phpgw->template->pparse("out","form");

  $phpgw->common->phpgw_footer();
?>
