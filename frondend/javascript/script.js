// Fetch Products to showing on table list
function getProducts() {
    fetch("http://localhost:8080/product_store_api/api/product_api.php")
        .then(response => response.json())
        .then(data => {
            console.log("Products:", data);
            let productList = document.getElementById("productList");
            productList.innerHTML = "";
            data.forEach((product, index) => {
                productList.innerHTML += `
                    <tr class="bg-danger">
                        <td>${index + 1}</td>
                        <td>${product.name}</td>
                        <td>$${product.price}</td>
                        <td>${product.brand}</td>
                        <td>${product.qty}</td>
                        <td>
                            <img src="http://localhost:8080/product_store_api/api/uploads/${product.image}" class="product-img" width="100">
                        </td>
                        <td>
                             <button class="btn btn-warning" onclick="loadProductForEdit(${product.id})" data-bs-toggle="modal" data-bs-target="#editProductModal">Edit</button>
                            <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                        </td>
                    </tr>

                `;
            });
        })
        .catch(error => console.error("Error fetching products:", error));
}



// Add Product
const addProduct = async () => {
    let name = document.getElementById("name").value;
    let price = document.getElementById("price").value;
    let brand = document.getElementById("brand").value;
    let qty = document.getElementById("qty").value;
    let image = document.getElementById("image").files[0];

    // Check if any required field is missing
    if (!name || !price || !brand || !qty || !image) {
        alert("Please fill all the fields.");
        return;
    }

    let formData = new FormData();
    formData.append("name", name);
    formData.append("price", price);
    formData.append("brand", brand);
    formData.append("qty", qty);
    formData.append("image", image);

    let response = await fetch("http://localhost:8080/product_store_api/api/product_api.php", {
        method: "POST",
        body: formData
    });

    let result = await response.json(); // Read the response as JSON
    alert(result.message);  // Show the response message
    if (result.message === "Product added successfully") {
        window.location.href="productList.html";
        getProducts();  // Refresh the product list
    }
}


//edit product
const loadProductForEdit = async (id) => {
    // Fetch the product details to display them
    let response = await fetch(`http://localhost:8080/product_store_api/api/product_api.php?id=${id}`);
    let product = await response.json();

    // Fill the form with the product details
    document.getElementById("ProductId").value = product.id;
    document.getElementById("name").value = product.name;
    document.getElementById("price").value = product.price;
    document.getElementById("brand").value = product.brand;
    document.getElementById("qty").value = product.qty;

    // Display the existing image
    let imageContainer = document.querySelector(".showing_image");
    imageContainer.innerHTML = `<img src="http://localhost:8080/product_store_api/api/uploads/${product.image}" alt="Product Image" width="180px" />`; // Show the existing image
}


//update product
const updateProduct = async () => {
    const id = document.getElementById("ProductId").value;
    const name = document.getElementById("name").value;
    const price = document.getElementById("price").value;
    const brand = document.getElementById("brand").value;
    const qty = document.getElementById("qty").value;
    const image = document.getElementById("image").files[0]; // Get the image file from input

    let formData = new FormData();
    formData.append("id", id);  // Add the product ID for update
    formData.append("name", name);
    formData.append("price", price);
    formData.append("brand", brand);
    formData.append("qty", qty);

    // Append new image if it's provided, otherwise, it will retain the old image
    if (image) {
        formData.append("image", image);
    }

    // Adding _method = PUT to simulate PUT request since fetch only supports POST and GET
    formData.append("_method", "PUT");

    // Send the request to update the product
    let response = await fetch("http://localhost:8080/product_store_api/api/product_api.php", {
        method: "POST", // We use POST with _method=PUT
        body: formData
    });

    let result = await response.json(); // Read the response as JSON
    alert(result.message);  // Show the response message

    if (result.message === "Product updated successfully") {
        // Optionally redirect to a product list page or refresh the current page
        getProducts();  // Refresh the product list
    }
}




// Delete Product
function deleteProduct(id) {
    fetch(`http://localhost:8080/product_store_api/api/product_api.php?id=${id}`, {
        method: "DELETE"
    })
        .then(response => response.json())
        .then(result => {
            alert(result.success || result.error);
            getProducts();  // Refresh the product list
        })
        .catch(error => console.error("Error deleting product:", error));
}

// Load products on page load
document.addEventListener("DOMContentLoaded", getProducts);
