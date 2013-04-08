<?php
/**
Copyright 2011-2013 Nick Korbel

This file is part of phpScheduleIt.

phpScheduleIt is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

phpScheduleIt is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with phpScheduleIt.  If not, see <http://www.gnu.org/licenses/>.
*/
// See http://adldap.sourceforge.net/wiki/doku.php?id=documentation_configuration for a full list

$conf['settings']['domain.controllers'] = 'DC=mydomain,DC=local'; // comma separated list of ldap servers such as domaincontroller1,controller2
$conf['settings']['port'] = '389';      // default ldap port 389 or 636 for ssl.
$conf['settings']['username'] = '';     // admin user - bind to ldap service with an authorized account user/password
$conf['settings']['password'] = '';     // admin password - corresponding password
$conf['settings']['basedn'] =  'ou=uidauthent,o=domain.com';   // The base dn for your domain. This is generally the same as your account suffix, but broken up and prefixed with DC=. Your base dn can be located in the extended attributes in Active Directory Users and Computers MMC.
$conf['settings']['version'] = '3';		// LDAP protocol version
$conf['settings']['use.ssl'] = 'false'; // 'true' if 636 was used.
$conf['settings']['account.suffix'] = '';	// The full account suffix for your domain. Example: @uidauthent.domain.com.
$conf['settings']['database.auth.when.ldap.user.not.found'] = 'false';	// if ldap auth fails, authenticate against phpScheduleIt database
$conf['settings']['attribute.mapping'] = 'sn=sn,givenname=givenname,mail=mail,telephonenumber=telephonenumber,physicaldeliveryofficename=physicaldeliveryofficename,title=title';	// mapping of required attributes to attribute names in your directory
?>