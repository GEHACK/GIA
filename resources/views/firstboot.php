#!/bin/bash
sleep 10

ip addr show
sleep 5

baseurl="http://<?php echo env("SYS_URL"); ?>"
scriptid="<?php echo $script->guid; ?>"
aptpackages="sed perl emacs git mate-terminal make gcc openjdk-11-jdk ntp xsltproc procps g++ pypy fp-compiler firefox cups kate vim gedit geany vim-gnome idle-python2.7 idle-python3.7 codeblocks terminator xterm ddd valgrind gdb icpc-clion icpc-intellij-idea icpc-pycharm icpc-eclipse icpc2019-jetbrains icpc-kotlinc junit"

# Start of more expansive installation
apt install -y software-properties-common

curl -XPOST -H "Content-Type: text/plain" --data 5 ${baseurl}/proxy/pixie/script/${scriptid}/update

cat > /root/pc2cancer.gpgkey << EOF
-----BEGIN PGP PUBLIC KEY BLOCK-----

mQINBFcT5RMBEACueGCpG+Jh79S6sz38SAELRLR/VFPJZENdy4Pl2Q+NCrXAMbUE
VHHZgcUTfd2yhvtnVCIXdGXYCohVFjyDubzi4fvshwdhGTRqcnZkPSUJWKiDVsu9
OW+BQWf9AT5Qg2hhcjTRe+CxNzfhRZgVVIYWHFCjZNzOjQXDlMWMyUueAjxcUWS2
7wpSuOzDqjQ+CFmkqVU+v1YMZOM83ESOYUV24G6qoObLGGXyFxn1yA5fWFLpr2S+
WiQ4DuLOLiTKxBAeBhjFRpsrpYLTIITv4A1Gl2yIba9ith1+/TGLk5UOiryY2pxk
AnvHFAYPwepxO93l63x6JM+Pmmcjb73AGR2pCdsLa8/JQfmTQ4D/T9SHkGeYtG6L
EJkTfDRLqO5G28+5A7C4sI3fpwsw2fvVUoaNVVG1dZDJBUHctV9hzC24kqEVgtwQ
Iy/qN5Po4EkG/WCVXD8hZlPql24iSJBltqI2ezI424Diy0hmfCV+rgGhzsKDzea2
73lGaX2qlg+vBXjurbqP3JmPhY6EgJwTtplI5Gmh0jqfHg7vBB3GmnU5CvQR8pRn
Idu0Lwfhj1vDYxrst9vJharFgTOaXj1Ee8VBM/A1GZB+3NuZhCUNzAucRlkbytsk
Iv+gCc8cnsvZ199bDDBGG/y+uqQ7nsz9ANPI+qANhr6m3TUht7fyS0Pb9QARAQAB
tDJQQ14yIFBhY2thZ2UgU2lnbmluZyBLZXkgKDIwMTYpIDxwYzJAZWNzLmNzdXMu
ZWR1PokCPQQTAQoAJwUCVxPlEwIbAwUJEswDAAULCQgHAwUVCgkICwUWAwIBAAIe
AQIXgAAKCRAGKeN5kzCo8CUKD/9GT9rLN8uEIdFfQwnryrkQVBNulXUt6jvq3W95
SwL+/d8uVDq9VuJx+OP69gVzPOBNA438h/hcWmW/6XBy5mzS59m0uxS1auk08Hvu
ri+Yqezm+Z/n3WVPgf0LwU9m53KjAoWILgz/hQG9ILVz42k96gX49DZzduy6KWLz
qAL2Ef2g2GnSXi144IVPwDGncjR4jVjGd+c+OqBi81sZrGezVK01ZzoBmf1VT8ST
Bz8qCbC5LAEhdivCUW0NyK3fFLvFY9k+iXJIFvHR46h41g5Ew+78kjtSV3xnSiNi
9jVcaTXJ+rJw93P+rZmvrbaiIWQVmqxk64xNdP7LLldiRJJaUA3t97yISa8tFuE3
gQfpEUepDN0XFGVPbaRWL4Kzm9wZ2jUaWusPWXIldgnZR9cgvp9M69kW33x4sXkz
DIeXBRZnkprGYzk6+WJm+nBAZzuUl+bzSsCOQ5qw/8ydRTdZ/dTISYAqlpolS6fc
qBMD+SrZ2uK0ZUqu1+SYdyw/UO7m1Oe+TPNy5bgApnlstB+YHQDZ5s/n+N3zpJVs
F++I3OwejrXmI0Mj/s3gm4fgzJTdHMKJZcUE7mc+ToEuBB6/7sBjAQ3cCppN0Dri
XnSA9nxRMgYo3jzwbWTWowiiw/z7jiw3nJ9Grd8b67TwhImAZaPmVSHWdwgDFiMi
+McCJrkCDQRXE+UTARAA0N5yQvnvQ0YILQw0j4HCTM4m0h5bG/7p0EAflBlfbH+F
But6NxpxiDT3zFW5wu8o899Anr5jmyZ4r+qFt8KbuGEWlQTimtWbMMYHZvn5s6nN
LBX3xxK/eXulVLWwgyhWMMquPUOs7evJM+Abjs+JvLIeB2x5rSN8PLrVtIXWBLR/
x+Cv/6WEEGSxwzNSS7FsTkUfCdxOYk/rYTJzaR5zaDmu8iRNPkaT84Md/oaz8Pm6
eTi0ub+Kw4NJ9Kgcue0dXHAqAEpb4KOYi7K+vJEMTuBz1hge41isqjTirp1C174J
22QIZQwBmBnSOGd4sPeuci+3DmBSfEwJcaWNZxOatFCSaymymr+I7wnwMAs47frS
2POanZnRjJcETa5Y4VSvBqNAelA9Hxzya96bNPQ+n03AU3xfiI/ip4+Fi4WD48rC
LfN2zUtqzIqt5W9EIeicG0kc1TuvmY7GlJWEItaECONQqg+dI/cjmrPh9Se/Qbl8
9TUP3HxCu9KGfnq/LUU9L1h7kVtbT9N9tFE9/6Ubdt5j57vO20nGm3U/qHCMgLbB
wRHhi1gMK1kpMkMBgYRfcRnmWlfNO3FaBwzWE07aKTK4dL2yivxHPreheSIeYJWY
dc5V9k66/m+jHDwLG2D6vJCdRBpBr2ywKl/TCQX3CuXmtDpcwegcxqWnKcsBjgUA
EQEAAYkCJQQYAQoADwUCVxPlEwIbDAUJEswDAAAKCRAGKeN5kzCo8JMLD/9A40Il
ufBJeNPB4k6RDCI1jcHwC51tY4N5z6ULQqhXi0fZMuRQp1COPmv6EbygZLwcXVes
0xIQn3HJicSR0MWGbOXojZb8eDZeVw1gHfnEUf1zn5vYJvPMebBxwnTGnD4nyj4b
iQzils4a7NwSc+VpMLkR1mB0C2a8qtulrMod+eJG4ojX+7aFrjgnfgqFuBCaaB+t
rgvbFONSN+g0DnlF7m8lb8AK8WKXE3C2Jq/djVTRMyOZultNJRo3V7OAI64Bpvtl
3Ab4RS1pVfR0iy4GsbeQPQI6O9xnX3KbHTW+LA6kVYbFkX26f64jPjtznZ1EtK1s
pENrdXjVXUtN41Z64/qnSW/q9bNEGr03mGqK1Q1L3sPz6nuU1TTAMgkHIOE+fx+8
CL0tpmbudRGr2/tiHSrP/sEJOn0tUHsF25n1pyL/ZU55t73L4ZrO1YsHz41jsLms
wWs8afKaVje6XyJa7T6cwDtCE42pmSr0j8x085OQJHayDispWBGJ9KbQ7Z0Pcmes
bJ8c36Nx57x/uhI2LKLJ68ySBOwBiSF66K9McqVzEEhpbsMRXiJrZiBsteFG1nFa
dN2DScLNQoyQvDE4R+0cBzU5IHNvjUu4TX+u8CMNFIEbkbbywU1InX7Pxw6Bz1QA
gb5QGo5PpwPFV7eZc1hq7rpAX5Jdma+CkGSTCQ==
=XQ8/

