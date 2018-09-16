#!/bin/bash

mkdir imgFiles
wget http://archive.ubuntu.com/ubuntu/dists/bionic-updates/main/installer-amd64/current/images/netboot/ubuntu-installer/amd64/linux -O imgFiles/linux
wget http://archive.ubuntu.com/ubuntu/dists/bionic-updates/main/installer-amd64/current/images/netboot/ubuntu-installer/amd64/initrd.gz -O imgFiles/initrd.gz

git clone git://git.ipxe.org/ipxe.git
baseurl="http://pixie.progcont"
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
imgargs linux auto=true fb=false url=\${base-url}/preseed.cfg tasksel/first=\"\" netcfg/choose_interface=enp0s3 hostname=teammachine domain=progcont

boot

DELIM

cat > preseed.cfg << DELIM
d-i debian-installer/locale string en_US
d-i console-setup/ask_detect boolean false
d-i keyboard-configuration/layoutcode string us
d-i netcfg/choose_interface select enp0s3
d-i netcfg/dhcp_timeout string 600
d-i netcfg/get_hostname string contestmachine
d-i netcfg/get_domain string progcont
d-i netcfg/wireless_wep string
d-i hw-detect/load_firmware boolean true
d-i mirror/http/proxy string http://pixie.progcont:3142
d-i mirror/country string germany

d-i mirror/http/mirror select de.archive.ubuntu.com
d-i clock-setup/utc boolean true
d-i time/zone string Europe/Amsterdam
d-i clock-setup/ntp boolean true
d-i partman-auto/method string regular
d-i partman-lvm/purge_lvm_from_device boolean true
d-i partman-lvm/confirm boolean true
d-i partman-auto/choose_recipe select atomic
d-i partman-partitioning/confirm_write_new_label boolean true
d-i partman/choose_partition select finish
d-i partman/confirm boolean true
d-i partman-md/confirm boolean true
d-i partman-partitioning/confirm_write_new_label boolean true
d-i partman/choose_partition select finish
d-i partman/confirm boolean true
d-i partman/confirm_nooverwrite boolean true
d-i passwd/make-user boolean false
d-i passwd/user-fullname string Contestant
d-i passwd/username string contestant
d-i passwd/user-password password contestant
d-i passwd/user-password-again password contestant
d-i user-setup/allow-password-weak boolean true
d-i passwd/auto-login boolean true
d-i user-setup/encrypt-home boolean false
d-i pkgsel/include string openssh-server htop curl
d-i pkgsel/upgrade select none
d-i pkgsel/update-policy select none
d-i pkgsel/updatedb boolean false
d-i lilo-installer/skip boolean true
d-i grub-installer/only_debian boolean true
d-i grub-installer/with_other_os boolean true
d-i finish-install/reboot_in_progress note

xserver-xorg xserver-xorg/autodetect_monitor boolean true
xserver-xorg xserver-xorg/config/monitor/selection-method    select medium
xserver-xorg xserver-xorg/config/monitor/mode-list    select 1024x768 @ 60 Hz

d-i preseed/late_command string in-target usermod -G contestant contestant; mkdir /target/root/.ssh; echo "      dhcp-identifier: mac" >> /target/etc/netplan/01-netcfg.yaml; mkdir /target/root/.ssh; chmod 700 /target/root/.ssh; wget http://pixie.progcont/firstboot -O /target/etc/rc.local; chmod +x /target/etc/rc.local
DELIM

cat > firstboot << DELIM
#!/bin/bash
sleep 10

ip addr show
sleep 5

apt-get update
apt-get install software-properties-common screen curl snapd parallel -y --force-yes
curl http://pixie.progcont/proxy/key >> /root/.ssh/authorized_keys;

cat > /etc/cron.d/notif-pixie << EOF
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

* * * * * root /usr/bin/curl -XPOST http://pixie.progcont/proxy/register/laptop/\$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32 ; echo '') >/dev/null 2>&1
EOF

echo "export LC_ALL=C.UTF-8" >> /etc/profile

# Install de
apt-get install --no-install-recommends ubuntu-gnome-desktop -y --force-yes
apt-get remove --purge gdm3 -y --force-yes

apt-get install lightdm-webkit-greeter lightdm -y --force-yes

wget http://pixie.progcont/proxy/pixie/greeter.html -O /usr/share/lightdm-webkit/themes/default/index.html
wget http://pixie.progcont/proxy/pixie/bg.png -O /etc/alternatives/lightdm-webkit-theme/bg.png
cp /etc/alternatives/lightdm-webkit-theme/bg.png /usr/share/backgrounds/warty-final-ubuntu.png

apt-get install make gcc openjdk-8-jdk ntp xsltproc procps g++ fp-compiler firefox cups kate vim gedit geany vim-gnome idle-python2.7 idle-python3.5 codeblocks terminator xterm -y --force-yes

mkdir snaps
cd snaps
wget -r -np --cut-dirs=3 -R "index.html*" pixie.progcont/snaps
cd pixie.progcont
find . -name "*.assert" | cut -d'.' -f2 | parallel 'snap ack .{}.assert; snap install --classic .{}.snap'
cd ../..
rm -rf snaps

rm /etc/rc.local
reboot

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
address=/pixie.progcont/10.1.0.1
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
chmod +x *.snap

cat >  /etc/nginx/sites-enabled/pixie << EOF

server {
    listen 80;

    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    server_name pixie;

    location / {
        try_files \$uri \$uri/ =404;
    }


    location ~ ^/proxy/(.*)(\/)?$ {
        proxy_pass http://131.155.69.89/\$1;
        proxy_set_header host pixie.progcont;
        proxy_set_header Origin "127.0.0.1";
        proxy_set_header X-REAL-IP \$remote_addr;
        proxy_set_header contest-hash VZ1fmxC2y6d2DNMrZ14rUKALlcRo7jG3;
    }
}

EOF

cat >  /etc/nginx/sites-enabled/judge << EOF

server {
    listen 80 default_server;

    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    server_name judge;

    location / {
        proxy_pass https://judge.gehack.nl/;
        proxy_set_header X-REAL-IP \$remote_addr;
        proxy_ssl_verify      off;
        proxy_ssl_server_name on;
        proxy_set_header contest-hash VZ1fmxC2y6d2DNMrZ14rUKALlcRo7jG3;
    }
}

EOF

cat >  /etc/nginx/sites-enabled/docs << EOF

server {
    listen 80;

    server_name docs;

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
