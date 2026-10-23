# X-Forwarded-For — phpBB Extension

**Version:** 1.1.1 | **License:** GPL-2.0-only

## Description

The X-Forwarded-For extension lets phpBB record the real client IP address when
the forum runs behind a reverse proxy (nginx, HAProxy, Apache, etc.) or the
Cloudflare CDN. Without this extension, phpBB logs the proxy's IP address instead
of the visitor's address. The extension reads either the `X-Forwarded-For` header
(generic proxy mode) or the `CF-Connecting-IP` header (Cloudflare mode) and
overrides the session IP accordingly.

## Security Model

phpBB trusts the IP address it receives from the web server. When a proxy sits in
front of phpBB, that address belongs to the proxy, not the end user. Proxies add
the real client IP to forwarded headers — but those headers can also be forged by
any client that connects directly to the server.

**Generic proxy mode** — The extension checks whether the connecting IP appears in
the configured trusted proxy list. Only if it does will it read the
`X-Forwarded-For` header. The header is walked right-to-left per RFC 7239 §5.2:
the rightmost entry is the one the trusted proxy appended; the first entry to the
left that does not belong to a trusted proxy is the real client IP. This prevents
spoofing because clients cannot control rightmost entries — only trusted proxies
can.

**Cloudflare mode** — Cloudflare injects the `CF-Connecting-IP` header and strips
or rewrites it before it reaches your server, so it cannot be forged by end users
when Cloudflare is the only network path to the server. When this mode is enabled
the trusted IP list is not consulted and the session IP is read solely from
`CF-Connecting-IP`.

> **Warning:** Misconfiguration can allow any visitor to set an arbitrary IP
> address in the phpBB session log. Never enable this extension unless you
> understand which IPs are truly trusted.

## Requirements

- phpBB 3.3.x
- PHP 7.1.3 or higher

## Installation

1. Copy the extension directory to `<phpBB root>/ext/bakasura/xforwardedfor`.
2. Log in to the Administration Control Panel (ACP).
3. Navigate to **Customise → Extensions** and enable **X-Forwarded-For**.

## Configuration

Navigate to **ACP → Extensions → X-Forwarded-For → Settings**.

### Cloudflare mode

Check this box if your site is served through Cloudflare. When enabled, phpBB reads
the real client IP exclusively from the `CF-Connecting-IP` header. The trusted IP
list is ignored in this mode and does not need to be filled in.

### Trusted IPs (generic proxy mode only)

A comma-separated list of proxy IP addresses or CIDR ranges that are permitted to
forward the `X-Forwarded-For` header. Only IPs listed here will trigger the IP
override; all other connecting addresses are left unchanged.

CIDR notation examples:

| Entry | Meaning |
|---|---|
| `203.0.113.42` | Single IPv4 address |
| `192.168.1.0/24` | IPv4 subnet (256 addresses) |
| `2400:cb00::/32` | IPv6 prefix |

Leave this field empty when Cloudflare mode is enabled.

## How the Headers Work

| Header | Used when |
|---|---|
| `CF-Connecting-IP` | Cloudflare mode is enabled |
| `X-Forwarded-For` | Generic proxy mode (Cloudflare mode disabled) |

`X-Forwarded-For` may contain a comma-separated chain of IP addresses accumulated
across multiple hops. The extension selects the first non-trusted IP walking from
right to left in accordance with RFC 7239, which prevents a malicious client from
injecting a fake address at the start of the chain.

## Security Warning

Do not enable this extension on a server that is directly reachable from the
internet without a trusted reverse proxy in front of it. Any client could then set
an arbitrary `X-Forwarded-For` header and forge their IP address in phpBB's session
and moderation logs.

**Empty trusted IP list is safe by default.** In generic proxy mode, if the trusted
IP list is left empty the extension will never override the session IP — no IP
forwarding will occur at all. This is the safe default; add proxy IPs to the list
only when you are certain those hosts are under your control.

**Cloudflare mode requires Cloudflare to be the sole network path to your server.**
`CF-Connecting-IP` is only trustworthy because Cloudflare injects and controls it
before the request reaches your origin. If your server is directly reachable from
the internet (e.g. its IP address is publicly known or DNS bypasses Cloudflare), any
client can send a forged `CF-Connecting-IP` header and the extension will accept it
as the real IP. Only enable Cloudflare mode when all public traffic is routed through
Cloudflare and direct access to the origin server is blocked at the network level.

## License

[GPL-2.0-only](license.txt)
