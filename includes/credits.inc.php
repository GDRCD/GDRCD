<?php
$REFERENCES = gdrcd_filter('out', $PARAMETERS['info']['site_name']). ' - '.
              gdrcd_filter('out', $MESSAGE['homepage']['info']['webm']). ': '. gdrcd_filter('out', $PARAMETERS['info']['webmaster_name']). ' - '.
              gdrcd_filter('out', $MESSAGE['homepage']['info']['dbadmin']). ': '. gdrcd_filter('out', $PARAMETERS['info']['dbadmin_name']). ' - '.
              gdrcd_filter('out', $MESSAGE['homepage']['info']['email']). ': <a href="mailto:'. gdrcd_filter('out', $PARAMETERS['info']['webmaster_email']). '">'. gdrcd_filter('out', $PARAMETERS['info']['webmaster_email']). '</a>.';
$CREDITS = gdrcd_filter('out', 'Basato su "GDRCD '.$GLOBALS['PARAMETERS']['info']['GDRCD'].'". CMS Open Source sviluppato dal <a href="https://github.com/orgs/GDRCD/people" target="_blank">Team di GDRCD</a> e distribuito gratuitamente.');
$LICENCE = '<a href="license.md" target="_blank">' . gdrcd_filter('out', 'Licenza d\'uso e riproduzione') . '</a>.';
