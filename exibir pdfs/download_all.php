<?php
$baseDir = __DIR__ . '/pdfs';
$folder = $_GET['folder'] ?? null;

if (!$folder) exit('Nenhuma pasta selecionada.');

$folderPath = "$baseDir/$folder";
if (!is_dir($folderPath)) exit('Pasta inválida.');

$files = glob("$folderPath/*.pdf");
if (!$files) exit('Nenhum PDF para baixar.');

$zipName = "$folder.zip";
$zip = new ZipArchive();
$zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

foreach ($files as $file) {
    $zip->addFile($file, basename($file));
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipName.'"');
header('Content-Length: ' . filesize($zipName));

readfile($zipName);
unlink($zipName); // remove o zip temporário
exit;
?>
