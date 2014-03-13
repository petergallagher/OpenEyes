<?php
/**
 *
 * Use OEMail::mail with the same syntax as mail(),
 * the difference being it will continue execution but log the exception
 * Optional extra parameter $flashErrorMessageOnFail to display a flash warning message
 *
 */

class OEMail {
	public static function mail($email, $subject, $body, $headers, $flashErrorMessageOnFail=null) {
		if(!@mail($email, $subject, $body, $headers)) {
			if($flashErrorMessageOnFail) {
			Yii::app()->user->setFlash('warning.email-failure',$flashErrorMessageOnFail);
			}
			self::logException(new Exception("Failed to send email to $email"));
			return false;
		}
		return true;
	}

	static function logException($exception)
	{
		$category = 'exception.' . get_class($exception);
		if ($exception instanceof CHttpException)
			$category .= '.' . $exception->statusCode;
		$message = $exception->__toString();
		if (isset($_SERVER['REQUEST_URI']))
			$message .= ' REQUEST_URI=' . $_SERVER['REQUEST_URI'];
		Yii::log($message, CLogger::LEVEL_ERROR, $category);
	}
} 