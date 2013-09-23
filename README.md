Introduction

WordPress Onion is a plugin for WordPress, adding Tor Hidden Services and/or a Tor Relay to the installation.
The Hidden Services can are responsible for anonymous contenet publishing and provide end-to-end encryption.

We configure WordPress to receive comments and posts via it's .onion address, which is only accessible throug the
Tor network. Hidden Service provide DDoS and cencorship resiliance. WordPress site owners are also able to contribute
bandwidth and IP to the Tor Network by running a Realy on their sites.


1. Hidden Service

We are using the latest Tor binaries copied from Tor Browser Bundle. This enables us to install a portable version of Tor
which we use to run the hidden service.

-download latest english Tor Browser Bundle


LD_LIBRARY_PATH="Lib/"
LDPATH="Lib/"

ONION_PATH=/var/www/wordpress/wp-content/plugins/WordPress-Onion/S6tt8vLeenvpQvrX6uY1QvFjkAg4OgfmD81X4WR4s9DlK3xPwH9T0cAh3c5VWKXJ

/usr/local/bin/tor -f ONION_PATH/tor/torrc --pidfile /var/run/tor/tor.pid --log notice file /var/log/tor/tor.log --runasdaemon 1 --datadirectory /var/lib/tor --user _tor



sha1sum(64bits random text)

on Activation, make a new randomly generated directory:
/var/www/wordpress/wp-content/plugins/WordPress-Onion/76cde9b3d310732721730c24af396e9454a2d9f4


TODO:
activate
	build our secret director
	save secret directory in database
	






