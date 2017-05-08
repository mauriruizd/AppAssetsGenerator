# Assets Generator
Resize easily assets for both android and iOS platforms.

## Options
* `--dir` or `-d` : (required) The directory path with all the assets. Other file types or directories will be omitted.
* `--inputdensity` or `-i` : Input Android density. Default is xxxhdpi.
* `--outputdensities` or `-o` : The list of output densities separated by commas. Default is ldpi,mdpi,hdpi,xhdpi,xxhdpi

## iOS values
For the iOS always the output will always be 3 files, the 1x, 2x and 3x. The input files will be used as the 3x file.

## Utilization
For the usage first, install the dependencies:
```
composer install
```
You can run the script by typing the following
```
php generator.php --dir INPUTDIR
```
The directory path can be relative or absolute. In the directory passed as parameter will be created a folder named output, with two folders in it: android and ios, with all the assets resized for you.
