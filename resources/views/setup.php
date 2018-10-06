#!/bin/bash

mkdir imgFiles
wget http://archive.ubuntu.com/ubuntu/dists/xenial-updates/main/installer-amd64/current/images/netboot/ubuntu-installer/amd64/linux -O imgFiles/linux
wget http://archive.ubuntu.com/ubuntu/dists/xenial-updates/main/installer-amd64/current/images/netboot/ubuntu-installer/amd64/initrd.gz -O imgFiles/initrd.gz

git clone git://git.ipxe.org/ipxe.git
baseurl="http://<?php echo env("SYS_URL"); ?>"
cat > embed.ipxe << DELIM
#!ipxe
dhcp

set base-url ${baseurl}

echo About to chain

chain \${base-url}/second

shell

DELIM

cat > second << DELIM
#!ipxe

echo Trying to load image
imgload \${base-url}/imgFiles/linux
echo Trying to load initrd
initrd \${base-url}/imgFiles/initrd.gz

echo Attempt boot
imgargs linux auto=true fb=false url=\${base-url}/proxy/template/preseed tasksel/first=\"\" netcfg/choose_interface=eno1 hostname=teammachine domain=progcont

boot

DELIM


apt install dnsmasq -y
cat > /etc/dnsmasq.conf << DELIM
log-queries

log-dhcp

interface=enp0s8
domain=progcont
dhcp-range=10.1.0.100,10.1.255.255,255.255.0.0,infinite
dhcp-option=option:router,10.1.0.1
dhcp-host=ac:16:2d:37:cb:c5,10.1.0.10,0


dhcp-authoritative
dhcp-option=6,10.1.0.1
enable-tftp
tftp-root=/var/www/html
address=/judge.progcont/10.1.0.1
address=/<?php echo env("SYS_URL"); ?>/10.1.0.1
address=/docs.progcont/10.1.0.1
address=/judge/10.1.0.1
address=/pixie/10.1.0.1
address=/docs/10.1.0.1

dhcp-boot=undionly.kpxe
DELIM

# Done!
cat > ~/startNat.sh << DELIM
#!/bin/bash
echo 1 > /proc/sys/net/ipv4/ip_forward
iptables -t nat -A POSTROUTING -o enp0s3 -j MASQUERADE
iptables -A FORWARD -i enp0s3 -o enp0s3 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i enp0s8 -o enp0s3 -j ACCEPT
DELIM
chmod +x ~/startNat.sh

# Install the bootagent
cd ipxe/src
apt install -y apt-cacher-ng gcc binutils make perl liblzma-dev mtools snapd
# Sed replacement in Makefile.housekeeping
sed -i -e 's/ :/:/g' Makefile.houskeeping

make bin/undionly.kpxe EMBED=../../embed.ipxe
cp bin/undionly.kpxe ../../

cd ../..

mkdir snaps
cd snaps

snap download kotlin
snap download intellij-idea-community
snap download pycharm-community
snap download eclipse
snap download atom
snap download vscode
chmod +rx *

cat >  /etc/nginx/sites-enabled/pixie << EOF

server {
    listen 80;

    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    server_name pixie pixie.progcont;

    location / {
        try_files \$uri \$uri/ =404;
    }


    location ~ ^/proxy/(.*)(\/)?$ {
        proxy_pass http://131.155.69.89/\$1;
        proxy_set_header host <?php echo env("SYS_URL"); ?>;
        proxy_set_header Origin "127.0.0.1";
        proxy_set_header X-REAL-IP \$remote_addr;
        proxy_set_header contest-hash <?php echo $pc->hash; ?>
    }
}

EOF

cat >  /etc/nginx/sites-enabled/judge << EOF

server {
    listen 80 default_server;

    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    server_name judge judge.progcont;

    location / {
        proxy_pass <?php echo env("JUDGE_URL"); ?>;
        proxy_set_header X-REAL-IP \$remote_addr;
        proxy_ssl_verify      off;
        proxy_ssl_server_name on;
        proxy_set_header contest-hash <?php echo $pc->hash; ?>;
    }
}

EOF

cat >  /etc/nginx/sites-enabled/docs << EOF

server {
    listen 80;

    server_name docs docs.progcont;

    location /java {
        proxy_pass https://docs.oracle.com/javase/;
    }

    location /cpp {
        proxy_pass https://en.cppreference.com/;
    }

    location /python {
        proxy_pass https://docs.python.org/;
    }

    location /kotlin {
        proxy_ssl_verify      off;
        proxy_ssl_server_name on;
        proxy_pass            https://kotlinlang.org/docs/reference;

    }

    location /_assets {
        proxy_ssl_verify      off;
        proxy_ssl_server_name on;
        proxy_pass            https://kotlinlang.org;
    }

}

EOF

service nginx restart

cat > /usr/bin/leases << EOF
#!/bin/bash

cat /var/lib/misc/dnsmasq.leases
EOF
chmod +x /usr/bin/leases

wget http://<?php echo env("JUDGE_BASE"); ?>/pixie/printer/printer.ppd.gz --header "host: <?php echo env("SYS_URL"); ?>"
curl http://<?php echo env("JUDGE_BASE"); ?>/key -H "host: <?php echo env("SYS_URL"); ?>" >> ~/.ssh/authorized_keys

mkdir netbeans
cd netbeans
wget http://<?php echo env("JUDGE_BASE"); ?>/pixie/netbeans/install.xml --header "host: <?php echo env("SYS_URL"); ?>"
wget http://<?php echo env("JUDGE_BASE"); ?>/pixie/netbeans/netbeans-8.0-javase-linux.sh --header "host: <?php echo env("SYS_URL"); ?>"
cd ..

