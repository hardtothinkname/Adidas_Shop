<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "adidas_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests
    $data = json_decode(file_get_contents('php://input'), true);

    if ($_POST['action'] == 'signInUser') {
        // Handle "addUser" endpoint
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);

        // Execute the query and fetch the results
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with the given username and password was found
        // Fetch the first row of the result set
        $row = $result->fetch_assoc();

        // Check if a user with the given username and password was found
        if ($result->num_rows > 0) {
            // Return the boolean value and the role value as JSON
            echo json_encode(array('success' => true, 'role' => $row['role']));
        } else {
            // Return the boolean value only as JSON
            echo json_encode(array('success' => false));
        }

        // Close the database connection
        $stmt->close();
        $conn->close();
    } elseif ($_POST['action'] == 'AddNewProduct') {
        // Handle "addUser" endpoint
        $productName = $_POST['product_name'];
        $description = $_POST['description'];
        $weight = $_POST['weight'];
        $unitPrice = $_POST['unit_price'];
        $stockQuantity = $_POST['stock_quantity'];
        $image = basename($_POST['imagePath']);
        // Prepare a SQL query to insert the product into the database
        try {
            // code that may cause an exception
            $stmt = $conn->prepare("INSERT INTO product (name, description, weight, unit_price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddis", $productName, $description, $weight, $unitPrice, $stockQuantity, $image);
            $stmt->execute();

            // $stmt_latest = $conn->prepare("SELECT * FROM mytable ORDER BY id DESC LIMIT 1");
            // $stmt_latest->execute();
            // $result = $stmt_latest->get_result();

            // $latest_record = $result->fetch_assoc();
            // $stmt = $conn->prepare("INSERT INTO sales (product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            // $stmt->bind_param("ssddi", $productName, $description, $weight, $unitPrice, $stockQuantity);
            // $stmt->execute();
            // Check if the execution is successful
            if ($stmt->affected_rows > 0) {
                echo "true";
            } else {
                echo "false";
            }

        } catch (Exception $e) {
            // code to handle the exception
            echo "Error: " . $e->getMessage();
        }

        // Close the prepared statement and database connection
        $stmt->close();
        $conn->close();
    } elseif ($_POST['action'] == 'purchaseProduct') {
        // Handle "addUser" endpoint
        $productID = $_POST['id'];
        $quantity = $_POST['quantity'];
        $unit_price = $_POST['unit_price'];
        // Prepare a SQL query to insert the product into the database
        try {
            // code that may cause an exception
            $stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
            $stmt->bind_param("i", $productID);

            // Execute the query and fetch the results
            $stmt->execute();
            $result = $stmt->get_result();
            $product_data = $result->fetch_assoc();

            $stmt = $conn->prepare("INSERT INTO sales (product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isif", $product_data['id'], $product_data['name'], $quantity, $unit_price);
            $stmt->execute();
            // Check if the execution is successful
            if ($stmt->affected_rows > 0) {
                echo "true";
            } else {
                echo "false";
            }

        } catch (Exception $e) {
            // code to handle the exception
            echo "Error: " . $e->getMessage();
        }

        // Close the prepared statement and database connection
        $stmt->close();
        $conn->close();
    } elseif ($data['action'] == 'editProduct') {
        $id = $data['id'];
        $name = $data['name'];
        $description = $data['description'];
        $weight = $data['weight'];
        $unit_price = $data['unit_price'];
        $quantity = $data['quantity'];
        $filePath = $data['filePath'];

        if($filePath != ""){
            $sql = "UPDATE product SET name='$name', description='$description', weight='$weight', unit_price='$unit_price', quantity='$quantity', image='$filePath' WHERE id='$id'";
        }else{
            $sql = "UPDATE product SET name='$name', description='$description', weight='$weight', unit_price='$unit_price', quantity='$quantity' WHERE id='$id'";
        }


        if (!mysqli_query($conn, $sql)) {
            http_response_code(500);
            echo 'Internal Server Error';
            exit;
        }

        mysqli_close($conn);

        http_response_code(200);
        echo 'true';
    } elseif ($data['action'] == 'editUser') {
        // $data = json_decode(file_get_contents('php://input'), true);

        $id = $data['id'];
        $name = $data['name'];
        $email = $data['email'];

        $sql = "UPDATE user SET name=?, email=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $id);

        if (mysqli_stmt_execute($stmt)) {
            http_response_code(200);
            return 'true';
        } else {
            http_response_code(500);
            return 'Internal Server Error';
            error_log("SQL error: " . mysqli_error($conn));
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests
    if ($_GET['action'] === 'getUser') {
        // Handle "getUser" endpoint
        $userId = $_GET['userId'];
        // Retrieve user from database
    }
    // else {
    //     // Invalid endpoint
    //     http_response_code(400);
    //     echo 'Invalid endpoint';
    // }

    if ($_GET['action'] === 'getProducts') {
        // Handle "addUser" endpoint
        $sql = "SELECT * FROM product";
        $result = $conn->query($sql);
        // Insert new user into database
        // Close connection
        $conn->close();

        // Return data as JSON
        header('Content-Type: application/json');
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
    if ($_GET['action'] === 'getMonthlySales') {
        $sql = "SELECT DATE_FORMAT(purchase_timestamp, '%Y-%m') AS month, SUM(quantity * unit_price) AS sales FROM sales GROUP BY month";
        $result = $conn->query($sql);

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $conn->close();
        echo json_encode($data);
    }
    if ($_GET['action'] === 'getYearlySales') {
        $sql = "SELECT DATE_FORMAT(purchase_timestamp, '%Y') AS year, SUM(quantity * unit_price) AS sales FROM sales GROUP BY year";
        $result = $conn->query($sql);

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $conn->close();
        echo json_encode($data);
    }
    if ($_GET['action'] === 'getUsers') {
        $sql = "SELECT * FROM user WHERE role = 'customer'";
        $result = $conn->query($sql);

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $conn->close();
        echo json_encode($data);
    }

}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body, true);

    if ($data['action'] === "customer") {
        $customer_id = $data['customer_id'];
        $sql = "DELETE FROM user WHERE id = $customer_id";
        if ($conn->query($sql) === true) {
            // return a success response to the frontend
            http_response_code(200);
            echo json_encode(array('message' => 'Customer deleted successfully.'));
        } else {
            // return an error response to the frontend
            http_response_code(500);
            echo json_encode(array('message' => 'Error deleting customer: ' . $conn->error));
        }

        $conn->close();
    }
    if ($data['action'] === "product") {
        $product_id = $data['product_id'];
        $sql = "DELETE FROM product WHERE id = $product_id";
        if ($conn->query($sql) === true) {
            // return a success response to the frontend
            http_response_code(200);
            echo json_encode(array('message' => 'Product deleted successfully.'));
        } else {
            // return an error response to the frontend
            http_response_code(500);
            echo json_encode(array('message' => 'Error deleting Product: ' . $conn->error));
        }

        $conn->close();
    }
}

function verifyCredentials($email, $password)
{
    global $servername, $username, $password, $dbname;

    // Create a new MySQL database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare a SQL query to select the user with the given username and password
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);

    // Execute the query and fetch the results
    $stmt->execute();
    $result = $stmt->get_result();

    echo $result;

    // Check if a user with the given username and password was found
    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
