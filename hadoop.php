<?php
// Connecting, selecting database
$link = mysql_connect('localhost', 'root', 'tatishev5.4')
    or die('Could not connect: ' . mysql_error());
mysql_select_db('nlp_systems') or die('Could not select database');

// Performing SQL query
$query = 'SELECT log FROM hadoop';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Printing results in HTML
echo "<table>\n";
echo "<tr><th>Логи</th></tr>\n";
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $line['log'] . "</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
?>

