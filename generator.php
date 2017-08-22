<?php
require 'vendor/autoload.php';
use Intervention\Image\ImageManagerStatic as Image;
use Intervention\Image\Exception\NotReadableException;

// Setting up configurations
const ANDROIDDENSITYMAP = [
    3 => "ldpi",
    4 => "mdpi",
    6 => "hdpi",
    8 => "xhdpi",
    12 => "xxhdpi",
    16 => "xxxhdpi"
];
$shortoptions = "d:i::o::";
$longoptions = [
	"dir:",
	"inputdensity::",
	"outputdensities:"
];

function checkOrCreateDir($dir)
{
    if (!is_dir($dir)) {
        if (is_file($dir)) {
            die('There is a file with the name of ' . $dir . ' already');
        }
        if(!mkdir($dir)) {
            die('No fue posible crear ' . $dir);
        }
    }
}

function getFiles($dir)
{
    return scandir($dir);
}

function getIndexFromDensityString($density)
{
    return array_keys(ANDROIDDENSITYMAP, $density)[0];
}

function calculateNewAndroidSize($inputSize, $from, $to)
{
    return round(getIndexFromDensityString($to) * $inputSize / getIndexFromDensityString($from));
}

function calculateNewIosSize($inputSize, $times)
{
    if ($times == 3) {
        return $inputSize;
    }
    $originalSize = $inputSize / 3;
    return $originalSize * $times;
}

function getOutputFileName($filePath, $ios, $times)
{
    $fileName = basename($filePath);
    if ($ios) {
        if ($times > 0 && $times < 4) {
            $extension = '.' . strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $extensionWithTimes = '@' . $times . 'x' . $extension;
            $outputFileName = str_replace($extension, $extensionWithTimes, $fileName);
        }
    } else {
        $outputFileName = $fileName;
    }
    return $outputFileName;
}

function saveNewImage($filePath, $from, $to, $outputDir, $ios = false, $times = 1)
{
    if (!is_file($filePath)) return false;
    $outputFileName = getOutputFileName($filePath, $ios, $times);
    try {
        $image = Image::make($filePath);
        $imageSize = $image->width();
        $newSize = $ios ? calculateNewIosSize($imageSize, $times) : calculateNewAndroidSize($imageSize, $from, $to);
        $image->resize($newSize, null, function ($c) {
            $c->aspectRatio();
        })->save($outputDir . '/' . $outputFileName);
        return true;
    } catch (NotReadableException $e) {
        echo "Unable to open $filePath as image\n";
    }
}

// Getting options
$options = getopt($shortoptions, $longoptions);
if (!isset($options['dir']) && !isset($options['d'])) {
    die('Input directory not found!');
}
$dir = isset($options['dir']) ? $options['dir'] : $options['d'];

// Setting up directories
$outputBaseDirectory = $dir . '/output/';
$outputDirectoriesPrefix = 'Android/drawable-';
$iosOutputDirectory = $outputBaseDirectory . '/iOS/';

// Creating directories
checkOrCreateDir($outputBaseDirectory);
checkOrCreateDir($outputBaseDirectory . 'Android');
foreach (ANDROIDDENSITYMAP as $density) {
    checkOrCreateDir($outputBaseDirectory . $outputDirectoriesPrefix . $density);
}
checkOrCreateDir($iosOutputDirectory);

$inputDensity = isset($options['inputdensity']) ? $options['inputdensity'] :
    (isset($options['i']) ? $options['i'] : 'xxxhdpi');
$outputDensities = isset($options['outputdensities']) ? $options['outputdensities'] :
    (isset($options['o']) ? $options['o'] : 'ldpi,mdpi,hdpi,xhdpi,xxhdpi,xxxhdpi');
$outputDensities = explode(',', trim($outputDensities));

$files = getFiles($dir);
foreach ($outputDensities as $density) {
    $outputDirectory = $outputBaseDirectory . $outputDirectoriesPrefix . $density;
    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        saveNewImage($filePath, $inputDensity, $density, $outputDirectory);
    }
}

foreach ($files as $file) {
    $filePath = $dir . '/' . $file;
    for ($times = 1; $times <=3; $times++) {
        saveNewImage($filePath, 0, 0, $iosOutputDirectory, true, $times);
    }
}
