<?php
require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/../../models/Store.php';

class StoreController {
    public function getAllStores(){
        global $hostname, $username, $password, $dbname, $port;
        $db = new mysqli($hostname, $username, $password, $dbname, $port);

        $sql = "SELECT * FROM store";
        $result = $db->query($sql);

        $stores = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $store = new Store();
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
        
        $store = $result->fetch_object();
        $stmt->close();
        $db->close();
        
        return $store;
    }
}
?>

