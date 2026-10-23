<?php

namespace bakasura\xforwardedfor\acp;

/**
 * ACP module info class for the X-Forwarded-For extension.
 *
 * Returns the metadata array that tells the phpBB ACP framework how to
 * register this extension's administration module (title, filename, modes,
 * and capability requirements).
 */
class x_forwarded_for_info
{
	/**
	 * Returns the ACP module definition array.
	 *
	 * @return array ACP module metadata consumed by the phpBB ACP framework.
	 */
	public function module()
	{
		return [
			'filename' => '\bakasura\xforwardedfor\acp\x_forwarded_for_module',
			'title' => 'XFF_ACP_TITLE',
			'modes' => [
				'settings' => [
					'title' => 'XFF_ACP',
					'auth' => 'ext_bakasura/xforwardedfor && acl_a_board',
					'cat' => ['XFF_ACP_TITLE'],
				],
			],
		];
	}
}
