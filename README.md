# Service Configurator

This is a service configuration automator. It can provision service configuration
from potentially untrusted sources. The intent is that app-specific config files
for global services (e.g. Nginx virtualhost configs) can be checked into version
control along with app source and then automatically installed on deploy, provided
they are accompanied by a valid GPG signature, which validates that the configuration
is indeed intended to be applied by a trusted party.
