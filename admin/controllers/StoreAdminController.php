<?php
    require_once __DIR__ .'/../../config/env.php';
    require_once __DIR__ .'/../models/StoreAdmin.php';

    class StoreAdminController {
        public function getAllStores(){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT * FROM store";
            $result = $db->query($sql);
            $stores = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $store = new StoreAdmin();
                    $store->Id = $row['Id'];
                    $store->StoreName = $row['StoreName'];
                    $store->Address = $row['Address'];
                    $store->Phone = $row['Phone'];
                    $store->OpenTime = $row['OpenTime'];
                    $store->CloseTime = $row['CloseTime'];
                    $stores[] = $store;
                }
            }
            $db->close();
            return $stores;
        }
public function getStoreById($storeId){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "SELECT * FROM store WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $storeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $store = new StoreAdmin();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $store->Id = $row['Id'];
                $store->StoreName = $row['StoreName'];
                $store->Address = $row['Address'];
                $store->Phone = $row['Phone'];
                $store->OpenTime = $row['OpenTime'];
                $store->CloseTime = $row['CloseTime'];
            }
            $stmt->close();
            $db->close();
            return $store;
        }

    public function createStore($store){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "INSERT INTO store (StoreName, Address, Phone, OpenTime, CloseTime) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sssss", $store->StoreName, $store->Address, $store->Phone, $store->OpenTime, $store->CloseTime);
            $isSuccess = $stmt->execute();
            $stmt->close();
            $db->close();
            return $isSuccess;
        }

    public function deleteStore($storeId){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "DELETE FROM store WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $storeId);
            $isSuccess = $stmt->execute();
            $stmt->close();
            $db->close();
            return $isSuccess;
        }

    public function updateStore($store){
            global $hostname, $username, $password, $dbname, $port;
            $db = new mysqli($hostname, $username, $password, $dbname, $port);
            $sql = "UPDATE store SET StoreName = ?, Address = ?, Phone = ?, OpenTime = ?, CloseTime = ? WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sssssi", 
                $store->StoreName, 
                $store->Address, 
                $store->Phone, 
                $store->OpenTime, 
                $store->CloseTime, 
                $store->Id
            );
            $isSuccess = $stmt->execute();
            $result = $isSuccess && ($stmt->affected_rows > 0);
            $stmt->close();
            $db->close();
            return $result;
        }
    }
?>

