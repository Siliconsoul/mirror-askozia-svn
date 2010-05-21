# --- T2-COPYRIGHT-NOTE-BEGIN ---
# This copyright note is auto-generated by ./scripts/Create-CopyPatch.
# 
# T2 SDE: target/share/firmware/build.sh
# Copyright (C) 2004 - 2008 The T2 SDE Project
# 
# More information can be found in the files COPYING and README.
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; version 2 of the License. A copy of the
# GNU General Public License can be found in the file COPYING.
# --- T2-COPYRIGHT-NOTE-END ---
#
#Description: ubootimage

. $base/misc/target/functions.in

set -e

# set firmware preparation directory
imagelocation="$build_toolchain/firmware"

# cylinder size in bytes (16 heads x 63 sectors/track x 512 bytes/sector)
cylinder_size="516096"
sectors_per_cylinder="1008"
block_pad="2048"

echo "Preparing firmware image from build result ..."
rm -rf $imagelocation{,.img}
mkdir -p $imagelocation ; cd $imagelocation
mkdir root_stage
mkdir root_stage/boot
mkdir root_stage/conf
cp $base/target/$target/config.xml root_stage/conf/config.xml
mkdir offload_stage
mkdir offload_stage/asterisk
mkdir offload_stage/kernel-modules
mkdir offload_stage/software-information
mkdir loop

