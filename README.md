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

v1.2.0
======
- Updated to deal with deprecated mime_content_type() function
  in PHP5.3+; will re-update when a better replacement is found
- Fixed bug where attempting to access a numerical key in an
  array (e.g. {{ arr.3 }}) causes the ".3" to be rendered as a
  float, rather than an index
- Fixed RootComponent multi-rendering on extends bug; very
  hacky, expect a better solution soon

v1.1.3
======
- Fixed RootComponent parent bug

v1.1.2
======
- Fixed bug that sometimes wasn't correctly rendering decimals
  as values
- Updated core set, with slight adjustments to AJAX library for
  link purposes
- In documentation, fixed bug where "." was appearing in the
  filenames of the text version "navigation"

v1.1.1
======
- Updated views.php to fall back to finding HTML templates and
  rendering them if the PHP files doesn't exist

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
