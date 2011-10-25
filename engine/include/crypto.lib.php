<?php
	/*
		project: recovery
		version: 0.0.1
		date: 2011-02-16
		
		file: crypto.lib.php
		version: 0.0.1
		date: 2011-02-15
		author: daryl
		description: a class providing cryptographic functions and primitives forming the basis of many other
		critical site functions. it has been thoroughly thought-out and designed so as to afford the maximum
		possible security and account for any conceivable environment, and for that reason any tampering 
		should be avoided barring  absolute necessity. the availability of some cryptographic primitives is
		dependent upon operating system libraries. this is predominantly for use in POSIX-compliant systems.
	*/

	// every file must begin with a check to ensure the presence of the website "engime." this practice halts
	// any possible attempts to execute PHP code outside of a website context.
	if (!defined('IN_RECOVERY')) die();

	class cryptoClass {
		private $randomState;
		private $itoa64;
		private $iterationCountLog2;

		// we initialize with a pseudorandom seed.
		public function __construct() {
			$this->randomState = microtime() . getmypid();
			$this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			$this->iterationCountLog2 = 8;
		}
	
		/***** function: getRandomBytes()
		description: obtains pseudorandom data of a particular size
		returns: string containing MD5 hash of random data
		*****/
		function getRandomBytes($count) {
			$output = '';
			if (($fd = @fopen('/dev/urandom', 'rb'))) {
				$output = fread($fd, $count);
				fclose($fd);   
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

		/***** function: encode64()
			description:
		*****/
		function encode64($input, $count) {
			$output = '';
			$i = 0;
			do {
				$value = ord($input[$i++]);
				$output .= $this->itoa64[$value & 0x3f];
				if ($i < $count)
					$value |= ord($input[$i]) << 8;
				$output .= $this->itoa64[($value >> 6) & 0x3f];
				if ($i++ >= $count)
					break;
				if ($i < $count)
					$value |= ord($input[$i]) << 16;
				$output .= $this->itoa64[($value >> 12) & 0x3f];
				if ($i++ >= $count)
					break;
				$output .= $this->itoa64[($value >> 18) & 0x3f];
			} while ($i < $count);

			return $output;
		}
		
		/***** function: genPassword()
		description: attempt to generate a cryptographically secure, highly entropic password
		returns: string containing a salted password hash
		*****/
		public function genPassword() {
			$pwLength = 10;
			$charSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$password = ''; 
                                
			srand((double) microtime() * 1000000);
                 
			for ($i=0; $i < $pwLength; ++$i) $password .= substr($charSet, rand() % strlen($charSet), 1);
			return $password . ':::::' . $this->hashPassword($password);
		}
		
		/***** function: genUniqueID()
		description: attempt to generate a highly entropic unique identifier
		returns: string containing identifier
		*****/
		public function genUniqueID() {
			return sha1($this->getRandomBytes(20));
		}             
		
		/***** function: genSaltDES()
		*****/
		function genSaltDES($input) {
			$countLog2 = min($this->iterationCountLog2 + 8, 24);
			// This should be odd to not reveal weak DES keys, and the maximum valid value is (2**24 - 1)
			// which is odd anyway.
			$count = (1 << $countLog2) - 1;

			$output = '_';
			$output .= $this->itoa64[$count & 0x3f];
			$output .= $this->itoa64[($count >> 6) & 0x3f];
			$output .= $this->itoa64[($count >> 12) & 0x3f];
			$output .= $this->itoa64[($count >> 18) & 0x3f];

			$output .= $this->encode64($input, 3);

			return $output;
		}
		/***** function: genSaltMD5()
		*****/
		function genSaltMD5($input) {
			$salt = '';
			$input = substr($input, 0, 8);
			$input = base64_encode($input);
			$salt = '$1$' . $input . '$';
			return $salt;
		}
    
		/***** function: genSaltBlowfish()
		*****/
		function genSaltBlowfish($input) {
			// This one needs to use a different order of characters and a different encoding scheme from the
			// one in encode64() above. We care because the last character in our encoded string will only
			// represent 2 bits.  While two known implementations of bcrypt will happily accept and correct a
			// salt string which has the 4 unused bits set to non-zero, we do not want to take chances and we
			// also do not want to waste an additional byte of entropy.
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

		/***** function: hashPassword()
		*****/
		function hashPassword($password) {
			$random = '';
			
			if (CRYPT_BLOWFISH == 1) {
				$random = $this->getRandomBytes(16);
				$hash = crypt($password, $this->genSaltBlowfish($random));
				if (strlen($hash) == 60)
					return $hash;
			}

			if (CRYPT_MD5 == 1) {
				$random = $this->getRandomBytes(8);
				$hash = crypt($password, $this->genSaltMD5($random));
				if (strlen($hash) == 34)
					return $hash;
			}
			
			if (CRYPT_EXT_DES == 1) {
				if (strlen($random) < 3)
				$random = $this->cryptoClass->getRandomBytes(3);
				$hash = crypt($password, $this->genSaltDES($random));
				if (strlen($hash) == 20)
					return $hash;
			}
			
			return '*';
		}

		/***** function: checkPassword()
		*****/
        function checkPassword($password, $storedHash) {
			$hash = crypt($password, $storedHash);
			return $hash == $storedHash;
		}
}
?>
