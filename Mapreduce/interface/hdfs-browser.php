<?php
require_once 'hadoop-api.php';

$currentPath = $_GET['path'] ?? '/';

// Gestion de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['hdfs_file'])) {
    $uploadPath = $currentPath . '/' . basename($_FILES['hdfs_file']['name']);
    $tmpPath = $_FILES['hdfs_file']['tmp_name'];
    
    $uploadResult = uploadToHDFS($tmpPath, $uploadPath);
    if ($uploadResult['success']) {
        $message = "Fichier uploadé avec succès !";
    } else {
        $message = "Erreur lors de l'upload : " . $uploadResult['error'];
    }
}

$listing = listHDFSDirectory($currentPath);

function getParentPath($path) {
    return dirname($path) === '.' ? '/' : dirname($path);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HDFS Browser</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .path-navigation { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .file-listing { width: 100%; border-collapse: collapse; }
        .file-listing td, .file-listing th { padding: 8px; border: 1px solid #ddd; }
        .file-listing tr:nth-child(even) { background-color: #f2f2f2; }
        .upload-form { margin: 20px 0; padding: 15px; background: #e9f7ef; }
        .icon { font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Navigateur HDFS</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- Barre de navigation -->
        <div class="path-navigation">
            <a href="?path=/">Root</a>
            <?php 
            $parts = explode('/', trim($currentPath, '/'));
            $current = '';
            foreach ($parts as $part) {
                if (!empty($part)) {
                    $current .= '/' . $part;
                    echo ' &raquo; <a href="?path='.$current.'">'.$part.'</a>';
                }
            }
            ?>
        </div>

        <!-- Formulaire d'upload -->
        <div class="upload-form">
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="hdfs_file" required>
                <input type="submit" value="Uploader vers <?= htmlspecialchars($currentPath) ?>">
            </form>
        </div>

        <!-- Liste des fichiers -->
        <table class="file-listing">
            <?php if ($currentPath !== '/'): ?>
            <tr>
                <td colspan="4">
                    <a href="?path=<?= getParentPath($currentPath) ?>">
                        <span class="icon">📁</span> ..
                    </a>
                </td>
            </tr>
            <?php endif; ?>

            <?php
            if ($listing['success']) {
                foreach (explode("\n", $listing['output']) as $line) {
                    if (preg_match('/^[d-].+/', $line)) {
                        $cols = preg_split('/\s+/', $line);
                        $isDir = $cols[0][0] === 'd';
                        $path = end($cols);
                        $name = basename($path);
                        echo "<tr>
                            <td><span class='icon'>".($isDir ? '📁' : '📄')."</span></td>
                            <td><a href='".($isDir ? "?path=$path" : "#")."'>$name</a></td>
                            <td>".formatSize($cols[4])."</td>
                            <td><a href='download.php?path=$path'>Télécharger</a></td>
                        </tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='4'>Erreur : ".htmlspecialchars($listing['output'])."</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>

<?php
function formatSize($bytes) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes/pow(1024, $i), 2).' '.$units[$i];
}
?>