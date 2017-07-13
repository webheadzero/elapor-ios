<?php
 header("Access-Control-Allow-Origin: *");
 
 public $passwordHashStrategy = 'crypt';
  
function generatePasswordHash($password, $cost = null){
	$salt = generateSalt($cost);
	$hash = crypt($password, $salt);
	// strlen() is safe since crypt() returns only ascii
	if (!is_string($hash) || strlen($hash) !== 60) {
		throw new Exception('Unknown error occurred while generating hash.');
	}
	return $hash;
}

function generateSalt($cost = 13)
{
	$cost = (int) $cost;
	if ($cost < 4 || $cost > 31) {
		throw new InvalidParamException('Cost must be between 4 and 31.');
	}

	// Get a 20-byte random string
	$rand = $this->generateRandomKey(20);
	// Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
	$salt = sprintf("$2y$%02d$", $cost);
	// Append the random salt data in the required base64 format.
	$salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));

	return $salt;
}

function generateRandomKey($length = 32)
    {
		$bytes = '';

        // If we are on Linux or any OS that mimics the Linux /dev/urandom device, e.g. FreeBSD or OS X,
        // then read from /dev/urandom.
        if (@file_exists('/dev/urandom')) {
            $handle = fopen('/dev/urandom', 'r');
            if ($handle !== false) {
                $bytes .= fread($handle, $length);
                fclose($handle);
            }
        }

        if (StringHelper::byteLength($bytes) >= $length) {
            return StringHelper::byteSubstr($bytes, 0, $length);
        }

        // If we are not on Linux and there is a /dev/random device then we have a BSD or Unix device
        // that won't block. It's not safe to read from /dev/random on Linux.
        if (PHP_OS !== 'Linux' && @file_exists('/dev/random')) {
            $handle = fopen('/dev/random', 'r');
            if ($handle !== false) {
                $bytes .= fread($handle, $length);
                fclose($handle);
            }
        }

        if (StringHelper::byteLength($bytes) >= $length) {
            return StringHelper::byteSubstr($bytes, 0, $length);
        }

        if (!extension_loaded('openssl')) {
            throw new InvalidConfigException('The OpenSSL PHP extension is not installed.');
        }

        $bytes .= openssl_random_pseudo_bytes($length, $cryptoStrong);

        if (StringHelper::byteLength($bytes) < $length || !$cryptoStrong) {
            throw new Exception('Unable to generate random bytes.');
        }

        return StringHelper::byteSubstr($bytes, 0, $length);
	}
?>