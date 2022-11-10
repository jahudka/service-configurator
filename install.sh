#!/usr/bin/env bash

set -eu

install_dir="${1:-/opt/servconf}"

if [[ -e "${install_dir}" ]]; then
	echo "Destination already exists, aborting."
	exit 1
fi

tmp="/tmp/servconf-install-$( date +%s )"

if ! mkdir -m 0750 "${tmp}"; then
	echo "Cannot create temporary installation directory '${tmp}'"
	exit 1
fi

umask 027

cd "${tmp}"

echo "Downloading latest SiteAdmin source..."
wget -O servconf.zip "https://github.com/jahudka/service-configurator/archive/main.zip"

echo "Unpacking..."
unzip -oq servconf.zip

cd service-configurator-main

echo "Installing dependencies..."
composer install --no-ansi --no-interaction --no-cache

echo "Creating required files and directories..."
mkdir -p var/{run,gpg}
cp etc/services.yaml.example etc/services.yaml

echo "Moving installation into place..."
cd /
mv -fT "${tmp}/service-configurator-main" "${install_dir}"

echo "Cleaning up..."
rm -rf "${tmp}"

echo "All finished!"
echo ""
echo -n "Please don't forget to set up the Service Configurator daemon. "
echo -n "Either set something like supervisord to run 'servconf run', "
echo "or set up the servconf Systemd service using ${install_dir}/servconf.service."
