<?php

// Connecting, selecting database
$link = mysql_connect('localhost', 'root', 'tatishev5.4')
    or die('Could not connect: ' . mysql_error());
mysql_select_db('nlp_systems') or die('Could not select database');

// Performing SQL query
$query = 'SELECT date, node, nodenum, time FROM amazon';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Printing results in HTML
echo "<table>\n";
echo "<tr><th>Дата и время</th><th>Тип узла</th><th>Число узлов</th><th>Время (мин.)</th></tr>\n";
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "<tr>";
    $date = date_create($line['date']);
    echo "<td>" . date_format($date, 'd.m.Y H:i') . "</td>";
    echo "<td>" . $line['node'] . "</td>";
    echo "<td>" . $line['nodenum'] . "</td>";
    echo "<td>" . $line['time'] . "</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
?>

