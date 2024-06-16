<?php
$ipAddress = $_SERVER['REMOTE_ADDR'];
$ipVoteFileName = "ipVote.json";
$votesFileName = "votes.json";


$ipExists = false;
$fileContent_ipVote = file_get_contents($ipVoteFileName);
if ($fileContent_ipVote !== false) {
    $ipData = json_decode($fileContent_ipVote, true);

    if (isset($ipData[$ipAddress])) {
        $lastVoteTime = strtotime($ipData[$ipAddress]);
        $currentTime = time();
        $timeDifference = $currentTime - $lastVoteTime;


        if ($timeDifference < 60) {
            $ipExists = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$ipExists) {
        $selectedLanguage = $_POST['language'];
        $votes = [];


        if (file_exists($votesFileName)) {
            $votes = json_decode(file_get_contents($votesFileName), true);
        }


        if (isset($votes[$selectedLanguage])) {
            $votes[$selectedLanguage]++;
        } else {
            $votes[$selectedLanguage] = 1;
            $defaultLanguages = ['C++', 'C#', 'JavaScript', 'PHP', 'Java'];

            foreach ($defaultLanguages as $language) {
                if (!isset($votes[$language])) {
                    $votes[$language] = 0;
                }
            }
        }


        $totalVotes = array_sum($votes);
        $percentages = [];
        foreach ($votes as $language => $count) {
            $percentage = ($count / $totalVotes) * 100;
            $percentages[$language] = round($percentage, 2);
        }


        file_put_contents($votesFileName, json_encode($votes));


        $ipData[$ipAddress] = date('Y-m-d H:i:s');
        file_put_contents($ipVoteFileName, json_encode($ipData));
    } else {

        echo "<p>Вы уже проголосовали. Пожалуйста, подождите 1 минуту, прежде чем проголосовать снова.</p>";
    }
}


$votes = [];
if (file_exists($votesFileName)) {
    $votes = json_decode(file_get_contents($votesFileName), true);
    if ($votes === null) {
        $votes = [];
    }
}


$totalVotes = array_sum($votes);
$percentages = [];
foreach ($votes as $language => $count) {
    $percentage = ($count / $totalVotes) * 100;
    $percentages[$language] = round($percentage, 2);
}


if (empty($votes)) {
    $defaultLanguages = ['C++', 'C#', 'JavaScript', 'PHP', 'Java'];
    foreach ($defaultLanguages as $language) {
        $votes[$language] = 0;
        $percentages[$language] = 0.00;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Интернет-голосование</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-top: 50px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .radio-group {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .radio-group input[type="radio"] {
            margin-right: 10px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
        }

        table {
            margin-top: 50px;
            border-collapse: collapse;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        #vote-button {
            margin-top: 30px;
            padding: 10px 25px;
            font-size: 14px;
        }


        .result-bar {
            width: 100%;
            height: 20px;
            background-color: green;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<h1>Интернет-голосование</h1>
<div class="container">
    <h2>Какой язык программирования вы бы предпочли?</h2>
    <form method="post">
        <div class="radio-group">
            <?php foreach ($votes as $language => $count) { ?>
                <label>
                    <input type="radio" name="language" value="<?php echo $language; ?>">
                    <?php echo $language; ?>
                </label><br>
            <?php } ?>
        </div>
        <button type="submit" id="vote-button">Голосовать</button>
    </form>
</div>

<table>
    <tr>
        <th>Язык программирования</th>
        <th>% голосов</th>
        <th></th>
    </tr>
    <?php foreach ($votes as $language => $count) { ?>
        <tr>
            <td><?php echo $language; ?></td>
            <td>
                <div class="result-bar" style="width: <?php echo $percentages[$language]; ?>%;"></div>
            </td>
            <td><?php echo $percentages[$language]; ?>%</td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
