<?php
function cents_to_money($cents) {
    return '$' . number_format($cents / 100, 2);
}

// Connecting, selecting database
$link = mysql_connect('localhost', 'root', 'tatishev5.4')
    or die('Could not connect: ' . mysql_error());
mysql_select_db('nlp_systems') or die('Could not select database');

// Performing SQL query
$query = 'SELECT payed_on, service, amount_cents FROM billing';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Printing results in HTML
echo "<table>\n";
echo "<tr><th>Дата и время</th><th>Услуга</th><th>Сумма</th></tr>\n";
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "<tr>";
    $date = date_create($line['payed_on']);
    echo "<td>" . date_format($date, 'd.m.Y H:i') . "</td>";
    echo "<td>" . $line['service'] . "</td>";
    echo "<td>" . cents_to_money($line['amount_cents']) . "</td>";
    echo "</tr>\n";
}
echo "</table>\n";

$total_result = mysql_query('SELECT SUM(amount_cents) AS amount_cents_sum FROM billing');
$total = mysql_fetch_assoc($total_result)['amount_cents_sum'];
echo "<hr /><strong>Доход всего: </strong>" . cents_to_money($total);

// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
?>

