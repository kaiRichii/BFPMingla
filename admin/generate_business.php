<?php
require_once '../db_connection.php';
session_start();

if(!isset($_SESSION['role']) && $_SESSION['role'] != 'Admin'){
    header('location: ../index/index.php');
    exit;
}

$sql = "SELECT *, issuance.id AS fsec FROM applications INNER JOIN issuance ON applications.id = issuance.application_id WHERE applications.id = '".$_GET['id']."'";
$result = $conn->query($sql);

$applications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jsonData = json_decode($row['additional'], true);

        $fsic = $jsonData['fsic'] ?? null;
        $or = $jsonData['or'] ?? null;
        $amount = $jsonData['amount'] ?? null;
        $datePayment = $jsonData['datePayment'] ?? null;
        $dateCertificate = $jsonData['dateCertificate'] ?? null;

        $row['fsic'] = $fsic;
        $row['or'] = $or;
        $row['amount'] = $amount;
        $row['datePayment'] = $datePayment;
        $row['dateCertificate'] = $dateCertificate;

        $applications[] = $row;
    }
} else {
    echo "Error: " . $conn->error; 
}

$conn->close(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Document</title>
    <style>
        html, body {
            height: 956px;
            margin: 0;
            padding: 0;
            font-family: 'arial';
            font-weight: bold;
        }

        p {
            white-space: nowrap;
            margin: 0;
            padding: 0;
        }

        .document {
            background-image: url("../img/business.jpg");
            background-size: contain;
            background-repeat: no-repeat;
            height: 956px;
            position: relative;
            -webkit-print-color-adjust: exact !important; 
    		color-adjust: exact !important; 
        }

        .fsecno{
            position: absolute;
            margin-top: 188px;
            margin-left: 190px;
            color: black;
            font-size: 1rem;
            width: 130px;
            text-align: center;
        }

        .date {
            position: absolute;
            margin-top: 298px;
            margin-left: 420px;
            font-size: 1rem;
            width: 130px;
            text-align: center;
        }

        .building {
            position: absolute;
            margin-top: 380px;
            margin-left: 175px;
            font-size: 0.75rem;
            width: 530px;
            text-align: center;
        }

        .address {
            position: absolute;
            margin-top: 463px;
            margin-left: 75px;
            font-size: 0.75rem;
            width: 530px;
            text-align: center;
        }

        .owner {
            position: absolute;
            margin-top: 424px;
            margin-left: 235px;
            font-size: 0.75rem;
            width: 230px;
            text-align: center;
        }

        .issued {
            position: absolute;
            margin-top: 539px;
            margin-left: 325px;
            font-size: 0.75rem;
            width: 230px;
            text-align: center;
        }

        .valid {
            position: absolute;
            margin-top: 575px;
            margin-left: 245px;
            font-size: 0.75rem;
            width: 100px;
            text-align: center;
        }

        .chief {
            position: absolute;
            margin-top: 680px;
            margin-left: 445px;
            font-size: 0.75rem;
            width: 160px;
            text-align: center;
        }

        .marshal {
            position: absolute;
            margin-top: 745px;
            margin-left: 400px;
            font-size: 0.75rem;
            width: 160px;
            text-align: center;
        }

        .paid {
            position: absolute;
            margin-top: 657px;
            margin-left: 130px;
            font-size: 0.75rem;
            width: 100px;
            text-align: center;
        }

        .or {
            position: absolute;
            margin-top: 670px;
            margin-left: 130px;
            font-size: 0.75rem;
            width: 100px;
            text-align: center;
        }

        .date2 {
            position: absolute;
            margin-top: 682px;
            margin-left: 110px;
            font-size: 0.75rem;
            width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php foreach($applications as $app): ?>
    <div class="document">
        <p class="fsecno"><?= $app['fsic'] ?></p>
        <p class="date"><?= date("m/d/y", strtotime($app['dateCertificate'])) ?></p>
        <p class="building"><?= strtoupper($app['business_trade_name']) ?></p>
        <p class="address"><?= strtoupper($app['address']) ?></p>
        <p class="owner"><?= strtoupper($app['owner_name']) ?></p>
        <p class="issued">BUSINESS PERMIT</p>
        <p class="valid"><?= (new DateTime($app['dateCertificate']))->modify('+1 year')->format('m/d/y') ?></p>
        <p class="paid"><?= $app['amount'] ?>php</p>
        <p class="or"><?= $app['or'] ?></p>
        <p class="date2"><?= date("m/d/y", strtotime($app['datePayment'])) ?></p>
        <p class="chief">FO2 RUEL N. ANG, BFP </p>
        <p class="marshal">BERNARDITO T. BARUEL, F/SINSP BFP</p>
    </div>
    <?php endforeach; ?>
</body>
<script>
    window.onload = function() {
        window.print();

        window.onafterprint = function() {
            window.close();
        };
    };
</script>
</html>
