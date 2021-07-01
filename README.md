# sourcemod-vip-manage
## Manage people in vip group from webpanel

The site will allow you to manage vips added to the database and integrate them with the server via sourcemod plugin.

- Simple
- Fast
- Stable

## Features

- Add or remove server
- Add or remove vip to/from server
- Add people as vip to all servers

## Installation

Put all files to webserver with PHP 7.3+ and go to page, the installer will appear, fill in all fields and install the panel. Now compile sourcemod plugin and place them in sourcemod/plugins dir.

database entry in databases.cfg **vip-webpanel**
Ex. 	
```
"vip-webpanel"
	{
		"driver"			"mysql"
		"host"				"mysql_host.domain.tld"
		"database"			"database_name"
		"user"				"user_name"
		"pass"				"P4$$W0RD"
		//"timeout"			"0"
		//"port"			"0"
	}
```

## Credits

[Xiaoying Riley](https://themes.3rdwavemedia.com) - HTML template
