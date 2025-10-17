<?php
$baseDir = __DIR__ . '/pdfs';
$selectedFolder = $_GET['folder'] ?? null;

function listFolders($dir) {
    $folders = array_filter(glob($dir . '/*'), 'is_dir');
    return array_map('basename', $folders);
}

function listPDFs($dir) {
    $files = glob($dir . '/*.pdf');
    return array_map('basename', $files);
}

$folders = listFolders($baseDir);
$files = $selectedFolder ? listPDFs("$baseDir/$selectedFolder") : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Disponibilizador de PDFs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { padding-top: 70px; }
.card-folder { cursor: pointer; transition: transform 0.2s; }
.card-folder:hover { transform: scale(1.05); }
.pdf-card { margin-bottom: 20px; }
.pdf-card iframe { width: 100%; height: 200px; border: 1px solid #dee2e6; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Disponibilizador de PDFs</a>
    <?php if($selectedFolder && $files): ?>
    <div class="dropdown ms-auto">
      <button class="btn btn-light dropdown-toggle" type="button" id="pdfDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Arquivos de <?= htmlspecialchars($selectedFolder) ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pdfDropdown">
        <?php foreach($files as $file): ?>
          <li><a class="dropdown-item" href="?folder=<?= urlencode($selectedFolder) ?>&file=<?= urlencode($file) ?>"><?= htmlspecialchars($file) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</nav>

<div class="container-fluid mt-3">
<?php if(!$selectedFolder): ?>
    <!-- Página inicial: Cards com pastas -->
    <div class="row">
        <?php foreach($folders as $folder): ?>
        <div class="col-md-3 mb-4">
            <div class="card card-folder text-center shadow-sm" onclick="window.location='?folder=<?= urlencode($folder) ?>'">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($folder) ?></h5>
                    <p class="card-text text-muted">Clique para abrir</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Pasta selecionada -->
    <div class="row">
        <!-- Menu lateral -->
        <nav class="col-md-3 col-lg-2 border-end">
            <h5>PDFs em <?= htmlspecialchars($selectedFolder) ?></h5>
            <a href="download_all.php?folder=<?= urlencode($selectedFolder) ?>" class="btn btn-success mb-3 w-100">Baixar Todos</a>
            <?php foreach($files as $file): ?>
                <a href="pdfs/<?= urlencode($selectedFolder) ?>/<?= urlencode($file) ?>" 
                   class="btn btn-outline-secondary w-100 mb-1" download><?= htmlspecialchars($file) ?></a>
            <?php endforeach; ?>
        </nav>

        <!-- Área principal: cards para PDFs -->
        <main class="col-md-9 col-lg-10">
            <div class="row">
                <?php foreach($files as $file): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card pdf-card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($file) ?></h6>
                            <iframe src="pdfs/<?= urlencode($selectedFolder) ?>/<?= urlencode($file) ?>"></iframe>
                            <a href="pdfs/<?= urlencode($selectedFolder) ?>/<?= urlencode($file) ?>" 
                               class="btn btn-outline-secondary mt-2 w-100" download>Baixar</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
