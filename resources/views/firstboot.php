#!/bin/bash
sleep 10

ip addr show
sleep 5

baseurl="http://<?php echo env("SYS_URL"); ?>"
scriptid="<?php echo $script->guid; ?>"
aptpackages="git make gcc openjdk-8-jdk ntp xsltproc procps g++ fp-compiler firefox cups kate vim gedit geany vim-gnome idle-python2.7 idle-python3.5 codeblocks terminator xterm ddd valgrind gdb"

curl -XPOST -H "Content-Type: text/plain" --data 5 ${baseurl}/proxy/pixie/script/${scriptid}/update

add-apt-repository ppa:damien-moore/codeblocks-stable
add-apt-repository https://pc2cancer.ecs.csus.edu/apt/
apt-get update
apt-get install software-properties-common screen curl snapd parallel -y --force-yes
curl ${baseurl}/proxy/key >> /root/.ssh/authorized_keys;

curl -XPOST -H "Content-Type: text/plain" --data 6 ${baseurl}/proxy/pixie/script/${scriptid}/update

cat > /etc/cron.d/notif-pixie << EOF
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

#* * * * * root /usr/bin/curl -XPOST ${baseurl}/proxy/register/laptop/\$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32 ; echo '') >/dev/null 2>&1
EOF

echo "export LC_ALL=C.UTF-8" >> /etc/profile

# Install de
apt-get install --no-install-recommends ubuntu-gnome-desktop -y --force-yes
apt-get remove --purge gdm3 -y --force-yes

apt-get install lightdm-webkit-greeter lightdm -y --force-yes

curl -XPOST -H "Content-Type: text/plain" --data 35 ${baseurl}/proxy/pixie/script/${scriptid}/update

wget ${baseurl}/proxy/pixie/greeter.html -O /usr/share/lightdm-webkit/themes/default/index.html
wget ${baseurl}/proxy/pixie/bg.png -O /etc/alternatives/lightdm-webkit-theme/bg.png
cp /etc/alternatives/lightdm-webkit-theme/bg.png /usr/share/backgrounds/warty-final-ubuntu.png

curl -XPOST -H "Content-Type: text/plain" --data 40 ${baseurl}/proxy/pixie/script/${scriptid}/update


apt-get install $aptpackages -y --force-yes

curl -XPOST -H "Content-Type: text/plain" --data 60 ${baseurl}/proxy/pixie/script/${scriptid}/update

mkdir snaps
cd snaps
wget -r -np --cut-dirs=3 -R "index.html*" ${baseurl}/snaps
cd ${baseurl}
find . -name "*.assert" | cut -d'.' -f2 | parallel 'snap ack .{}.assert; snap install --classic .{}.snap'
cd ../..
rm -rf snaps

curl -XPOST -H "Content-Type: text/plain" --data 90 ${baseurl}/proxy/pixie/script/${scriptid}/update

# Install printer
wget ${baseurl}/printer.ppd.gz
lpadmin -p Printer -P printer.ppd.gz -v ipp://10.1.0.10

curl -XPOST -H "Content-Type: text/plain" --data 93 ${baseurl}/proxy/pixie/script/${scriptid}/update

#remove wireless
mkdir -p /root/backup/
mv /lib/modules/$(uname -r)/kernel/drivers/net/wireless /root/backup/
curl -XPOST -H "Content-Type: text/plain" --data 94 ${baseurl}/proxy/pixie/script/${scriptid}/update

#install netbeans 8
#wget ${baseurl}/netbeans/netbeans-8.0-javase-linux.sh
#wget ${baseurl}/netbeans/install.xml
#chmod +x netbeans-8.0-javase-linux.sh
#    ./netbeans-8.0-javase-linux.sh --silent --state install.xml

curl -XPOST -H "Content-Type: text/plain" --data 96 ${baseurl}/proxy/pixie/script/${scriptid}/update


# Setup aliasses
cat >> /etc/profile << EOF

mycc() {
gcc -x c -Wall -O2 -static -pipe -o program "$@" -lm
    ./program
}
mycpp() {
g++ -x c++ -Wall -O2 -static -pipe -o program "$@" -lm
    ./program
}
myjava() {
javac -encoding UTF-8 -sourcepath . -d . "$@"
    for i in *.class
    do
        java -Dfile.encoding=UTF-8 -XX:+UseSerialGC "${i%.*}"
    done
}

alias mypy='echo "Please select a python version: Run \"alias mypy=mypy2\" or \"alias mypy=mypy3\""'
alias mypy2=pypy2
alias mypy3=python3

EOF

cd /root
wget https://www.domjudge.org/icpc-kotlinc_1.1.4-3~icpc_all.deb
dpkg -i icpc-kotlinc_1.1.4-3~icpc_all.deb

curl -XPOST -H "Content-Type: text/plain" --data 98 ${baseurl}/proxy/pixie/script/${scriptid}/update

cat > /etc/rc.local << EOF

snaps=$(snap list)
apts=$(apt list --installed $aptpackages)
echo "$apts

$snaps" | curl -XPOST -H "Content-Type: text/plain" --data @- ${baseurl}/proxy/pixie/script/${scriptid}/finish

rm /etc/rc.local

EOF

curl -XPOST -H "Content-Type: text/plain" --data 99 ${baseurl}/proxy/pixie/script/${scriptid}/update

reboot
