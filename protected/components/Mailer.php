<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class Mailer extends CComponent
{
	// can be mail, smtp, or sendmail
	public $mode = '';

	public $sendmail_command = '/usr/sbin/sendmail -bs';

	// configuration for smtp
	public $host;
	public $port = 25;
	// ssl or tls
	public $security;
	public $username;
	public $password;

	protected $_transport;

	protected $_mailer;

	/**
	 * initialise the component by pulling in the appropriate SwiftMailer classes
	 */
	public function init()
	{
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		require_once(Yii::getPathOfAlias('application.vendors.SwiftMailer') . '/swift_required.php');
		spl_autoload_register(array('YiiBase', 'autoload'));
	}

	/**
	 * return the transport object for the configured mail type
	 *
	 * @throws Exception
	 * @return Transport object
	 */
	protected function getTransport()
	{
		if (!$this->_transport) {
			if ($this->mode == 'sendmail') {
				$this->_transport = Swift_SendmailTransport::newInstance($this->sendmail_command);
			} elseif ($this->mode == 'smtp') {
				$this->_transport = Swift_SmtpTransport::newInstance($this->host, $this->port);
				if ($this->security) {
					$this->setEncryption($this->security);
				}
				if ($this->username) {
					$this->setUsername($this->username);
				}
				if ($this->password) {
					$this->setPassword($this->password);
				}
			} elseif ($this->mode == 'mail') {
				$this->_transport = Swift_MailTransport::newInstance();
			} else {
				throw new Exception('unrecognised email mode ' . $this->mode);
			}
		}

		return $this->_transport;
	}

	/**
	 * get the SwiftMailer object with the configured transport
	 *
	 */
	protected function getMailer()
	{
		if (!$this->_mailer) {
			$this->_mailer = Swift_Mailer::newInstance($this->getTransport());
		}
		return $this->_mailer;
	}

	/**
	 * instantiate an appopriate SwiftMailer email message object
	 *
	 */
	public function newMessage()
	{
		return Swift_Message::newInstance();
	}

	/**
	 * Sends a message to the recipient, unless they're forbidden
	 * @param Swift_Message $message
	 */
	protected function directlySendMessage($message) {
		$mailer = $this->getMailer();

		if ($this->recipientForbidden($message)) {
			$message->setBody("This message was generated by the OpenEyes instance at: http://".(@$_SERVER['HTTPS']?'s':'').@$_SERVER['SERVER_NAME']."\n\nThe content has been removed as this email address is deemed insecure.\n\nPlease log into OpenEyes to view your messages.");
			$message->setChildren(array());
		}

		return $mailer->send($message);
	}

	/**
	 * Diverts an email from its original destination. Useful for testing things in nearlive
	 * @param Swift_Message $message
	 */
	protected function divertMessage($message) {
		$params = Yii::app()->params;

		// 1. Verify we have a list of addresses to divert to
		if (!array_key_exists('Mailer_divert_addresses', $params))
			return;
		$diverts = $params['Mailer_divert_addresses'];

		// 2. Prepend the intended list of recipients
		$orig_rcpts = $message->getHeaders()->get('To');
		if (is_array($orig_rcpts))
			$orig_rcpts = implode(", ", $orig_rcpts);
		// 3. Divert the mail to the divert addresses
		$message->setTo($diverts);
		$message->getHeaders()->addTextHeader('X-Original-Rcpt', $orig_rcpts);
		return $this->directlySendMessage($message);
	}

	/**
	 * Send an email
	 *
	 * @param Swift_Message $message
	 */
	public function sendMessage($message)
	{
		$params = Yii::app()->params;

		switch (@$params['Mailer_mode']) {
			case false: //Disable
				return;
			case 'divert':
				return $this->divertMessage($message);
			default:
				return $this->directlySendMessage($message);
		}
	}

	/**
	 * Checks the email recipients are in domains that are allowed.
	 *
	 * @param $message
	 * @return bool
	 */
	public function recipientForbidden($message)
	{
		if (!empty(Yii::app()->params['restrict_email_domains'])) {
			foreach ($message->getTo() as $email => $name) {
				$domain = preg_replace('/^.*?@/','',$email);
				if (!in_array($domain,Yii::app()->params['restrict_email_domains'])) {
					return true;
				}
			}
			if ($cc = $message->getCc()) {
				foreach ($message->getCc() as $email => $name) {
					$domain = preg_replace('/^.*?@/','',$email);
					if (!in_array($domain,Yii::app()->params['restrict_email_domains'])) {
						return true;
					}
				}
			}
			if ($bcc = $message->getBcc()) {
				foreach ($message->getBcc() as $email => $name) {
					$domain = preg_replace('/^.*?@/','',$email);
					if (!in_array($domain,Yii::app()->params['restrict_email_domains'])) {
						return true;
					}
				}
			}

		}
	}

	/**
	 * Mailer:mail is intended as a more robust simple replacement for php mail(),
	 * @param array $to address eg array('helpdesk@example.com'=>'OpenEyes')
	 * @param string $subject
	 * @param string $body
	 * @param array $from address eg array('helpdesk@example.com'=>'OpenEyes')
	 * @return bool mail sent without error
	 */
	public static function mail($to, $subject, $body, $from)
	{
		try {
		$message = Yii::app()->mailer->newMessage();
		$message->setSubject($subject);
		$message->setFrom($from);
		$message->setTo($to);
		$message->setBody($body);
		Yii::app()->mailer->sendMessage($message);
		}
		catch (Exception $Exception)
		{
			OELog::logException($Exception);
			return false;
		}
		return true;
	}
}


