<?php

/**
 * This extension automatically detects the IMAP and SMTP settings by
 * extracting them from the email address itself. For example, if the user
 * attemps to login as 'info@example.com', then the IMAP and SMTP host would
 * be set to to 'example.com'.
 *
 * Based on:
 * https://github.com/the-djmaze/snappymail/blob/master/plugins/override-smtp-credentials/index.php
 *
 */

class AutoDomainGrabPlugin extends \RainLoop\Plugins\AbstractPlugin
{
	const
		NAME     = 'Auto Domain Selection',
		VERSION  = '2.10',
		REQUIRED = '2.10.0',
		CATEGORY = 'General',
		DESCRIPTION = 'Sets the IMAP/SMTP host based on the user\'s login';

	private $imap_prefix = 'mail.';
	private $smtp_prefix = 'mail.';

	public function Init() : void
	{
		$this->addHook('smtp.before-connect', 'FilterSmtpCredentials');
		$this->addHook('imap.before-connect', 'FilterImapCredentials');
	}

	/**
	 * This function detects the IMAP Host, and if it is set to 'auto', replaces it with the MX or email domain.
	 */
	public function FilterImapCredentials(\RainLoop\Model\Account $oAccount, \MailSo\Imap\ImapClient $oImapClient, array &$aImapCredentials)
	{
		// Check for mail.$DOMAIN as entered value in RL settings
		if (!empty($aImapCredentials['Host']) && 'auto' === $aImapCredentials['Host'])
		{
			$domain = \substr(\strrchr($oAccount->Email(), '@'), 1);
			$mxhosts = array();
			if (\getmxrr($domain, $mxhosts) && $mxhosts)
			{
				$aImapCredentials['Host'] = $mxhosts[0];
			}
			else
			{
				$aImapCredentials['Host'] = $this->imap_prefix.$domain;
			}
		}
	}

	/**
	 * This function detects the SMTP Host, and if it is set to 'auto', replaces it with the MX or email domain.
	 */
	public function FilterSmtpCredentials(\RainLoop\Model\Account $oAccount, \MailSo\Smtp\SmtpClient $oSmtpClient, array &$aSmtpCredentials)
	{
		// Check for mail.$DOMAIN as entered value in RL settings
		if (!empty($aSmtpCredentials['Host']) && 'auto' === $aSmtpCredentials['Host'])
		{
			$domain = \substr(\strrchr($oAccount->Email(), '@'), 1);
			$mxhosts = array();
			if (\getmxrr($domain, $mxhosts) && $mxhosts)
			{
				$aSmtpCredentials['Host'] = $mxhosts[0];
			}
			else
			{
				$aSmtpCredentials['Host'] = $this->smtp_prefix . $domain;
			}
		}
	}
}
