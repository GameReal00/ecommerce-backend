async function loadProducts() {
    const token = localStorage.getItem('token');
    if (!token) {
        alert('Please login first.');
        window.location.href = 'login.html';
        return;
    }

    const response = await fetch('http://localhost/PAYMENT_E-COMMERCE/products.php', {
        headers: {
            'Authorization': `Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwicm9sZSI6ImFkbWluIiwiZXhwIjoxNzQwODIyNTA5fQ.-SJUbFGmUCzA70JctPd5T1fjODIaCf1Kp25RB76xCWk`
        }
    });

    const products = await response.json();

    if (!Array.isArray(products)) {
        console.error('Unexpected API response:', products);
        alert('Failed to load products.');
        return;
    }

    const container = document.getElementById('products-container');
    container.innerHTML = products.map(product => `
        <div class="product">
            <h2>${product.name}</h2>
            <p>${product.description}</p>
            <p>Price: $${product.price}</p>
            <button onclick="addToCart(${product.id})">Add to Cart</button>
        </div>
    `).join('');
}


async function addToCart(productId) {
    try {
        const response = await fetch('http://localhost/PAYMENT_E-COMMERCE/Cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwicm9sZSI6ImFkbWluIiwiZXhwIjoxNzQwODIyNTA5fQ.-SJUbFGmUCzA70JctPd5T1fjODIaCf1Kp25RB76xCWk'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        });

        const result = await response.json();

        console.log('Add to Cart Result:', result); // Debugging line
        alert(result.message);
    } catch (error) {
        console.error('Error in addToCart:', error);
        alert('Failed to add product to cart.');
    }
}

loadProducts();