echo "Copy system into staging directories ..."
cp ../../boot/cuImage.warp root_stage/boot/
cp -Rp ../../offload/asterisk/* offload_stage/asterisk/
rm -rf offload_stage/asterisk/agi-bin
rm -rf offload_stage/asterisk/firmware
rm -rf offload_stage/asterisk/images
rm -rf offload_stage/asterisk/keys
rm -rf offload_stage/asterisk/static-http
cp -Rp ../../lib/modules/* offload_stage/kernel-modules/
rm -rf offload_stage/asterisk/astdb
rm -rf offload_stage/asterisk/sounds/en
ln -s /offload/asterisk/sounds/en-us offload_stage/asterisk/sounds/en
# new bits to move usr/* out of the initramfs and into /offload
mkdir offload_stage/rootfs
mkdir offload_stage/rootfs/usr/
mkdir offload_stage/rootfs/usr/libexec
# usr/bin
cp -Rp ../../usr/bin offload_stage/rootfs/usr/
rm -rf offload_stage/rootfs/usr/bin/aclocal*
rm -rf offload_stage/rootfs/usr/bin/auto*
rm -rf offload_stage/rootfs/usr/bin/bison
rm -rf offload_stage/rootfs/usr/bin/bzdiff
rm -rf offload_stage/rootfs/usr/bin/bzgrep
rm -rf offload_stage/rootfs/usr/bin/bzip2recover
rm -rf offload_stage/rootfs/usr/bin/bzmore
rm -rf offload_stage/rootfs/usr/bin/c_rehash
rm -rf offload_stage/rootfs/usr/bin/dbclient
rm -rf offload_stage/rootfs/usr/bin/envsubst
rm -rf offload_stage/rootfs/usr/bin/flite
rm -rf offload_stage/rootfs/usr/bin/flite_cmu_time_awb
rm -rf offload_stage/rootfs/usr/bin/flite_cmu_us_awb
rm -rf offload_stage/rootfs/usr/bin/flite_cmu_us_kal16
rm -rf offload_stage/rootfs/usr/bin/flite_cmu_us_rms
rm -rf offload_stage/rootfs/usr/bin/flite_cmu_us_slt
rm -rf offload_stage/rootfs/usr/bin/flite_time
rm -rf offload_stage/rootfs/usr/bin/gettextize
rm -rf offload_stage/rootfs/usr/bin/ifnames
rm -rf offload_stage/rootfs/usr/bin/libtool*
rm -rf offload_stage/rootfs/usr/bin/locale
rm -rf offload_stage/rootfs/usr/bin/ngettext
rm -rf offload_stage/rootfs/usr/bin/msg*
rm -rf offload_stage/rootfs/usr/bin/php-config
rm -rf offload_stage/rootfs/usr/bin/phpize
rm -rf offload_stage/rootfs/usr/bin/recode-sr-latin
rm -rf offload_stage/rootfs/usr/bin/xgettext
rm -rf offload_stage/rootfs/usr/bin/yacc
# usr/lib
cp -Rp ../../usr/lib offload_stage/rootfs/usr/
rm -rf offload_stage/rootfs/usr/lib/build/
rm -rf offload_stage/rootfs/usr/lib/engines/
rm -rf offload_stage/rootfs/usr/lib/gettext/
rm -rf offload_stage/rootfs/usr/lib/grub/
rm -rf offload_stage/rootfs/usr/lib/perl5/
rm -rf offload_stage/rootfs/usr/lib/pkgconfig/
rm -rf offload_stage/rootfs/usr/lib/preloadable_libiconv.so
# usr/libexec
cp -Rp ../../usr/libexec/sftp-server offload_stage/rootfs/usr/libexec
# usr/sbin
cp -Rp ../../usr/sbin offload_stage/rootfs/usr/
rm -rf offload_stage/rootfs/usr/sbin/dahdi_genconf
rm -rf offload_stage/rootfs/usr/sbin/dahdi_hardware
rm -rf offload_stage/rootfs/usr/sbin/dahdi_registration
#rm -rf offload_stage/rootfs/usr/sbin/grub
# usr/share
mkdir offload_stage/rootfs/usr/share
cp -Rp ../../usr/share/dahdi offload_stage/rootfs/usr/share/
mkdir offload_stage/rootfs/usr/share/terminfo
mkdir offload_stage/rootfs/usr/share/terminfo/a
cp -Rp ../../usr/share/terminfo/a/ansi offload_stage/rootfs/usr/share/terminfo/a/
mkdir offload_stage/rootfs/usr/share/terminfo/l
cp -Rp ../../usr/share/terminfo/l/linux offload_stage/rootfs/usr/share/terminfo/l/
mkdir offload_stage/rootfs/usr/share/terminfo/s
cp -Rp ../../usr/share/terminfo/s/screen offload_stage/rootfs/usr/share/terminfo/s/
mkdir offload_stage/rootfs/usr/share/terminfo/v
cp -Rp ../../usr/share/terminfo/v/vt100 offload_stage/rootfs/usr/share/terminfo/v/
cp -Rp ../../usr/share/terminfo/v/vt200 offload_stage/rootfs/usr/share/terminfo/v/
mkdir offload_stage/rootfs/usr/share/terminfo/x
cp -Rp ../../usr/share/terminfo/x/xterm offload_stage/rootfs/usr/share/terminfo/x/
cp -Rp ../../usr/share/terminfo/x/xterm-color offload_stage/rootfs/usr/share/terminfo/x/
cp -Rp ../../usr/share/terminfo/x/xterm-xfree86 offload_stage/rootfs/usr/share/terminfo/x/
cp -Rp ../../usr/share/udhcpc offload_stage/rootfs/usr/share/
chmod 755 offload_stage/rootfs/usr/share/udhcpc/default.script
mkdir offload_stage/rootfs/usr/share/zoneinfo
cp -Rp ../../usr/share/zoneinfo offload_stage/rootfs/usr/share/
# usr/www
cp -Rp ../../usr/www offload_stage/rootfs/usr/
chmod 644 offload_stage/rootfs/usr/www/*
chmod 755 offload_stage/rootfs/usr/www/*.php
chmod 755 offload_stage/rootfs/usr/www/cgi-bin/*.cgi
# usr/www_provisioning
cp -Rp ../../usr/www_provisioning offload_stage/rootfs/usr/
chmod 644 offload_stage/rootfs/usr/www_provisioning/*
chmod 755 offload_stage/rootfs/usr/www_provisioning/*.php

echo "Cleaning up asterisk sounds ..."
if [[ -d "offload_stage/asterisk/sounds/es" ]] ; then
	rmdir offload_stage/asterisk/sounds/es
fi
if [[ -d "offload_stage/asterisk/sounds/fr" ]] ; then
	rmdir offload_stage/asterisk/sounds/fr
fi
find offload_stage/asterisk/sounds/ -type f -name "*.pdf" -print -delete
find offload_stage/asterisk/sounds/ -type f -name "*.txt" -print -delete
for FILE in `find offload_stage/asterisk/sounds/ -name *g711u`
do
NEW=`echo $FILE | sed -e 's/g711u/ulaw/'`
mv "$FILE" "$NEW"
done

echo "Documenting software used in this build ..."
svn info $base > offload_stage/software-information/00-svn-revision-information
cp ../../var/adm/packages/* offload_stage/software-information/

echo "Cleaning away stray files ..."
find ./ -name "._*" -print -delete
find ./ -name "*.a" -print -delete
find ./ -name "*.c" -print -delete
find ./ -name "*.o" -print -delete
find ./ -name "*.po" -print -delete
rm -vrf `find ./ -name ".svn"`

echo "Root partition size calculation ..."
root_size=`du -B512 -s root_stage | cut -f 1`
root_size=`expr $root_size + $block_pad`
echo "   = $root_size sectors"

echo "Offload partition size calculation ..."
offload_size=`du -B512 -s offload_stage | cut -f 1`
offload_size=`expr $offload_size + $block_pad`
echo "   = $offload_size sectors"

echo "Total image size calculation ..."
total_sector_count=`expr $root_size + $offload_size + 1`
echo "   = $total_sector_count sectors"

echo "Writing a binary container for the disk image ..."
dd if=/dev/zero of=firmware.img bs=512 count=$total_sector_count


cyls_needed=`expr $total_sector_count / $sectors_per_cylinder + 1`
echo "Cylinders needed = $total_sector_count sectors / $sectors_per_cylinder sectors-per-cyl + 1 = $cyls_needed"
offload_start_sector=`expr $root_size + 1`

echo "Partition the disk image ..."
sfdisk -C$cyls_needed -S63 -H16 -uS -f -D --no-reread firmware.img << EOF
1,$root_size,6,*
$offload_start_sector,$offload_size,83
;
;
EOF


echo "Formatting and populating partitions ..."
echo " - part1 - dd..."
dd if=/dev/zero of=part1.img bs=512 count=$root_size
echo " - part1 - losetup..."
losetup /dev/loop0 part1.img
echo " - part1 - mkfs.vfat..."
mkfs.vfat -n system /dev/loop0
echo " - part1 - mount..."
mount -t msdos /dev/loop0 loop
echo " - part1 - cp root_stage..."
cp -Rp root_stage/* loop/
echo " - part1 - unmount..."
umount /dev/loop0
echo " - part1 - losetup -d..."
losetup -d /dev/loop0

echo " - part2 - dd..."
dd if=/dev/zero of=part2.img bs=512 count=$offload_size
echo " - part2 - mke2fs..."
mke2fs -m0 -L offload -F part2.img
echo " - part2 - tune2fs..."
tune2fs -c0 part2.img
echo " - part2 - mount..."
mount -o loop part2.img loop
echo " - part2 - cp offload_stage..."
cp -Rp offload_stage/* loop/
echo " - part2 - umount..."
umount loop

echo " - dd part1 -> firmware.img..."
dd if=part1.img of=firmware.img bs=512 seek=1
echo " - dd part2 -> firmware.img..."
dd if=part2.img of=firmware.img bs=512 seek=$offload_start_sector

gzip -9 firmware.img
mv firmware.img.gz ../$SDECFG_ID.img
