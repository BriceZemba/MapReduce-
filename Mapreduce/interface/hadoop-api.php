<?php
function executeHadoopCommand($command) {
    $fullCmd = "docker exec namenode " . escapeshellcmd($command);
    exec($fullCmd, $output, $returnCode);
    return [
        'success' => $returnCode === 0,
        'output' => implode("\n", $output)
    ];
}

function uploadToHDFS($localPath, $hdfsPath) {
        // 1. Copier le fichier dans le conteneur
        $tmpInContainer = "/tmp/" . basename($localPath);
        exec("docker cp " . escapeshellarg($localPath) . " namenode:" . $tmpInContainer);
        
        // 2. Upload vers HDFS
        $command = "docker exec namenode hdfs dfs -put " . escapeshellarg($tmpInContainer) . " " . escapeshellarg($hdfsPath) . " 2>&1";
        exec($command, $output, $returnCode);
        
        // 3. Nettoyer
        exec("docker exec namenode rm " . escapeshellarg($tmpInContainer));
        
        return [
            'success' => $returnCode === 0,
            'error' => $returnCode === 0 ? '' : implode("\n", $output)
        ];
    }

function listHDFSDirectory($path = '/') {
    $cmd = "hdfs dfs -ls $path";
    return executeHadoopCommand($cmd);
}

function runMapReduceJob($jarPath, $inputPath, $outputPath) {
    $cmd = "hadoop jar $jarPath $inputPath $outputPath";
    return executeHadoopCommand($cmd);
}
?>