Requirements
	PHP 5.3+
	H2O Template Engine (provided)
	
Obtaining Mephit
	The latest version of Mephit can be downloaded at https://github.com/nesicus/mephit/

	Installation
	1. Download and extract Mephit into your website's HTML root or subfolder
	2. Ensure that PHP is able to write to the engine/etc directory and the configuration files therein
	3. Navigate your web browser to the path where Mephit was extracted and complete the automated setup
	4. Once Mephit has been extracted and configured, register an account on the website. This user will be administrator by default.
	
Manual Configuration
	You may also configure this software manually, by editing the file main.conf.php found in the engine/etc directory.
	This will require you to manually create the database schema, found in this package as the schema.sql file.
	Once this is done, navigate to the path where Mephit was instracted and create a user. This user will be administrator by default.

Use

Licensing
	This software is free to use under the BSD license. The included H2O Template Engine is also provided under the MIT license. Both licenses are at the bottom of this document. The licenses are also included as LICENSE.txt for Mephit and LICENSE_H2O.txt for the H2O software.

Attributions
	In addition to the H2O Template Engine, this CMS makes use of the Portable PHP password hashing framework by Solar Designer, available at:
	
	http://openwall.com/phpass/
	
	This is the basis for the class "cryptoClass" in engine/include/crypto.lib.php
	Thanks go to Taylor Luk (H2O) and Solar Designer (phpass) for their efforts.
	
Mephit CMS
Modified BSD License
------------------------
Copyright 2011 Daryl Fain <daryl@99years.com>

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

* Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above
  copyright notice, this list of conditions and the following disclaimer
  in the documentation and/or other materials provided with the
  distribution.
* Neither the name of the {company} nor the names of its
  contributors may be used to endorse or promote products derived from
  this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.






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