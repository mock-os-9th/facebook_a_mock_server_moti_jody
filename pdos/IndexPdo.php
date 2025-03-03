<?php

function createUser($email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO User (email, phoneNum, pwd, secondName, firstName, bday, gender) VALUES (?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender]);

    $recruitId = $pdo->lastInsertId();
    $st = null;
    $pdo = null;

    return $recruitId;
}

function isEmailDuplicated($email)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isPhoneNumDuplicate($phoneNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE phoneNum = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$phoneNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidUser($id, $pw)
{
    $pdo = pdoSqlConnect();

    if (isValidPhoneNum($id)) {
        $query = "SELECT EXISTS(SELECT * FROM User WHERE phoneNum= ? AND pwd = ? AND isDeleted = 'N') AS exist;";
    } else if (isValidEmail($id)) {
        $query = "SELECT EXISTS(SELECT * FROM User WHERE email= ? AND pwd = ? AND isDeleted = 'N') AS exist;";
    } else {
        return false;
    }
    $st = $pdo->prepare($query);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function deleteUser($id)
{
    $pdo = pdoSqlConnect();

    if (isValidPhoneNum($id)) {
        $query = "UPDATE User SET isDeleted = 'Y' WHERE phoneNum = ?;";
    } else if (isValidEmail($id)) {
        $query = "UPDATE User SET isDeleted = 'Y' WHERE email = ?;";
    } else {
        return false;
    }
    $st = $pdo->prepare($query);
    $st->execute([$id]);

    $st = null;
    $pdo = null;

    return $id;
}


////READ
//function testDetail($testNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM Test WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$testNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//
//function testPost($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Test (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}

// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
