<?php
// some version constants
define ('majorversion',0);
define ('minorversion',4);
define ('patchlevel',0);
define ('version', majorversion . '.' . minorversion . '.' . patchlevel);
define ('threaded',1);
define ('nonthreaded',0);
define ('mysql4','mysql4');
define ('mysql3','mysql3');
define ('MySQL4','mysql4');
define ('MySQL3','mysql3');
define ('PostgreSQL','postgresql');
define ('postgresql','postgresql');
define ('E_NONE',0);
define ('STANDARD_LANGUAGE','english');
define ('STANDARD_LANGCODE','en'); // remove this
define ('DEFAULT_CONTENT_LANG','en');
define ('STANDARD_THEME','moderngray');
define ('STANDARD_DATABASE_TYPE',MySQL4);
define ('COPYRIGHT','&copy; 2005 Nathan Samson. YaPCaS is Licensed under the GPL');
define ('NL',chr (10)); // NewLine
define ('TAB',chr (9)); // TAB
//define ('TBL_PAGES',TBL_PREFIX . 'pages');
define ('FIELD_PAGES_NAME','name');
define ('FIELD_PAGES_LANGUAGE','language');
define ('FIELD_PAGES_IN_NAVIGATION','show_in_nav');
define ('FIELD_PAGES_IN_USER_NAVIGATION','show_in_user_nav');
define ('FIELD_PAGES_LINK','name');
define ('FIELD_PAGES_SHOWN_NAME','shown_name');
?>
