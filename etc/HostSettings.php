<?php

$wgServer           = "//test.oshwiki.eu";
$wgSquidServers = array( '127.0.0.1:8090' );
$wgEmergencyContact = "webmaster@syslab.com";
$wgPasswordSender = "webmaster@syslab.com";

## Database settings
$wgDBtype           = "mysql";
$wgDBserver         = "127.0.0.1";
$wgDBname           = "wikidb";
$wgDBuser           = "wikiuser";
$wgDBpassword       = "password";

$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUploadDirectory = "/expert/oshwiki/shared/blobs/images";
$wgSecretKey = "KEY";
$wgDiff3 = "/usr/bin/diff3";

$wgVirtualRestConfig['modules']['parsoid'] = array(
  // URL to the Parsoid instance
  'url' => 'http://localhost:8000',
  // Parsoid "domain", see below (optional)
  'domain' => 'test.oshwiki.eu',
  // Parsoid "prefix", see below (optional)
  'prefix' => 'test.oshwiki.eu'
);

$wgSMTP = array( 
'host' => "some.smtp.host", 
'IDHost' => "some.id.host", 
'port' => 25, 
'auth' => false, // You may need to change this 
);
