<?php
  /**************************************************************************\
  * phpGroupWare - Addressbook                                               *
  * http://www.phpgroupware.org                                              *
  * Written by Miles Lott <milosch@phpgroupware.org>                         *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'addressbook';
	include('../header.inc.php');

	if(!$GLOBALS['phpgw']->acl->check('run',1,'admin'))
	{
		echo lang('access not permitted');
		$GLOBALS['phpgw']->common->phpgw_footer();
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$field = $HTTP_POST_VARS['field'];
	$field_name = $HTTP_POST_VARS['field_name'];
	$start = $HTTP_POST_VARS['start'];
	$query = $HTTP_POST_VARS['query'];
	$sort  = $HTTP_POST_VARS['sort'];

	$GLOBALS['phpgw']->template->set_file(array('form' => 'field_form.tpl'));
	$GLOBALS['phpgw']->template->set_block('form','add','addhandle');
	$GLOBALS['phpgw']->template->set_block('form','edit','edithandle');

	if ($HTTP_POST_VARS['submit'])
	{
		$errorcount = 0;

		if (!$field_name)
		{
			$error[$errorcount++] = lang('Please enter a name for that field !');
		}

		$fields = read_custom_fields($start,$limit,$field_name);
		if ($fields[0]['name'])
		{
			$error[$errorcount++] = lang('That field name has been used already !');
		}

		if (! $error)
		{
			$field_name = addslashes($field_name);
			save_custom_field($field,$field_name);
		}
	}

	if ($errorcount) { $GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error)); }
	if (($submit) && (! $error) && (! $errorcount)) { $GLOBALS['phpgw']->template->set_var('message',lang('Field x has been added !', $field_name)); }
	if ((! $submit) && (! $error) && (! $errorcount)) { $GLOBALS['phpgw']->template->set_var('message',''); }

	$GLOBALS['phpgw']->template->set_var('title_fields',lang('Add'). ' ' . lang('Custom Field'));
	$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/addressbook/addfield.php'));
	$GLOBALS['phpgw']->template->set_var('doneurl',$GLOBALS['phpgw']->link('/addressbook/fields.php'));
	$GLOBALS['phpgw']->template->set_var('hidden_vars','<input type="hidden" name="field" value="' . $field . '">');

	$GLOBALS['phpgw']->template->set_var('lang_name',lang('Field name'));

	$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
	$GLOBALS['phpgw']->template->set_var('lang_reset',lang('Clear Form'));
	$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));

	$GLOBALS['phpgw']->template->set_var('field_name',$field_name);

	$GLOBALS['phpgw']->template->set_var('edithandle','');
	$GLOBALS['phpgw']->template->set_var('addhandle','');
	$GLOBALS['phpgw']->template->pparse('out','form');
	$GLOBALS['phpgw']->template->pparse('addhandle','add');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
