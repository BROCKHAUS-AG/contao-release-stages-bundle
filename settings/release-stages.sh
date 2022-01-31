#!/bin/bash

rm -r /html/contao/files/content_old
mv /html/contao/files/content /html/contao/files/content_old
cp -R /html/contao/files/temp/content /html/contao/files/content
