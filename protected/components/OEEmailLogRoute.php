class OEEmailLogRoute extends CEmailLogRoute {
	/**
	 * Sends an email.
	 * @param string $email single email address
	 * @param string $subject email subject
	 * @param string $message email content
	 */
	protected function sendEmail($email,$subject,$message)
	{
 		$headers=$this->getHeaders();
		if($this->utf8)
		{
			$headers["MIME-Version"] = "1.0";
			$headers["Content-Type"] = "text/plain; charset=UTF-8";
			$subject='=?UTF-8?B?'.base64_encode($subject).'?=';
		}
		if(($from=$this->getSentFrom())!==null)
		{
			$matches=array();
			preg_match_all('/([^<]*)<([^>]*)>/iu',$from,$matches);
			if(isset($matches[1][0],$matches[2][0]))
			{
				$name=$this->utf8 ? '=?UTF-8?B?'.base64_encode(trim($matches[1][0])).'?=' : trim($matches[1][0]);
				$from=trim($matches[2][0]);
				$headers["From"] = "{$name} <{$from}>";
			}
			else
				$headers["From"] = $from;
			$headers["Reply-To"] = $from;
		}
		Yii::app()->Mailer->mailWithHeaders($email,$subject,$message,$headers);
	}

	/**
	 * Utility function to determine if an array has entirely string keys
	 * @param array $array
	 * @return bool
	 */
	protected function is_assoc($array)
	{
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

	/**
	 * Given a key and value, adds a header to ourselves
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	protected function addHeaderByKV($key, $value)
	{
		$this->_headers[$key] = $value;
	}

	/**
	 * Given a correctly formed header string, add it to ourselves
	 * @param string $v header string
	 * @return void
	 */
	protected function addHeaderByString($v)
	{
		$split = preg_split('/:\s*/', $v, 2);
		$this->addHeader($split[0],$split[1]);
	}
	
	/**
	 * Used to delete a named header
	 * @param string $key
	 * @return void
	 */
	public function deleteHeader($key)
	{
		unset($this->_headers[$key]);
	}

	/**
	 * @param mixed $value list of additional headers to use when sending an email.
	 * When string, is treated as one-or-more \r\n separated, correctly-formed headers
	 * When array of string, is treated as correctly-formed headers
	 * When assoc of string, is treated as Headerkey => Headerval
	 * @return void
	 */
	public function addHeaders($headers)
	{
		if (is_array($headers)) {
			if ($this->is_assoc($headers)) {
				foreach ($headers as $k => $v) {
					$this->addHeaderByKV($k,$v);
				}
			} else {
				foreach ($headers as $v) {
					$this->addHeaderByString($v);
				}
			}
		} else {
			foreach (preg_split('/\r?\n/', $headers, -1, PREG_SPLIT_NO_EMPTY) as $h) {
				$this->addHeaderByString($h);
			}
		}
	}

	/**
	 * Replicating the old API
	 * @param $headers
	 * @return void
	 */
	public function setHeaders($headers)
	{
		$this->_headers = array();
		$this->addHeaders($headers);
	}

}