-----END PGP PUBLIC KEY BLOCK-----

EOF
apt-key add /root/pc2cancer.gpgkey

#add-apt-repository ppa:damien-moore/codeblocks-stable
add-apt-repository http://pc2cancer.ecs.csus.edu/apt/
apt-get update
apt-get install screen curl snapd parallel -y --force-yes
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

mkdir /root/snaps
cd /root/snaps
wget -r -np --cut-dirs=3 -R "index.html*" ${baseurl}/snaps
cd <?php echo env("SYS_URL"); ?>

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

cat > /etc/modprobe.d/blacklist-sound.conf << EOF
blacklist soundcore
blacklist snd
blacklist snd_pcm
blacklist snd_pcsp
blacklist pcspkr
EOF

curl -XPOST -H "Content-Type: text/plain" --data 96 ${baseurl}/proxy/pixie/script/${scriptid}/update


# Setup aliasses
cat >> /etc/skel/.bashrc << EOF

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
alias mypy2=pypy
alias mypy3=python3

EOF

rm -rf /home/contestant
cp -r /etc/skel/ /home/contestant
chown -R contestant:contestant /home/contestant/

curl -XPOST -H "Content-Type: text/plain" --data 100 ${baseurl}/proxy/pixie/script/${scriptid}/update

snaps="$(snap list)"
apts="$(apt list --installed $aptpackages)"

curl -0 -v -XPOST -H "Content-Type: text/plain; charset=utf-8" $baseurl/proxy/pixie/script/${scriptid}/finish \
--data-binary @- << EOF
${apts}
${snaps}
EOF

cat >> /root/makePublic.sh << EOF
#!/bin/bash

apt install lightdm-webkit-greeter --reinstall -y
echo "" > /etc/apt/apt.conf

EOF

chmod +x /root/makePublic.sh


rm /etc/rc.local

reboot
