<!DOCTYPE html>
<html>
<body>
<?php
require_once 'vendor/autoload.php';

echo "Hello PHP Application Insight World!";
$servername = "XXX.XXX.XXX.XXXX:3309";
$username = "[your username]";
$password = "[your password]";
$dbname = "[your db name]";

$telemetryClient = new \ApplicationInsights\Telemetry_Client();
$context = $telemetryClient->getContext();
$context->setInstrumentationKey('[get this from application insight portal]');

// Start tracking
$telemetryClient->trackEvent('TestConnectMySQL', ['Status' => 'start-mysql-test']);

$telemetryClient->flush();

$start = microtime(true);

try
{
    try {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        $connected = true;
    } catch (mysqli_sql_exception  $e) { 
        $telemetryClient->trackException($e, ['TestConnectMySQLError' => mysqli_connect_error()], ['duration_inner' => 42.0]);
        $telemetryClient->flush();
    } 

    // Check connection
    if ($conn->connect_error) {

        $telemetryClient->trackEvent('TestConnectMySQLError', ['Status' => mysqli_connect_error()]);
        $telemetryClient->flush();
        $errmsg=mysqli_connect_error();
        $telemetryClient->trackException( new Exception("MySQL error $conn->connect_error <br> Query:<br> $errmsg", $conn->errno), ['TestConnectMySQLError' => mysqli_connect_error()], ['duration_inner' => 42.0]);
        $telemetryClient->flush();

        die("Connection failed: " . $conn->connect_error);

    } 

    $sql = 'SELECT * FROM `dbname`.`tablename` LIMIT 10;';
    $result = mysqli_query($conn, $sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<br> aAccount: ". $row["aAccount"]. " - aPassword: ". $row["aPassword"]."<br>";
        }
    } else {
        echo "0 results";
    }

    $conn->close();
}
catch (\Exception $ex)
{
    $telemetryClient->trackException($ex, ['TestConnectMySQLError' => 'connnect-mysql-failed'], ['duration_inner' => 42.0]);
    $telemetryClient->flush();
}

$time_elapsed_secs = microtime(true) - $start;

$telemetryClient->trackEvent('TestConnectMySQL', ['Status' => 'end-mysql-test']);
$telemetryClient->trackMetric('TotalmySQLExecutionTime(seconds)', $time_elapsed_secs);
$telemetryClient->flush();

echo '<b>Total Execution Time:</b> '.$time_elapsed_secs.' seconds';
?>

</body>
</html>