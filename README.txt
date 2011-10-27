Requirements
	PHP 5.3+
	H2O Template Engine (provided)
	
Obtaining Mephit
	The latest version of Mephit can be downloaded at https://github.com/nesicus/mephit/

	Installation
	1. Download and extract Mephit into your website's HTML root or subfolder
	2. Navigate your web browser to the path where Mephit was extracted and complete the automated setup
	3. Once Mephit has been extracted and configured, register an account on the website. This user will be administrator by default.
	
Manual Configuration
	You may also configure this software manually, by editing the file main.conf.php found in the engine/etc directory.
	This will require you to manually create the database schema, found in this package as the schema.sql file.
	Once this is done, navigate to the path where Mephit was instracted and create a user. This user will be administrator by default.

	Use

Licensing
	This software is free to use under the MIT license. The included H2O Template Engine is also provided under this license. Both licenses are at the bottom of this document. The licenses are also included as LICENSE.txt for Mephit and LICENSE_H2O.txt for the H2O software.

Attributions
	In addition to the H2O Template Engine, this CMS makes use of the Portable PHP password hashing framework by Solar Designer, available at:
	
	http://openwall.com/phpass/
	
	This is the basis for the class "cryptoClass" in engine/include/crypto.lib.php
	Thanks go to Taylor Luk (H2O) and Solar Designer (phpass) for their efforts.
	
Mephit CMS
The MIT License
------------------------
Copyright (c) 2011 Daryl Fain

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.




H2O Template Engine

The MIT License
------------------------
Copyright (c) 2008 Taylor Luk 

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.