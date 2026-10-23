<?php

namespace bakasura\xforwardedfor\migrations;

class x_forwarded_for_acp extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * If our config variable already exists in the db
	 * skip this migration.
	 *
	 * config_text->get() returns null only when the key is absent from the
	 * database; an empty string ('') means the key exists with no value set,
	 * which also counts as installed.
	 */
	public function effectively_installed()
	{
		$config_text = $this->container->get('config_text');

		return $config_text->get('xff_trusted_ips') !== null;
	}

	/**
	 * This migration depends on phpBB's v314 migration
	 * already being installed.
	 */
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v314'];
	}

	public function update_data()
	{
		return [
			// Add the config variables for trusted proxy IPs and Cloudflare mode.
			['config_text.add', ['xff_trusted_ips', '']],
			['config_text.add', ['xff_cloudflare_mode', '0']],

			// Add a parent module (XFF_ACP_TITLE) to the Extensions tab (ACP_CAT_DOT_MODS)
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'XFF_ACP_TITLE',
			]],

			// Add our main_module to the parent module (XFF_ACP_TITLE)
			['module.add', [
				'acp',
				'XFF_ACP_TITLE',
				[
					'module_basename' => '\bakasura\xforwardedfor\acp\x_forwarded_for_module',
					'modes' => ['settings'],
				],
			]],
		];
	}

	public function revert_data()
	{
		return [
			// Remove the child module before the parent.
			['module.remove', [
				'acp',
				'XFF_ACP_TITLE',
				[
					'module_basename' => '\bakasura\xforwardedfor\acp\x_forwarded_for_module',
					'modes' => ['settings'],
				],
			]],

			// Remove the parent module.
			['module.remove', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'XFF_ACP_TITLE',
			]],

			// Remove the config_text entries.
			['config_text.remove', ['xff_trusted_ips']],
			['config_text.remove', ['xff_cloudflare_mode']],
		];
	}
}
