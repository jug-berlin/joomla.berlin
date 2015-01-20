<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/**
 * Generates email reports for scan results
 */
class AEFinalizationEmail extends AEAbstractObject
{
	public function send_scan_email($parent)
	{
		if (parent instanceof AECoreDomainFinalization)
		{
			$parent->relayStep('Sending email');
			$parent->relaySubstep('');
		}

		// If no email is set, quit
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Getting email addresses");
		$registry = AEFactory::getConfiguration();
		$email = $registry->get('admintools.scanner.email', '');
		$email = trim($email);

		if (empty($email))
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "No email is set. Scan results will not sent by email.");

			return true;
		}

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Email address set to $email");

		// Get the ID of the scan
		$statistics = AEFactory::getStatistics();
		$latestBackupId = $statistics->getId();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Latest scan ID is $latestBackupId");

		// Get scan statistics
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Getting scan statistics");
		$items = F0FModel::getTmpInstance('Scans', 'AdmintoolsModel')
			->id($latestBackupId)
			->getItemList();
		$item = array_pop($items);

		// Populate table data for new, modified and suspicious files
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Populating table");
		$body_new = '';
		$body_modified = '';
		$totalFiles = F0FModel::getTmpInstance('Scanalerts', 'AdmintoolsModel')
			->scan_id($latestBackupId)
			->acknowledged(0)
			->getTotal();
		$segments = (int)($totalFiles / 100) + 1;
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Processing file list in $segments segment(s)");
		for ($i = 0; $i < $segments; $i++)
		{
			$limitstart = 100 * $i;

			$files = F0FModel::getTmpInstance('Scanalerts', 'AdmintoolsModel')
				//->resetSavedState()
				->scan_id($latestBackupId)
				->acknowledged(0)
				->limit(100)
				->limitstart($limitstart)
				->getItemList();

			if (!empty($files))
			{
				foreach ($files as $file)
				{
					$fileRow = "<tr><td>{$file->path}</td><td>{$file->threat_score}</td></tr>\n";
					if ($file->newfile)
					{
						$body_new .= $fileRow;
					}
					else
					{
						$body_modified .= $fileRow;
					}
				}
			}
		}

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Preparing email text");
		// Prepare the email body
		$body = '<html><head>' . JText::_('COM_ADMINTOOLS_SCANS_EMAIL_HEADING') . '<title></title></head><body>';
		$body .= '<h1>' . JText::_('COM_ADMINTOOLS_SCANS_EMAIL_HEADING') . "</h1><hr/>\n";
		$body .= '<h2>' . JText::_('COM_ADMINTOOLS_SCANS_EMAIL_OVERVIEW') . "</h2>\n";
		$body .= "<p>\n";
		$body .= '<strong>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_TOTAL') . "</strong>: " . $item->multipart . "<br/>\n";
		$body .= '<strong>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_MODIFIED') . "</strong>: " . $item->files_modified . "<br/>\n";
		$body .= '<strong>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_ADDED') . "</strong>: " . $item->files_new . "<br/>\n";
		$body .= '<strong>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_SUSPICIOUS') . "</strong>: " . $item->files_suspicious . "<br/>\n";
		$body .= "</p>\n";

		$body .= '<hr/><h2>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_ADDED') . "</h2>\n";
		$body .= "<table width=\"100%\">\n";
		$body .= "\t<thead>\n";
		$body .= "\t<tr>\n";
		$body .= "\t\t<th>" . JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH') . "</th>\n";
		$body .= "\t\t<th width=\"50\">" . JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE') . "</th>\n";
		$body .= "\t</tr>\n";
		$body .= "\t</thead>\n";
		$body .= "\t<tbody>\n";
		$body .= $body_new;
		unset($body_new);
		$body .= "\t</tbody>\n";
		$body .= '</table>';

		$body .= '<hr/><h2>' . JText::_('COM_ADMINTOOLS_LBL_SCANS_MODIFIED') . "</h2>\n";
		$body .= "<table width=\"100%\">\n";
		$body .= "\t<thead>\n";
		$body .= "\t<tr>\n";
		$body .= "\t\t<th>" . JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH') . "</th>\n";
		$body .= "\t\t<th width=\"50\">" . JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE') . "</th>\n";
		$body .= "\t</tr>\n";
		$body .= "\t</thead>\n";
		$body .= "\t<tbody>\n";
		$body .= $body_modified;
		unset($body_modified);
		$body .= "\t</tbody>\n";
		$body .= '</table>';

		$body .= '</body></html>';

		// Prepare the email subject
		$sitename = AEUtilJconfig::getValue('sitename', 'Unknown Site');
		$subject = JText::sprintf('COM_ADMINTOOLS_SCANS_EMAIL_SUBJECT', $sitename);

		// Send the email
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__ . ": Ready to send out emails");
		$this->_send_email($email, $subject, $body);

		return true;
	}

	private function _send_email($email, $subject, $body)
	{
		$mailer = AEPlatform::getInstance()->getMailer();

		if (!is_object($mailer))
		{
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, "Could not send email to $email - Reason: Mailer object is not an object; please check your system settings");

			return false;
		}

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "-- Creating email message");

		$recipient = array($email);
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->IsHTML(true);

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "-- Sending message");

		$result = $mailer->Send();

		if ($result instanceof JException)
		{
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, "Could not email $email:");
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, $result->getMessage());
			$ret = $result->getMessage();
			unset($result);
			unset($mailer);

			return $ret;
		}
		else
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "-- Email sent");

			return true;
		}
	}
}