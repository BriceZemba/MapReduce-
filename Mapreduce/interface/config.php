<?php
// Configuration du cluster Hadoop
define('HADOOP_NAMENODE', 'hdfs://namenode:9000');
define('HADOOP_HOST', 'localhost');
define('HADOOP_PORT', '9870');

// Chemins des commandes
define('HADOOP_CMD', '/usr/local/hadoop/bin/hadoop');
define('HDFS_CMD', '/usr/local/hadoop/bin/hdfs');

// Connexion Docker
define('DOCKER_CMD', 'docker exec hadoop-namenode');
?>
