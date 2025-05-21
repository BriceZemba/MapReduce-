<?php
require_once('config.php');
require_once('hadoop-api.php');

// Récupérer quelques stats HDFS pour la page d'accueil
$hdfs_stats = [];
exec(DOCKER_CMD . " " . HDFS_CMD . " dfsadmin -report", $report_output);
foreach ($report_output as $line) {
    if (strpos($line, 'Configured Capacity') !== false) {
        $hdfs_stats['capacity'] = trim(str_replace('Configured Capacity:', '', $line));
    }
    if (strpos($line, 'Present Capacity') !== false) {
        $hdfs_stats['used'] = trim(str_replace('Present Capacity:', '', $line));
    }
    if (strpos($line, 'DFS Remaining') !== false) {
        $hdfs_stats['remaining'] = trim(str_replace('DFS Remaining:', '', $line));
    }
}

// Récupérer la liste des jobs récents
$recent_jobs = [];
exec(DOCKER_CMD . " " . HADOOP_CMD . " job -list all", $jobs_output);
foreach ($jobs_output as $line) {
    if (preg_match('/^job_\d+_\d+/', $line)) {
        $parts = preg_split('/\s+/', $line);
        $recent_jobs[] = [
            'id' => $parts[0],
            'name' => $parts[1],
            'state' => $parts[2],
            'start_time' => isset($parts[3]) ? date('Y-m-d H:i:s', $parts[3] / 1000) : '',
            'user' => isset($parts[4]) ? $parts[4] : ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hadoop Management Interface</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Hadoop Manager</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li>
                    <a href="hdfs-browser.php"><i class="fas fa-folder-open"></i> HDFS Browser</a>
                </li>
                <li>
                    <a href="job-submit.php"><i class="fas fa-tasks"></i> Job Submission</a>
                </li>
                <li>
                    <a href="job-monitor.php"><i class="fas fa-chart-line"></i> Job Monitor</a>
                </li>
                <li>
                    <a href="cluster-monitor.php"><i class="fas fa-server"></i> Cluster Monitor</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                    </button>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">Dashboard Overview</h2>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h5 class="card-title">HDFS Capacity</h5>
                                <div class="stat-value"><?= $hdfs_stats['capacity'] ?? 'N/A' ?></div>
                                <div class="stat-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h5 class="card-title">Used Space</h5>
                                <div class="stat-value"><?= $hdfs_stats['used'] ?? 'N/A' ?></div>
                                <div class="stat-icon">
                                    <i class="fas fa-hdd"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h5 class="card-title">Remaining Space</h5>
                                <div class="stat-value"><?= $hdfs_stats['remaining'] ?? 'N/A' ?></div>
                                <div class="stat-icon">
                                    <i class="fas fa-memory"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card quick-actions">
                            <div class="card-header">
                                <h5>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <a href="hdfs-browser.php" class="quick-action">
                                            <div class="quick-action-icon">
                                                <i class="fas fa-folder-open"></i>
                                            </div>
                                            <div class="quick-action-text">Browse HDFS</div>
                                        </a>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <a href="upload.php" class="quick-action">
                                            <div class="quick-action-icon">
                                                <i class="fas fa-upload"></i>
                                            </div>
                                            <div class="quick-action-text">Upload File</div>
                                        </a>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <a href="job-submit.php" class="quick-action">
                                            <div class="quick-action-icon">
                                                <i class="fas fa-tasks"></i>
                                            </div>
                                            <div class="quick-action-text">Submit Job</div>
                                        </a>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <a href="cluster-monitor.php" class="quick-action">
                                            <div class="quick-action-icon">
                                                <i class="fas fa-server"></i>
                                            </div>
                                            <div class="quick-action-text">Cluster Status</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Jobs -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Jobs</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_jobs)): ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Job ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Start Time</th>
                                                <th>User</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_jobs as $job): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($job['id']) ?></td>
                                                    <td><?= htmlspecialchars($job['name']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?=
                                                                                    $job['state'] === 'RUNNING' ? 'success' : ($job['state'] === 'SUCCEEDED' ? 'info' : 'danger')
                                                                                    ?>">
                                                            <?= htmlspecialchars($job['state']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($job['start_time']) ?></td>
                                                    <td><?= htmlspecialchars($job['user']) ?></td>
                                                    <td>
                                                        <a href="job-details.php?id=<?= urlencode($job['id']) ?>"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-info-circle"></i> Details
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No recent jobs found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- External Links -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Hadoop Web Interfaces</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="http://<?= HADOOP_HOST ?>:9870" target="_blank" class="external-link">
                                            <i class="fas fa-sitemap"></i> NameNode UI
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="http://<?= HADOOP_HOST ?>:8088" target="_blank" class="external-link">
                                            <i class="fas fa-project-diagram"></i> ResourceManager
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="http://<?= HADOOP_HOST ?>:19888" target="_blank" class="external-link">
                                            <i class="fas fa-history"></i> JobHistory
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/scripts.js"></script>
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>

</html>