#!/bin/sh  

rm -rvf extraction_directory_$2/

mkdir -v extraction_directory_$2
 
chmod -R 777 extraction_directory_$2

cp -rfv apktool-install-linux-r05-ibot/*  extraction_directory_$2/

cp -rfv $1 extraction_directory_$2/$2.apk

cd extraction_directory_$2/

apktool if framework-res.apk

apktool d $2.apk



