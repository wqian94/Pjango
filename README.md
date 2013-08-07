Pjango -- Django for PHP project
================================

Pjango is open-source project that aims to allow web developers
familiar with Django in Python to use the same templates in a
PHP environment. Furthermore, the basic goal is to provide
compatibility for Django templates being imported into PHP
projects; compability in the other direction need not be
guaranteed.

Documentation has been provided in the 'documentation' folder.
There are both UNIX-plaintext and HTML documentation files.

Licensing information can be found in the LICENSE included in
this package.

================================
Version Updates
================================

v1.1.0
======
- Significant bug fixes to {% extends %}

v1.0.6
======
- Improved reliability of CSRF. Also cleans up old tokens.

v1.0.5
======
- Fixed {% js %} to close properly

v1.0.4
======
- Fixed {% js_ext %} bug that prevented it from operating like
  a normal self-closing tag.

v1.0.3
======
- Modified tags {% css_ext%} and {% js_ext %} slightly
- Modified installationg index.php to give link to
	documentation compiling script

v1.0.2
======
- Added SQLite3 support to SQL class
- Documentation updated for SQL class
- Fixed bug in __SITE class that caused errors when refactoring
- Tag {% for %} now behaves properly for key,value pairs

v1.0.1
======
- Fixed {% extends %} tag warning
- Added PJANGO_VERSION constant
- Changed from home.php to index.php as default page
- Updated SQL library

v1.0.0
======
- Pjango initial launch
