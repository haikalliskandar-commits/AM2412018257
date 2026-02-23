<?php
include 'db.php';

$query = "
SELECT users.name, destinations.city, trips.travel_date
FROM trips
JOIN users ON trips.user_id = users.user_id
JOIN destinations ON trips.destination_id = destinations.destination_id
";
$result = $conn->query($query);

$currencyApiKey = "a05744349118d40813463e2573b7a60de796a365c063310551dbf58279d4a896";
$currencyUrl = "https://v6.exchangerate-api.com/v6/$currencyApiKey/latest/USD";

$exchangeRate = "3.89";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $currencyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response !== false) {
    $currencyData = json_decode($response, true);
    if (isset($currencyData['conversion_rates']['MYR'])) {
        $exchangeRate = $currencyData['conversion_rates']['MYR'];
    }
}

/* ===============================
   3️⃣ OPENWEATHER API KEY
   =============================== */
$weatherApiKey = "77fc4ea4a85b6fdde9e79618b1804e3b";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Travel Information Dashboard</title>
</head>
<body>

<h2>Travel Information Dashboard</h2>

<table border="1" cellpadding="8">
<tr>
    <th>User Name</th>
    <th>Destination</th>
    <th>Travel Date</th>
    <th>Temperature (°C)</th>
    <th>Exchange Rate (USD → MYR)</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $city = $row['city'];
        $temperature = "N/A";

        /* ===============================
           WEATHER API REQUEST
           =============================== */
        $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=$weatherApiKey&units=metric";
        $weatherResponse = @file_get_contents($weatherUrl);

        if ($weatherResponse !== false) {
            $weatherData = json_decode($weatherResponse, true);
            if (isset($weatherData['main']['temp'])) {
                $temperature = $weatherData['main']['temp'];
            }
        }

        echo "<tr>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['city']) . "</td>
            <td>{$row['travel_date']}</td>
            <td>{$temperature}</td>
            <td>{$exchangeRate}</td>
        </tr>";
    }

} else {
    echo "<tr><td colspan='5'>No data found</td></tr>";
}
?>

</table>

</body>
</html>
