<?php

namespace bakasura\xforwardedfor\acp;

/**
 * ACP module for the X-Forwarded-For extension.
 *
 * Provides the administration settings page where an administrator can
 * enable Cloudflare mode and/or configure the list of trusted proxy IPs.
 */
class x_forwarded_for_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/**
	 * Main entry point called by the ACP framework.
	 *
	 * Handles both the display and the POST submission of the settings form.
	 * Reads/writes xff_trusted_ips and xff_cloudflare_mode via config_text.
	 *
	 * @param int    $id   ACP module ID (passed by the ACP framework, unused here).
	 * @param string $mode ACP module mode (e.g. 'settings').
	 * @return void
	 */
	public function main($id, $mode)
	{
		global $language, $template, $request, $phpbb_container;

		/* @var $config_text \phpbb\config\db_text */
		$config_text = $phpbb_container->get('config_text');

		$this->tpl_name = 'acp_x_forwarded_for';
		$this->page_title = $language->lang('XFF_ACP_TITLE');

		add_form_key('xff_settings');

		if ($request->is_set_post('submit')) {
			if (!check_form_key('xff_settings')) {
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config_text->set('xff_trusted_ips', $request->variable('xff_trusted_ips', ''));
			$config_text->set('xff_cloudflare_mode', $request->variable('xff_cloudflare_mode', '0'));
			trigger_error($language->lang('XFF_ACP_SETTING_SAVED') . adm_back_link($this->u_action), E_USER_NOTICE);
		}

		$template->assign_vars([
			'XFF_TRUSTED_IPS'      => $config_text->get('xff_trusted_ips'),
			'XFF_CLOUDFLARE_MODE'  => (bool) $config_text->get('xff_cloudflare_mode'),
			'U_ACTION'             => $this->u_action,
		]);
	}
}
