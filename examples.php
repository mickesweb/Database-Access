 <?php
    // Including class file.
    include_once(dirname(__FILE__).'/class.dataaccess.php');
    // Create a new instans of the class.
    $data = new Dataaccess();
    
    //Insert data to database
    $sql = "INSERT INTO test(name) VALUES (:name)";
    $sqlValue = array(array(":name"=>"Mikael"),array(":name"=>"Mickesweb"));
    echo "insert: ".$data->insert($sql, $sqlValue);
    // Is the last id in the table.
    echo "Last id: ".$data->lastId();

    //Delete rows from database
    $sql = "DELETE FROM test WHERE name = :name";
    $sqlValue = array(":name"=>"Mikael");
    //Shows the number of rows that are deleted.
    echo "Deltete row: ".$data->delete($sql, $sqlValue);

    // Query
    $sql = "SELECT * FROM test WHERE name = :name";
    $sqlValue = array(":name"=>"Mikael");
    echo "Result array: ";
    print_r($data->query($sql, $sqlValue));

    $sql = "SELECT * FROM test";
    echo "Result array: ";
    print_r($data->query($sql));

    // Close the database connection.
    $data->close();
?>