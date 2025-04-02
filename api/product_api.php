<?php
include "config.php";
// CORS headers
header("Content-Type: application/json"); // Ensure JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle OPTIONS request (preflight request)
// if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
//     http_response_code(200); // Allow the OPTIONS request and exit
//     exit;
// }

// Handle GET request
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Check if 'id' parameter is provided for a single product fetch
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Query to fetch a single product by id
        $sql = "SELECT id, name, price, brand, qty, image FROM products WHERE id = $id";
        $result = mysqli_query($conn, $sql);

        // Fetch the product if it exists
        if ($product = mysqli_fetch_assoc($result)) {
            echo json_encode($product); // Return the product as JSON
        } else {
            echo json_encode(["message" => "Product not found"]);
        }
    } else {
        // Query to fetch all products if no 'id' is provided
        $sql = "SELECT id, name, price, brand, qty, image FROM products";
        $result = mysqli_query($conn, $sql);

        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }

        echo json_encode($products); // Return all products as JSON
    }
}


// Handle DELETE request (Delete Product)
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    // Check if product ID is provided in the query string
    $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($productId > 0) {
        // Delete the product from the database
        $sql = "DELETE FROM products WHERE id = $productId";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => "Product deleted"]);
        } else {
            echo json_encode(["error" => "Failed to delete product"]);
        }
    } else {
        echo json_encode(["error" => "Invalid product ID"]);
    }
}


// Handle POST request (Insert or Update)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["_method"]) && $_POST["_method"] === "PUT") {
        // Update Product
        if (!isset($_POST["id"]) || !isset($_POST["name"]) || !isset($_POST["price"]) || !isset($_POST["brand"]) || !isset($_POST["qty"])) {
            echo json_encode(["message" => "Missing required fields"]);
            exit;
        }

        $id = intval($_POST["id"]);
        $name = $conn->real_escape_string($_POST["name"]);
        $price = floatval($_POST["price"]);
        $brand = $conn->real_escape_string($_POST["brand"]);
        $qty = intval($_POST["qty"]);

        // Fetch the current image name
        $sql = "SELECT image FROM products WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $currentImage = "";
        if ($row = mysqli_fetch_assoc($result)) {
            $currentImage = $row['image'];
        }

        $updateImage = "";
        if (!empty($_FILES["image"]["name"])) {
            // Delete the old image if a new one is uploaded
            if ($currentImage && file_exists("uploads/" . $currentImage)) {
                unlink("uploads/" . $currentImage); // Delete the old image from the server
            }

            // Move the new image to the server
            $targetDir = "uploads/";
            $imageName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFilePath = $targetDir . $imageName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $updateImage = $imageName;
            } else {
                echo json_encode(["message" => "Image upload failed"]);
                exit;
            }
        }

        // Update product details in the database
        $sql = "UPDATE products SET name='$name', price='$price', brand='$brand', qty='$qty', image='$updateImage' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Product updated successfully"]);
        } else {
            echo json_encode(["message" => "Failed to update product"]);
        }
        exit;
    }

    // Add Product (if applicable)
    if (!isset($_POST["name"]) || !isset($_POST["price"]) || !isset($_POST["brand"]) || !isset($_POST["qty"]) || !isset($_FILES["image"])) {
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    $name = $conn->real_escape_string($_POST["name"]);
    $price = floatval($_POST["price"]);
    $brand = $conn->real_escape_string($_POST["brand"]);
    $qty = intval($_POST["qty"]);

    $targetDir = "uploads/";
    $imageName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO products (name, price, brand, qty, image) VALUES ('$name', '$price', '$brand', '$qty', '$imageName')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Product added successfully"]);
        } else {
            echo json_encode(["message" => "Failed to add product"]);
        }
    } else {
        echo json_encode(["message" => "Image upload failed"]);
    }
}



$conn->close();
?>


