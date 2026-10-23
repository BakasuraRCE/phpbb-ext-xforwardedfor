<?php

namespace bakasura\xforwardedfor\event;

use phpbb\config\db_text;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var db_text $config_text */
	protected $config_text;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request $request     Request object
	 * @param db_text                $config_text Configuration text storage
	 * @access public
	 */
	public function __construct(\phpbb\request\request $request, $config_text)
	{
		$this->request = $request;
		$this->config_text = $config_text;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.session_ip_after' => 'core_session_ip_after',
		);
	}

	/**
	 * Check whether an IP address falls within a CIDR range.
	 *
	 * Both the IP and the CIDR must be of the same address family (IPv4 or
	 * IPv6). Mixed-family comparisons always return false.
	 *
	 * @param string $ip   The IP address to test.
	 * @param string $cidr The CIDR range (e.g. "192.168.1.0/24" or "2400:cb00::/32").
	 * @return bool True if $ip is within $cidr, false otherwise.
	 */
	public static function ip_in_range($ip, $cidr)
	{
		// ensure both IPv6
		if (strpos($ip, ':') !== false && strpos($cidr, ':') !== false) {
			return IpUtils::checkIp($ip, $cidr);
		}

		// ensure both IPv4
		if (strpos($ip, ':') === false && strpos($cidr, ':') === false) {
			return IpUtils::checkIp($ip, $cidr);
		}

		return false;
	}

	/**
	 * Determine whether an IP address is in the configured trusted proxy list.
	 *
	 * Reads xff_trusted_ips from config_text, splits the value on commas,
	 * trims whitespace from each entry, and tests the given IP against each
	 * entry — first as a plain address, then as a CIDR range when the entry
	 * contains a slash.
	 *
	 * @param string $ip The IP address to check.
	 * @return bool True if $ip matches any trusted entry, false otherwise.
	 */
	private function is_trusted_ip($ip)
	{
		$trusted_ips = $this->config_text->get('xff_trusted_ips');

		if (empty($trusted_ips)) {
			return false;
		}

		$trusted_ips = array_map('trim', explode(',', $trusted_ips));

		// check by single IP
		if (in_array($ip, $trusted_ips)) {
			return true;
		}

		// check by IP range
		foreach ($trusted_ips as $trusted_ip) {
			if (strpos($trusted_ip, '/') !== false) {
				if (self::ip_in_range($ip, $trusted_ip)) {
					return true;
				}
			}
		}

		// no match
		return false;
	}

	/**
	 * Override the session IP using the X-Forwarded-For or CF-Connecting-IP
	 * header when the connecting IP belongs to a trusted proxy.
	 *
	 * Trust model:
	 *  - Cloudflare mode (xff_cloudflare_mode = 1): the real client IP is read
	 *    exclusively from the CF-Connecting-IP header, which Cloudflare sets and
	 *    signs. The trusted IP list is not consulted in this mode.
	 *  - Generic proxy mode (xff_cloudflare_mode = 0): the connecting IP must
	 *    appear in the trusted proxy list. The X-Forwarded-For header is then
	 *    walked right-to-left per RFC 7239 §5.2 to find the first non-trusted IP,
	 *    which is the real client IP. Walking right-to-left prevents spoofing
	 *    because leftmost entries are client-controlled.
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc7239
	 *
	 * @param object $event The event object
	 * @return void
	 * @access public
	 */
	public function core_session_ip_after($event)
	{
		$ip = $event['ip'];

		$cloudflare_mode = (int) $this->config_text->get('xff_cloudflare_mode');

		if ($cloudflare_mode) {
			// Cloudflare mode: read the real client IP exclusively from the
			// CF-Connecting-IP header; the trusted IP list is not needed.
			$real_ip = trim($this->request->header('Cf-Connecting-Ip'));

			if (empty($real_ip) || !filter_var($real_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return;
			}

			$event['ip'] = $real_ip;
			return;
		}

		// Generic proxy mode (RFC 7239)
		// Exit if the connecting IP is not in the trusted list
		if (!$this->is_trusted_ip($ip)) {
			return;
		}

		$forwarded_for_raw = trim($this->request->header('X-Forwarded-For'));
		if (empty($forwarded_for_raw)) {
			return;
		}

		// Walk the XFF list right-to-left (RFC 7239 §5.2): the rightmost entry
		// was added by the most recently traversed proxy (which we trust because
		// it is in our trusted list). Keep walking left until we reach an IP that
		// is NOT trusted — that is the real client IP.
		$ips = array_reverse(array_map('trim', explode(',', $forwarded_for_raw)));
		$real_ip = null;
		foreach ($ips as $candidate) {
			if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
				&& !$this->is_trusted_ip($candidate)) {
				$real_ip = $candidate;
				break;
			}
		}

		if ($real_ip === null) {
			return;
		}

		$event['ip'] = $real_ip;
	}
}
