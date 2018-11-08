d-i debian-installer/locale string en_US
d-i console-setup/ask_detect boolean false
d-i keyboard-configuration/layoutcode string us
d-i netcfg/choose_interface select enp0s25
d-i netcfg/dhcp_timeout string 600
d-i netcfg/get_hostname string contestmachine
d-i netcfg/get_domain string progcont
d-i netcfg/wireless_wep string
d-i hw-detect/load_firmware boolean true
d-i mirror/http/proxy string http://<?php echo env("SYS_URL"); ?>:3142
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

d-i preseed/late_command string in-target usermod -G contestant contestant; mkdir /target/root/.ssh; echo "      dhcp-identifier: mac" >> /target/etc/netplan/01-netcfg.yaml; mkdir /target/root/.ssh; chmod 700 /target/root/.ssh; wget http://<?php echo env("SYS_URL"); ?>/proxy/templates/firstboot -O /target/etc/rc.local; chmod +x /target/etc/rc.local