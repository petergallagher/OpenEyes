<?php
/**
 * CFileLogRoute class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileLogRoute records log messages in files.
 *
 * The log files are stored under {@link setLogPath logPath} and the file name
 * is specified by {@link setLogFile logFile}. If the size of the log file is
 * greater than {@link setMaxFileSize maxFileSize} (in kilo-bytes), a rotation
 * is performed, which renames the current log file by suffixing the file name
 * with '.1'. All existing log files are moved backwards one place, i.e., '.2'
 * to '.3', '.1' to '.2'. The property {@link setMaxLogFiles maxLogFiles}
 * specifies how many files to be kept.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CFileLogRoute.php 3001 2011-02-24 16:42:44Z alexander.makarow $
 * @package system.logging
 * @since 1.0
 */
class ExceptionLogRoute extends CLogRoute
{
	/**
	 * @var integer maximum log file size
	 */
	private $_maxFileSize=1024; // in KB
	/**
	 * @var integer number of log files used for rotation
	 */
	private $_maxLogFiles=5;
	/**
	 * @var string directory storing log files
	 */
	private $_logPath;
	/**
	 * @var string log file name
	 */
	private $_logFile='application.log';

	public $adminEmail = false;
	public $exclude_regex = array();
	public $useragent_regex = array();
	public $emailSubject = 'Exception';

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		parent::init();
		if($this->getLogPath()===null) {
			$this->setLogPath(Yii::app()->getRuntimePath());
		}
	}

	/**
	 * @return string directory storing log files. Defaults to application runtime path.
	 */
	public function getLogPath()
	{
		return $this->_logPath;
	}

	/**
	 * @param string $value directory for storing log files.
	 * @throws CException if the path is invalid
	 */
	public function setLogPath($value)
	{
		$this->_logPath=realpath($value);
		if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath)) {
			throw new CException(
				Yii::t(
					'yii',
					'CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
					array('{path}'=>$value)
				)
			);
		}
	}

	/**
	 * @return string log file name. Defaults to 'application.log'.
	 */
	public function getLogFile()
	{
		return $this->_logFile;
	}

	/**
	 * @param string $value log file name
	 */
	public function setLogFile($value)
	{
		$this->_logFile=$value;
	}

	/**
	 * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
	 */
	public function getMaxFileSize()
	{
		return $this->_maxFileSize;
	}

	/**
	 * @param integer $value maximum log file size in kilo-bytes (KB).
	 */
	public function setMaxFileSize($value)
	{
		if(($this->_maxFileSize=(int) $value)<1)
			$this->_maxFileSize=1;
	}

	/**
	 * @return integer number of files used for rotation. Defaults to 5.
	 */
	public function getMaxLogFiles()
	{
		return $this->_maxLogFiles;
	}

	/**
	 * @param integer $value number of files used for rotation.
	 */
	public function setMaxLogFiles($value)
	{
		if (($this->_maxLogFiles=(int) $value)<1) {
			$this->_maxLogFiles=1;
		}
	}

	/**
	 * Returns the currently active username or some default text
	 * @return string
	 */
	protected function getUserName()
	{
		return isset(Yii::app()->session['user']->username) ? Yii::app()->session['user']->username : 'Not logged in';
	}

	/**
	 * Given a timestamp (from time()), formats it for the log
	 * @param timestamp $time
	 * @return string
	 */
	protected function formatLogTimeStamp($time)
	{
		return substr($timestamp,0,5).'-'.substr($timestamp,5,strlen($timestamp));
	}

	/**
	 * Given a log file name (should not exist) and an array of entries, write the entries to the log file
	 * @param string $logFile
	 * @param array[string] $entries
	 * @return void
	 */
	protected function writeLogFile($logFile, $entries)
	{
		if(@filesize($logFile)>$this->getMaxFileSize()*1024) {
			$this->rotateFiles();
		}
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		foreach($entries as $log) {
			@fwrite($fp,$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]));
		}
		@flock($fp,LOCK_UN);
		@fclose($fp);
	}

    /**
	 * In the event that there is a field called 'password' in a 'LoginForm' form, we would quite like to not see that in the logs. PII and all that.
	 * @return void
	 */
	protected function censorPassword()
	{
		if (isset($_POST['LoginForm']['password'])) {
			$_POST['LoginForm']['password'] = '*******';
		}
	}
	
	/**
	 * Actually write the log entry
	 * @param $path a path (which should not exist)
	 * @return void
	 */
	protected function writeLogContents($path)
	{
		file_put_contents($path, "SERVER:\n\n".print_r($_SERVER,true)."\n\nPOST:\n\n".print_r($_POST,true));
	}

	/**
	 * Attempt to do RCS-style history logfile naming (.1, .2 etc.)
	 * @param string $path Base path
	 * @return string a unique path
	 */
	protected function findUnusedLogfile($path)
	{
		$n = 1;
		$logPath = $path;
		while (file_exists($logPath)) {
			$logPath = $path.'.'.$n;
			$n++;
		}
	}
	
	/**
	 * Saves log messages in files.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
		$logfile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$this->writeLogFile($logfile, $logs);
		
		if ($this->adminEmail && $log[1] == 'error' && !$this->isFiltered($log[0]) && !$this->userAgentFiltered(@$_SERVER['HTTP_USER_AGENT'])) {
			$timestamp = $this->formatLogTimeStamp(time());
			$logpath = $this->findUnusedLogFile($timestamp.'.log');
			$this->censorPassword();
			$this->writeLogContents($logpath);
			$details = array(
				'user' => $this->getUserName(),
				'user-agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'None',
				'request-uri' => ((!empty($_POST) ? 'POST' : 'GET'))." http".(@$_SERVER['HTTPS']?'s':'').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
				'referrer' => $_SERVER['HTTP_REFERER']||'(None)',
				'remote-addr' => $_SERVER['REMOTE_ADDR']||'(None)',
				'via' => $_SERVER['HTTP_VIA']||"(None)",
				'x-forwarded-for' => $_SERVER['HTTP_X_FORWARDED_FOR']||"(None)",
				'log-0' => $log[0],
				'to' => $this->adminEmail,
				'subject' => $this->emailSubject . " [" . $timestamp . "]",
			);
			$message = $this->makeMessage($details);
			$this->sendMessage($message);
		}
	}

    /**
	 * Given a collection of details, construct a Swift_Message
	 * @param $details
	 * @return Swift_Message
	 */
	protected function makeMessage($details)
	{
		$body = "";
		$msg = array(
			"User" => $details['user'],
			"User agent" => $details['user-agent'],
			"Request URI" => $details['request-uri'],
			"Referring URL" => $details['referrer'],
			"Client IP" => $details['remote-addr'],
			'Via' => $details['via'],
			'Proxied IP' => $details['x-forwarded-for'],
			"Log file" => $details['log-file'],
		);
		foreach ($msg as $k => $v) {
			$body .= "$k: $v\n";
		}
		$body .= $details['log-0'];

		$message = Yii::app()->Mailer->newMessage();
		$message->setBody($body);
		$message->setTo($details['to']);
		$message->setSubject($details['subject']);
		return $message;
	}

	/**
	 * Given a swiftmailer message, actually send it
	 * @param Swift_Message $message
	 */
	protected function sendMessage($message) {
		return Yii::app()->mailer->send($message);
	}

	public function isFiltered($msg)
	{
		foreach ($this->exclude_regex as $regex) {
			if (preg_match($regex,$msg)) {
				return true;
			}
		}
		return false;
	}

	public function userAgentFiltered($useragent)
	{
		foreach ($this->useragent_regex as $regex) {
			if (preg_match($regex,$useragent)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$max=$this->getMaxLogFiles();
		for ($i=$max;$i>0;--$i) {
			$rotateFile=$file.'.'.$i;
			if (is_file($rotateFile)) {
				// suppress errors because it's possible multiple processes enter into this section
				if($i===$max)
					@unlink($rotateFile);
				else
					@rename($rotateFile,$file.'.'.($i+1));
			}
		}
		if(is_file($file))
			@rename($file,$file.'.1'); // suppress errors because it's possible multiple processes enter into this section
	}
}
