<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "facebookdev.cevu32mso1il.ap-northeast-2.rds.amazonaws.com";
        $DB_NAME = "facebookDev";
        $DB_USER = "facebookDev";
        $DB_PW = "lyunj2020!";

        $GOOGLE_API_KEY = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";

        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}