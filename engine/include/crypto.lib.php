<?php
	/* crypto.lib.php
	 * date: 2011-10-26
	 * description: a class providing cryptographic functions forming the basis of many other critical site functions. 
	*/

	if (!defined('IN_RECOVERY')) die();

	class cryptoClass {
		private $randomState;
		private $iterationCountLog2;
		private $hashAlgorithm;
		
		// we initialize with a pseudorandom seed.
		public function __construct($iterations = 8) {
			if ($iterations < 4) $iterations += 4;
			$this->randomState = microtime() . getmypid();
			$this->iterationCountLog2 = min($iterations, 31);
			
			// default hash algorithm to use for unique nonces and session IDs (not for passwords)
			$this->hashAlgorithm = 'sha1';
		}
	
		/* function: getRandomBytes()
		 * description: obtains pseudorandom data of a particular size
		 * returns: string containing bytes
		*/
		function getRandomBytes($count) {
			$output = '';
			
			// we will try to obtain entropy from several sources, starting with OpenSSL
			if (function_exists('openssl_random_pseudo_bytes')) {
				$strong = FALSE;
				$output = openssl_random_pseudo_bytes($count, $strong);
				// if OpenSSL didn't use a strong cryptographic primitive, we'll find another source of entropy
				if (FALSE == $strong) $output = '';
			}
			
			// if we've got a POSIX system, hopefully urandom is available
			if ($fd = @fopen('/dev/urandom', 'rb')) {
				$output = fread($fd, $count);
				fclose($fd);   
			}

			// if we're on Windows, hopefully we can use its PRNG
			if (class_exists('COM')) {
				@$com = new COM('CAPICOM.Utilities.1');
				@$output .= base64_decode($com->GetRandom($count, 0));
			}
			
			// we fall back to a rather cryptographically insufficient but workable source of entropy
			if (strlen($output) < $count) {
				$output = '';
				for ($i = 0; $i < $count; $i += 16) {
					$this->randomState = md5(microtime() . $this->randomState);
					$output .= md5($this->randomState, TRUE);
				}

				$output = substr($output, 0, $count);
			}
                
			return $output;
		}
		
		/* function: genPassword()
		 * description: attempt to generate a cryptographically secure password
		 * returns: string containing a plaintext password and corresponding salted password hash
		*/
		public function genPassword() {
			$pwLength = 10;
			$charSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$password = ''; 
                                
			srand((double) microtime() * 1000000);
                 
			for ($i=0; $i < $pwLength; ++$i) $password .= substr($charSet, rand() % strlen($charSet), 1);
			return $password . ':::::' . $this->hashPassword($password);
		}
		
		/* function: genUniqueID()
		 * description: attempt to generate a highly entropic unique identifier
		 * returns: string containing identifier
		*/
		public function genUniqueID($bytes = 20) {
			if (function_exists('hash')) {
				return hash($this->hashAlgorithm, $this->getRandomBytes($bytes));
			} else {
				return sha1($this->getRandomBytes($bytes));
			}
		}             
    
		/* function: genSalt()
		 * description: generates a bcrypt compatible salt and hash encoding string
		 * returns: string containing hash type, salt, and iteration count
		*/
		function genSalt($input) {
			$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

			$output = '$2a$';
			$output .= chr(ord('0') + $this->iterationCountLog2 / 10);
			$output .= chr(ord('0') + $this->iterationCountLog2 % 10);
			$output .= '$';

			$i = 0;
			do {
				$c1 = ord($input[$i++]);
				$output .= $itoa64[$c1 >> 2];
				$c1 = ($c1 & 0x03) << 4;
				if ($i >= 16) {
					$output .= $itoa64[$c1];
					break;
				}

				$c2 = ord($input[$i++]);
				$c1 |= $c2 >> 4;
				$output .= $itoa64[$c1];
				$c1 = ($c2 & 0x0f) << 2;

				$c2 = ord($input[$i++]);
				$c1 |= $c2 >> 6;
				$output .= $itoa64[$c1];
				$output .= $itoa64[$c2 & 0x3f];
			} while (1);
			
			return $output;
		}

		/* function: hashPassword()
		*/
		function hashPassword($password) {
			$random = $this->getRandomBytes(16);
			$hash = crypt($password, $this->genSalt($random));
			if (strlen($hash) == 60) return $hash;
			return '*';
		}

		/* function: checkPassword()
		*/
        function checkPassword($password, $storedHash) {
			$hash = crypt($password, $storedHash);
			return $hash == $storedHash;
		}
}
?>
