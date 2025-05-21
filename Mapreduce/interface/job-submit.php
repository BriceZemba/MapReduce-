<?php
require_once 'hadoop-api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPath = $_POST['input_path'];
    $outputPath = $_POST['output_path'];
    
    $result = runMapReduceJob(
        '/jobs/mapreduce.jar',
        $inputPath,
        $outputPath
    );
}
?>
<form method="POST">
    <label>Chemin HDFS Input:</label>
    <input type="text" name="input_path" value="/input/data.txt" required>
    
    <label>Chemin HDFS Output:</label>
    <input type="text" name="output_path" value="/output/result_".time()"" required>
    
    <button type="submit">Exécuter le Job</button>
</form>

<?php if (isset($result)): ?>
<div class="result">
    <h3>Résultats :</h3>
    <pre><?= htmlspecialchars($result['output']) ?></pre>
</div>
<?php endif; ?>